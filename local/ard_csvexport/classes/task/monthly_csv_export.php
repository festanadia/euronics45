<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Monthly CSV export task.
 *
 * @package    local_ard_csvexport
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_ard_csvexport\task;

use core\task\scheduled_task;
use local_ard_csvexport\sftp_client;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task for monthly CSV data export via SFTP.
 */
class monthly_csv_export extends scheduled_task {

    /**
     * Get the task name for display in the admin interface.
     *
     * @return string
     */
    public function get_name() {
        return get_string('monthlyexporttask', 'local_ard_csvexport');
    }

    /**
     * Execute the scheduled task.
     */
    public function execute() {
        global $DB, $CFG;

        mtrace('Starting monthly CSV export task...');

        try {
            // Clean up old temporary files first
            $this->cleanup_old_temp_files();

            // Get configuration settings.
            $sftphost = get_config('local_ard_csvexport', 'sftphost');
            $sftpport = get_config('local_ard_csvexport', 'sftpport') ?: 22;
            $sftpusername = get_config('local_ard_csvexport', 'sftpusername');
            $sftppassword = get_config('local_ard_csvexport', 'sftppassword');
            $sftppath = get_config('local_ard_csvexport', 'sftppath') ?: '/';
            $enabled = get_config('local_ard_csvexport', 'enabled');

            if (!$enabled) {
                mtrace('CSV export is disabled in settings.');
                return;
            }

            if (empty($sftphost) || empty($sftpusername) || empty($sftppassword)) {
                mtrace('ERROR: SFTP configuration is incomplete. Please check plugin settings.');
                return;
            }

            // Generate and upload first CSV file (course completions).
            $completionsdata = $this->generate_course_completions_data();
            if (!empty($completionsdata)) {
                $completionsfilename = $this->generate_filename('completions');
                $completionstempfile = $this->create_temp_csv_file($completionsdata, $completionsfilename);
                
                mtrace("Course completions CSV file created: $completionsfilename (" . filesize($completionstempfile) . " bytes)");
                
                $sftpclient = new sftp_client();
                $success1 = $sftpclient->upload_file(
                    $sftphost,
                    $sftpport,
                    $sftpusername,
                    $sftppassword,
                    $completionstempfile,
                    $sftppath . '/' . $completionsfilename
                );
                
                unlink($completionstempfile);
                
                if ($success1) {
                    mtrace("Course completions CSV file successfully uploaded: $completionsfilename");
                } else {
                    mtrace("ERROR: Failed to upload course completions CSV file.");
                }
            } else {
                mtrace('No course completions data to export.');
            }

            // Generate and upload second CSV file (course details).
            $coursesdata = $this->generate_course_details_data();
            if (!empty($coursesdata)) {
                $coursesfilename = $this->generate_filename('courses');
                $coursestempfile = $this->create_temp_csv_file($coursesdata, $coursesfilename);
                
                mtrace("Course details CSV file created: $coursesfilename (" . filesize($coursestempfile) . " bytes)");
                
                $sftpclient = new sftp_client();
                $success2 = $sftpclient->upload_file(
                    $sftphost,
                    $sftpport,
                    $sftpusername,
                    $sftppassword,
                    $coursestempfile,
                    $sftppath . '/' . $coursesfilename
                );
                
                unlink($coursestempfile);
                
                if ($success2) {
                    mtrace("Course details CSV file successfully uploaded: $coursesfilename");
                } else {
                    mtrace("ERROR: Failed to upload course details CSV file.");
                }
            } else {
                mtrace('No course details data to export.');
            }

        } catch (\Exception $e) {
            mtrace("ERROR: " . $e->getMessage());
        }

        mtrace('Monthly CSV export task completed.');
    }

/**
     * Generate course completions CSV data.
     *
     * @return array Array of course completion data rows
     */
    private function generate_course_completions_data() {
        global $DB;

        // Get the first and last day of the previous month
        $firstday = strtotime('first day of previous month');
        $lastday = strtotime('last day of previous month') + (24 * 60 * 60) - 1; // End of day

        mtrace("Extracting course completions for previous month: " . date('Y-m-d', $firstday) . " to " . date('Y-m-d', $lastday));

        $sql = "
         SELECT 
                LOWER(u.email) as STUD_ID, 
                'COURSE' as CPNT_TYP_ID, 
                CONCAT('CPNT_MERC_', cc.course) as CPNT_ID, 
                'COURSE_COMPLETE' as CMPL_STAT_ID,
                CONCAT(
                    UPPER(DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted), '%b')), '-',         
                    DATE_FORMAT(FROM_UNIXTIME(cc.timecompleted), '%d-%Y %H:%i:%s'),       
                    ' UTC'
                ) AS COMPL_DTE
            FROM {course_completions} cc
inner join {user} u on cc.userid=u.id and u.institution like 'caldic' and u.username <> 'caldic_review'
inner join {course} c on cc.course=c.id
inner join {customfield_data} cd1 on cc.course=cd1.instanceid and cd1.fieldid in (select id from {customfield_field} cf1 where shortname='course_type')
left  join {customfield_data} cd2 on cc.course=cd2.instanceid and cd2.fieldid in (select id from {customfield_field} cf2 where shortname='mono_course_type')
            WHERE cc.timecompleted IS NOT NULL
                AND cc.timecompleted >= :firstday
                AND cc.timecompleted <= :lastday
and 
(
	cd1.value=2  -- è un LP
	OR
	(cd1.value=1 and cd2.value=4) -- è uno SCORM
)
            ORDER BY cc.timecompleted, cc.course
        ";

        $params = [
            'firstday' => $firstday,
            'lastday' => $lastday
        ];

        try {
            $records = $DB->get_records_sql($sql, $params);
            mtrace("Extracted " . count($records) . " course completion records from previous month.");
            return $records;
        } catch (\Exception $e) {
            mtrace("ERROR in course completions SQL query: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate course details CSV data for courses that had completions in previous month.
     *
     * @return array Array of course details data rows
     */
    private function generate_course_details_data() {
        global $DB;

        // Get the first and last day of the previous month
        $firstday = strtotime('first day of previous month');
        $lastday = strtotime('last day of previous month') + (24 * 60 * 60) - 1; // End of day

        mtrace("Extracting course details for courses completed in previous month: " . date('Y-m-d', $firstday) . " to " . date('Y-m-d', $lastday));

        $sql = "
            SELECT DISTINCT
                CONCAT('CPNT_MERC_', cc.course) as CPNT_ID,
                'ONLINE' as CPNT_TYP_ID,
                CONCAT(
                    UPPER(DATE_FORMAT(FROM_UNIXTIME(c.timecreated), '%b')), '-',         
                    DATE_FORMAT(FROM_UNIXTIME(c.timecreated), '%d-%Y %H:%i:%s'),       
                    ' UTC'
                ) AS REV_DTE,
                'BU_EUR' as DMN_ID,
                IF(c.visible = 1, 'Y', 'N') as NOTACTIVE,
                'COURSE_COMPLETE' as CMPL_STAT_ID,
                c.shortname as CPNT_TITLE
            FROM {course_completions} cc
            INNER JOIN {user} u ON cc.userid = u.id 
                AND u.institution = 'caldic' 
                AND u.username <> 'caldic_review'
            INNER JOIN {course} c ON cc.course = c.id
            INNER JOIN {customfield_data} cd1 ON cc.course = cd1.instanceid 
                AND cd1.fieldid IN (
                    SELECT id FROM {customfield_field} cf1 WHERE shortname = 'course_type'
                )
            LEFT JOIN {customfield_data} cd2 ON cc.course = cd2.instanceid 
                AND cd2.fieldid IN (
                    SELECT id FROM {customfield_field} cf2 WHERE shortname = 'mono_course_type'
                )
            WHERE cc.timecompleted IS NOT NULL
                AND cc.timecompleted >= :firstday
                AND cc.timecompleted <= :lastday
                AND (
                    cd1.value = '2'  -- è un LP
                    OR
                    (cd1.value = '1' AND cd2.value = '4') -- è uno SCORM
                )
            ORDER BY cc.course
        ";

        $params = [
            'firstday' => $firstday,
            'lastday' => $lastday
        ];

        try {
            $records = $DB->get_records_sql($sql, $params);
            mtrace("Extracted " . count($records) . " course detail records for courses completed in previous month.");
            return $records;
        } catch (\Exception $e) {
            mtrace("ERROR in course details SQL query: " . $e->getMessage());
            return [];
        }
    }

    /**
     * Generate filename with month reference for different file types.
     *
     * @param string $type Type of file ('completions' or 'courses')
     * @return string
     */
    private function generate_filename($type = 'completions') {
        // Generate filename based on the PREVIOUS MONTH being exported (not current time)
        $previousmonth = strtotime('first day of previous month');
        $monthyear = date('Y-m', $previousmonth);
        
        switch ($type) {
            case 'completions':
                return "caldic_course_completions_{$monthyear}.csv";
            case 'courses':
                return "caldic_course_details_{$monthyear}.csv";
            default:
                return "caldic_export_{$monthyear}.csv";
        }
    }
	
    /**
     * Create temporary CSV file in moodledata directory.
     *
     * @param array $data Data to write to CSV
     * @param string $filename Filename for logging
     * @return string Path to temporary file
     */
    private function create_temp_csv_file($data, $filename) {
        global $CFG;

        // Use moodledata directory for load-balanced environments
        $tempdir = $CFG->dataroot . '/temp/ard_csvexport';
        
        // Ensure the directory exists with proper permissions
        if (!is_dir($tempdir)) {
            if (!mkdir($tempdir, 0755, true)) {
                throw new \Exception("Cannot create temporary directory: $tempdir");
            }
            
            // Add .htaccess file for security (prevent direct web access)
            $htaccessfile = $tempdir . '/.htaccess';
            file_put_contents($htaccessfile, "deny from all\n");
            chmod($htaccessfile, 0644);
        }

        $tempfile = $tempdir . '/' . $filename;
        $handle = fopen($tempfile, 'w');

        if (!$handle) {
            throw new \Exception("Cannot create temporary file: $tempfile");
        }

        mtrace("Creating temporary CSV file in moodledata: $tempfile");

        // Write CSV header if data exists.
        if (!empty($data)) {
            $firstrow = reset($data);
            if (is_object($firstrow)) {
                $firstrow = (array) $firstrow;
            }
            $headers = array_keys($firstrow);
            fputcsv($handle, $headers);

            // Write data rows.
            foreach ($data as $row) {
                if (is_object($row)) {
                    $row = (array) $row;
                }
                fputcsv($handle, $row);
            }
        }

        fclose($handle);
        
        // Verify file was created successfully
        if (!file_exists($tempfile)) {
            throw new \Exception("Temporary file was not created successfully: $tempfile");
        }
        
        mtrace("Temporary CSV file created successfully: " . filesize($tempfile) . " bytes");
        return $tempfile;
    }

    /**
     * Clean up old temporary files to prevent accumulation in load-balanced environments.
     */
    private function cleanup_old_temp_files() {
        global $CFG;

        $tempdir = $CFG->dataroot . '/temp/ard_csvexport';
        
        if (!is_dir($tempdir)) {
            return; // Nothing to clean
        }

        // Remove files older than 24 hours
        $cutofftime = time() - (24 * 60 * 60);
        $cleanedfiles = 0;

        if ($handle = opendir($tempdir)) {
            while (false !== ($file = readdir($handle))) {
                if ($file == '.' || $file == '..') {
                    continue;
                }
                
                $filepath = $tempdir . '/' . $file;
                if (is_file($filepath) && filemtime($filepath) < $cutofftime) {
                    if (unlink($filepath)) {
                        $cleanedfiles++;
                    }
                }
            }
            closedir($handle);
        }

        if ($cleanedfiles > 0) {
            mtrace("Cleaned up $cleanedfiles old temporary files from moodledata");
        }
    }
}
