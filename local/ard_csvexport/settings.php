<?php
// local/ard_csvexport/settings.php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_ard_csvexport', get_string('pluginname', 'local_ard_csvexport'));

    // Enable/disable the plugin.
    $settings->add(new admin_setting_configcheckbox(
        'local_ard_csvexport/enabled',
        get_string('enabled', 'local_ard_csvexport'),
        get_string('enabled_desc', 'local_ard_csvexport'),
        1
    ));

    // SFTP Server Settings header.
    $settings->add(new admin_setting_heading(
        'local_ard_csvexport/sftpheading',
        get_string('sftpsettings', 'local_ard_csvexport'),
        get_string('sftpsettings_desc', 'local_ard_csvexport')
    ));

    // SFTP hostname.
    $settings->add(new admin_setting_configtext(
        'local_ard_csvexport/sftphost',
        get_string('sftphost', 'local_ard_csvexport'),
        get_string('sftphost_desc', 'local_ard_csvexport'),
        '',
        PARAM_HOST
    ));

    // SFTP port.
    $settings->add(new admin_setting_configtext(
        'local_ard_csvexport/sftpport',
        get_string('sftpport', 'local_ard_csvexport'),
        get_string('sftpport_desc', 'local_ard_csvexport'),
        '22',
        PARAM_INT
    ));

    // SFTP username.
    $settings->add(new admin_setting_configtext(
        'local_ard_csvexport/sftpusername',
        get_string('sftpusername', 'local_ard_csvexport'),
        get_string('sftpusername_desc', 'local_ard_csvexport'),
        '',
        PARAM_USERNAME
    ));

    // SFTP password.
    $settings->add(new admin_setting_configpasswordunmask(
        'local_ard_csvexport/sftppassword',
        get_string('sftppassword', 'local_ard_csvexport'),
        get_string('sftppassword_desc', 'local_ard_csvexport'),
        ''
    ));

    // SFTP remote path.
    $settings->add(new admin_setting_configtext(
        'local_ard_csvexport/sftppath',
        get_string('sftppath', 'local_ard_csvexport'),
        get_string('sftppath_desc', 'local_ard_csvexport'),
        '/',
        PARAM_PATH
    ));

    // Task Schedule Information (read-only)
    $settings->add(new admin_setting_heading(
        'local_ard_csvexport/scheduleheading',
        get_string('scheduleinfo', 'local_ard_csvexport'),
        get_string('scheduleinfo_desc', 'local_ard_csvexport')
    ));

    // Add to the admin tree.
    $ADMIN->add('localplugins', $settings);
}