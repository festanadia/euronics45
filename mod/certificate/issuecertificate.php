<?php

// This file is part of the Certificate module for Moodle - http://moodle.org/
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
 * Handles viewing a certificate
 *
 * @package    mod_certificate
 * @copyright  Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once("../../config.php");
require_once("$CFG->dirroot/mod/certificate/locallib.php");
require_once("$CFG->dirroot/mod/certificate/deprecatedlib.php");
require_once("$CFG->libdir/pdflib.php");
global $DB;

$id = required_param('id', PARAM_INT);    
$userid = required_param('userid', PARAM_INT);

$user = $DB->get_record('user', array('id' => $userid));

if (!$cm = get_coursemodule_from_id('certificate', $id)) {
    print_error('Course Module ID was incorrect');
}
if (!$course = $DB->get_record('course', array('id' => $cm->course))) {
    print_error('course is misconfigured');
}
if (!$certificate = $DB->get_record('certificate', array('id' => $cm->instance))) {
    print_error('course module is incorrect');
}

// NF - bypass per reportistica
// require_login($course, false, $cm);
$context = context_module::instance($cm->id);
// NF - bypass per reportistica
//require_capability('mod/certificate:view', $context);

$event = \mod_certificate\event\course_module_viewed::create(array(
            'objectid' => $certificate->id,
            'context' => $context,
        ));
$event->add_record_snapshot('course', $course);
$event->add_record_snapshot('certificate', $certificate);
$event->trigger();

$completion = new completion_info($course);
$completion->set_module_viewed($cm);

// Initialize $PAGE, compute blocks
$PAGE->set_url('/mod/certificate/view.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($certificate->name));
$PAGE->set_heading(format_string($course->fullname));




// Check if the user can view the certificate
if ($certificate->requiredtime && !has_capability('mod/certificate:manage', $context)) {
    if (certificate_get_course_time($course->id) < ($certificate->requiredtime * 60)) {
        $a = new stdClass;
        $a->requiredtime = $certificate->requiredtime;
        notice(get_string('requiredtimenotmet', 'certificate', $a), "$CFG->wwwroot/course/view.php?id=$course->id");
        die;
    }
}

// No debugging here, sorry.
$CFG->debugdisplay = 0;
@ini_set('display_errors', '0');
@ini_set('log_errors', '1');

make_cache_directory('tcpdf');
$tempuser=$USER;
$USER=$user;
//Generate certificate if not present
$certrecord = certificate_get_issue($course, $user, $certificate, $cm);
$context = \context_module::instance($cm->id);
$filename = certificate_get_certificate_filename($certificate, $cm, $course) . '.pdf';
require("$CFG->dirroot/mod/certificate/type/$certificate->certificatetype/certificate.php");
$filecontents = $pdf->Output('', 'S');
if ($certificate->savecert == 1 || $certificate->archive == 1) {
    certificate_save_pdf($filecontents, $certrecord->id, $filename, $context->id, $certrecord, $certificate->savecert, $certificate->archive);
}
$USER=$tempuser;



if ($certificate->delivery == 0) {
    // Open in browser.
    send_file($filecontents, $filename, 0, 0, true, false, 'application/pdf');
} elseif ($certificate->delivery == 1) {
    // Force download.
    send_file($filecontents, $filename, 0, 0, true, true, 'application/pdf');
}

/*
$id = required_param('id', PARAM_INT);

$certificate=$DB->get_record('certificate_issues',array('id'=>$id));

echo $certificate->pdf;*/


