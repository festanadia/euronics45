<?php
// local/ard_csvexport/lang/en/local_ard_csvexport.php
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'CSV Export';
$string['monthlyexporttask'] = 'Monthly CSV Export via SFTP (Completions + Course Details)';

// Settings page strings.
$string['enabled'] = 'Enable CSV Export';
$string['enabled_desc'] = 'Enable or disable the monthly CSV export functionality. This will generate two files: course completions and course details for the previous month.';

$string['sftpsettings'] = 'SFTP Server Settings';
$string['sftpsettings_desc'] = 'Configure the SFTP server connection details for file upload. Both CSV files will be uploaded to the same location.';

$string['sftphost'] = 'SFTP Host';
$string['sftphost_desc'] = 'The hostname or IP address of the SFTP server.';

$string['sftpport'] = 'SFTP Port';
$string['sftpport_desc'] = 'The port number for the SFTP server (default: 22).';

$string['sftpusername'] = 'SFTP Username';
$string['sftpusername_desc'] = 'Username for SFTP authentication.';

$string['sftppassword'] = 'SFTP Password';
$string['sftppassword_desc'] = 'Password for SFTP authentication.';

$string['sftppath'] = 'Remote Path';
$string['sftppath_desc'] = 'The remote directory path where CSV files will be uploaded (default: /). Both files will be placed in this directory.';

$string['scheduleinfo'] = 'Task Schedule Information';
$string['scheduleinfo_desc'] = 'The CSV export task is automatically scheduled to run on the 1st day of each month at 3:00 AM UTC. It extracts course completion data from the previous month and generates two files: course completions and course details. You can monitor the task execution in Site Administration → Server → Tasks → Scheduled tasks.';

// Privacy API strings.
$string['privacy:metadata'] = 'The CSV Export plugin does not store any personal data. It only exports data based on course completions from the previous month and generates two CSV files: completions and course details.';