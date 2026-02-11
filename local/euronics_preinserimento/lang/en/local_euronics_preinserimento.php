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
 * English language strings for local_euronics_preinserimento.
 *
 * @package    local_euronics_preinserimento
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Euronics - User pre-registration';

// Navigation.
$string['menuitem'] = 'User registration';

// Page.
$string['pagetitle'] = 'New user registration';
$string['company_label'] = 'Company';
$string['select_company'] = '-- Select company --';

// Form: sections.
$string['section_anagrafici'] = 'Personal data';
$string['section_corsi'] = 'Safety course enrolment';

// Form: fields.
$string['firstname'] = 'First name';
$string['lastname'] = 'Last name';
$string['fiscalcode'] = 'Fiscal code';
$string['fiscalcode_help'] = 'Enter the fiscal code without spaces (16 alphanumeric characters).';
$string['course_sic_spec'] = 'Specific Safety';
$string['course_sic_spec_help'] = 'Select if the user must attend the specific safety course.';
$string['course_sic_agg'] = 'Safety Refresher';
$string['course_sic_agg_help'] = 'Select if the user must attend the safety refresher course.';
$string['course_sic_gen_info'] = 'The user will be automatically enrolled in <strong>General Safety</strong>';
$string['submit'] = 'Submit';

// Validation.
$string['error_fiscalcode_invalid'] = 'The fiscal code must contain exactly 16 alphanumeric characters.';
$string['error_fiscalcode_exists'] = 'A user with this fiscal code already exists.';
$string['error_firstname_required'] = 'First name is required.';
$string['error_lastname_required'] = 'Last name is required.';
$string['error_company_required'] = 'Please select a company.';
$string['error_username_exists'] = 'A user with this first name and last name combination already exists.';

// Success.
$string['success_title'] = 'User created successfully';
$string['success_message'] = 'The user <strong>{$a->fullname}</strong> has been created successfully.<br>Assigned username: <strong>{$a->username}</strong>';
$string['success_reminder_file'] = 'Remember to also add the user to the standard data file, to prevent the account from being deactivated during the next file processing.';
$string['success_reminder_schedule'] = 'Data processing occurs at <strong>14:00</strong> and <strong>20:00</strong>: new users will be able to access the platform only after these times.';
$string['success_enrolled_courses'] = 'The user has been enrolled in the following courses: {$a}';
$string['success_insert_another'] = 'Register another user';

// Errors.
$string['error_title'] = 'Error during creation';
$string['error_generic'] = 'An error occurred while creating the user.';
$string['error_no_company'] = 'Unable to determine the associated company. The company in your profile does not match any configured partner.';
$string['error_contact_support'] = 'Please contact support at: <strong>{$a}</strong>';
$string['error_nopermission'] = 'You do not have permission to register users.';

// Settings.
$string['settings_heading'] = 'User Pre-Registration Settings';
$string['settings_heading_desc'] = 'Configure partner companies and options for user pre-registration.';
$string['setting_admin_users'] = 'Admin usernames';
$string['setting_admin_users_desc'] = 'One username per line. These users will be able to select the company to operate for via a dropdown menu.';
$string['setting_companies'] = 'Partner companies list';
$string['setting_companies_desc'] = 'One company per line, format: CODE|NAME (e.g. S03|BRUNO SPA). The name must match the value in the HR user\'s profile field.';
$string['setting_auto_enrol_companies'] = 'Companies with automatic safety enrolment';
$string['setting_auto_enrol_companies_desc'] = 'Comma-separated company codes (e.g. S03,S04,S09). Users from these companies will be automatically enrolled in safety courses.';
$string['setting_support_email'] = 'Support email';
$string['setting_support_email_desc'] = 'Support email address displayed in error messages.';
$string['setting_external_dbname'] = 'External database name';
$string['setting_external_dbname_desc'] = 'Name of the external MySQL database containing the eur_utenti table (e.g. exteuronics).';
$string['setting_company_field'] = 'Company profile field';
$string['setting_company_field_desc'] = 'Shortname of the custom profile field that stores the user\'s company. If empty, the standard "institution" field will be used.';
