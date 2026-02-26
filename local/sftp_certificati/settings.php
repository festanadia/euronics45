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
 * Admin settings for the SFTP Certificati plugin.
 *
 * @package    local_sftp_certificati
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_sftp_certificati',
        get_string('pluginname', 'local_sftp_certificati'));

    // -------------------------------------------------------------------------
    // SFTP Connection settings.
    // -------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('local_sftp_certificati/sftp_heading',
        get_string('sftp_heading', 'local_sftp_certificati'),
        get_string('sftp_heading_desc', 'local_sftp_certificati')));

    $settings->add(new admin_setting_configtext('local_sftp_certificati/sftp_host',
        get_string('sftp_host', 'local_sftp_certificati'),
        get_string('sftp_host_desc', 'local_sftp_certificati'),
        '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_sftp_certificati/sftp_port',
        get_string('sftp_port', 'local_sftp_certificati'),
        get_string('sftp_port_desc', 'local_sftp_certificati'),
        '22', PARAM_INT));

    $settings->add(new admin_setting_configtext('local_sftp_certificati/sftp_username',
        get_string('sftp_username', 'local_sftp_certificati'),
        get_string('sftp_username_desc', 'local_sftp_certificati'),
        '', PARAM_TEXT));

    $settings->add(new admin_setting_configtext('local_sftp_certificati/sftp_keyfile',
        get_string('sftp_keyfile', 'local_sftp_certificati'),
        get_string('sftp_keyfile_desc', 'local_sftp_certificati'),
        'server.ppk', PARAM_FILE));

    $settings->add(new admin_setting_configpasswordunmask('local_sftp_certificati/sftp_keypassphrase',
        get_string('sftp_keypassphrase', 'local_sftp_certificati'),
        get_string('sftp_keypassphrase_desc', 'local_sftp_certificati'),
        ''));

    // -------------------------------------------------------------------------
    // Moodle authentication (for certificate PDF downloads).
    // -------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('local_sftp_certificati/auth_heading',
        get_string('auth_heading', 'local_sftp_certificati'),
        get_string('auth_heading_desc', 'local_sftp_certificati')));

    $settings->add(new admin_setting_configtext('local_sftp_certificati/moodle_auth_user',
        get_string('moodle_auth_user', 'local_sftp_certificati'),
        get_string('moodle_auth_user_desc', 'local_sftp_certificati'),
        '', PARAM_USERNAME));

    $settings->add(new admin_setting_configpasswordunmask('local_sftp_certificati/moodle_auth_pass',
        get_string('moodle_auth_pass', 'local_sftp_certificati'),
        get_string('moodle_auth_pass_desc', 'local_sftp_certificati'),
        ''));

    // -------------------------------------------------------------------------
    // Per-company SFTP path settings.
    // -------------------------------------------------------------------------
    $settings->add(new admin_setting_heading('local_sftp_certificati/companies_heading',
        get_string('companies_heading', 'local_sftp_certificati'),
        get_string('companies_heading_desc', 'local_sftp_certificati')));

    $companies = \local_sftp_certificati\helper::COMPANIES;
    foreach ($companies as $key => $name) {
        // Aziendasocia code(s) for this company.
        $settings->add(new admin_setting_configtext(
            'local_sftp_certificati/aziendasocia_' . $key,
            get_string('aziendasocia', 'local_sftp_certificati', $name),
            get_string('aziendasocia_desc', 'local_sftp_certificati'),
            '', PARAM_TEXT));

        // SFTP base path for this company.
        $settings->add(new admin_setting_configtext(
            'local_sftp_certificati/sftp_path_' . $key,
            get_string('sftp_path', 'local_sftp_certificati', $name),
            get_string('sftp_path_desc', 'local_sftp_certificati'),
            '', PARAM_RAW_TRIMMED));
    }

    $ADMIN->add('localplugins', $settings);
}
