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
 * Admin settings for local_euronics_preinserimento.
 *
 * @package    local_euronics_preinserimento
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage(
        'local_euronics_preinserimento',
        get_string('pluginname', 'local_euronics_preinserimento')
    );

    // Heading.
    $settings->add(new admin_setting_heading(
        'local_euronics_preinserimento/heading',
        get_string('settings_heading', 'local_euronics_preinserimento'),
        get_string('settings_heading_desc', 'local_euronics_preinserimento')
    ));

    // Course ID: Sicurezza Specifica.
    $settings->add(new admin_setting_configtext(
        'local_euronics_preinserimento/course_sic_spec',
        get_string('setting_course_sic_spec', 'local_euronics_preinserimento'),
        get_string('setting_course_sic_spec_desc', 'local_euronics_preinserimento'),
        '',
        PARAM_INT
    ));

    // Course ID: Sicurezza Aggiornamento.
    $settings->add(new admin_setting_configtext(
        'local_euronics_preinserimento/course_sic_agg',
        get_string('setting_course_sic_agg', 'local_euronics_preinserimento'),
        get_string('setting_course_sic_agg_desc', 'local_euronics_preinserimento'),
        '',
        PARAM_INT
    ));

    // Course ID: Sicurezza Generale.
    $settings->add(new admin_setting_configtext(
        'local_euronics_preinserimento/course_sic_gen',
        get_string('setting_course_sic_gen', 'local_euronics_preinserimento'),
        get_string('setting_course_sic_gen_desc', 'local_euronics_preinserimento'),
        '',
        PARAM_INT
    ));

    // Support email.
    $settings->add(new admin_setting_configtext(
        'local_euronics_preinserimento/support_email',
        get_string('setting_support_email', 'local_euronics_preinserimento'),
        get_string('setting_support_email_desc', 'local_euronics_preinserimento'),
        '',
        PARAM_EMAIL
    ));

    // Company profile field shortname.
    $settings->add(new admin_setting_configtext(
        'local_euronics_preinserimento/company_field',
        get_string('setting_company_field', 'local_euronics_preinserimento'),
        get_string('setting_company_field_desc', 'local_euronics_preinserimento'),
        '',
        PARAM_ALPHANUMEXT
    ));

    $ADMIN->add('localplugins', $settings);
}
