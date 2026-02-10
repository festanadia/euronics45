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
 * Main page for user pre-registration.
 *
 * @package    local_euronics_preinserimento
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/user/lib.php');
require_once($CFG->dirroot . '/lib/enrollib.php');

require_login();

$context = context_system::instance();
require_capability('local/euronics_preinserimento:insertuser', $context);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/euronics_preinserimento/index.php'));
$PAGE->set_pagelayout('standard');
$PAGE->set_title(get_string('pagetitle', 'local_euronics_preinserimento'));
$PAGE->set_heading(get_string('pagetitle', 'local_euronics_preinserimento'));

$isadmin   = local_euronics_preinserimento_is_admin_user();
$companies = local_euronics_preinserimento_get_companies();

// Determine the company for this session.
if ($isadmin) {
    // Admin users select the company from a dropdown.
    // Pre-selected company code may come from URL (after a successful insertion).
    $preselectedcompany = optional_param('company', '', PARAM_ALPHANUMEXT);
    $company = null; // Will be resolved from form data on submit.
} else {
    // Regular HR users: auto-detect company from profile.
    $resolved = local_euronics_preinserimento_resolve_user_company();
    if (empty($resolved)) {
        // Company not found or not in the configured list.
        echo $OUTPUT->header();
        $supportemail = get_config('local_euronics_preinserimento', 'support_email');
        $errormsg = get_string('error_no_company', 'local_euronics_preinserimento');
        if (!empty($supportemail)) {
            $errormsg .= '<br>' . get_string('error_contact_support', 'local_euronics_preinserimento',
                $supportemail);
        }
        echo $OUTPUT->notification($errormsg, \core\output\notification::NOTIFY_ERROR);
        echo $OUTPUT->footer();
        die();
    }
    $company = $resolved;
}

// Build form with custom data.
$formcustomdata = [
    'is_admin'  => $isadmin,
    'companies' => $companies,
];
$form = new \local_euronics_preinserimento\form\insert_user_form(null, $formcustomdata);

// Pre-select company for admin users (from URL parameter after successful insertion).
if ($isadmin && !empty($preselectedcompany)) {
    $form->set_data(['company_code' => $preselectedcompany]);
}

if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
}

if ($data = $form->get_data()) {
    // Resolve the company from form data.
    if ($isadmin) {
        $code = $data->company_code;
        if (!isset($companies[$code])) {
            throw new moodle_exception('error_company_required', 'local_euronics_preinserimento');
        }
        $company = ['code' => $code, 'name' => $companies[$code]];
    }

    try {
        $result = local_euronics_preinserimento_create_user($data, $company);

        echo $OUTPUT->header();

        // Company label.
        echo html_writer::tag('div', $company['name'], ['class' => 'euronics-title']);
        echo html_writer::tag('div',
            get_string('pagetitle', 'local_euronics_preinserimento'),
            ['class' => 'euronics-subtitle']);

        // Success message.
        $successdata = new stdClass();
        $successdata->fullname = fullname($result->user);
        $successdata->username = $result->user->username;
        echo $OUTPUT->notification(
            get_string('success_message', 'local_euronics_preinserimento', $successdata),
            \core\output\notification::NOTIFY_SUCCESS
        );

        // Enrolled courses list.
        if (!empty($result->enrolled_courses)) {
            echo $OUTPUT->notification(
                get_string('success_enrolled_courses', 'local_euronics_preinserimento',
                    implode(', ', $result->enrolled_courses)),
                \core\output\notification::NOTIFY_INFO
            );
        }

        // Reminders.
        echo html_writer::start_div('euronics-badge-info mt-3 mb-2');
        echo get_string('success_reminder_file', 'local_euronics_preinserimento');
        echo html_writer::end_div();

        echo html_writer::start_div('euronics-badge-info mb-3');
        echo get_string('success_reminder_schedule', 'local_euronics_preinserimento');
        echo html_writer::end_div();

        // Link to insert another user (preserve company selection for admins).
        $urlparams = [];
        if ($isadmin) {
            $urlparams['company'] = $company['code'];
        }
        $url = new moodle_url('/local/euronics_preinserimento/index.php', $urlparams);
        echo html_writer::link($url,
            get_string('success_insert_another', 'local_euronics_preinserimento'),
            ['class' => 'btn btn-euronics mt-2']);

        echo $OUTPUT->footer();
        die();

    } catch (moodle_exception $e) {
        echo $OUTPUT->header();
        $supportemail = get_config('local_euronics_preinserimento', 'support_email');
        $errormsg = get_string('error_generic', 'local_euronics_preinserimento');
        if (!empty($supportemail)) {
            $errormsg .= '<br>' . get_string('error_contact_support', 'local_euronics_preinserimento',
                $supportemail);
        }
        echo $OUTPUT->notification($errormsg, \core\output\notification::NOTIFY_ERROR);
        $form->display();
        echo $OUTPUT->footer();
        die();
    }
}

// Display the form.
echo $OUTPUT->header();

if (!$isadmin) {
    // Show auto-detected company label above the form.
    echo html_writer::tag('div', $company['name'], ['class' => 'euronics-title']);
}
echo html_writer::tag('div',
    get_string('pagetitle', 'local_euronics_preinserimento'),
    ['class' => 'euronics-subtitle']);

$form->display();

echo $OUTPUT->footer();


/**
 * Create a new Moodle user and enrol in the selected safety courses.
 *
 * @param stdClass $data    Form data.
 * @param array    $company Company array with 'code' and 'name' keys.
 * @return stdClass Object with 'user' and 'enrolled_courses' properties.
 * @throws moodle_exception On failure.
 */
function local_euronics_preinserimento_create_user(stdClass $data, array $company): stdClass {
    global $DB, $CFG;

    // Build username from fiscal code (lowercase).
    $username = strtolower(trim($data->fiscalcode));

    // Check uniqueness again (race condition guard).
    if ($DB->record_exists('user', ['username' => $username, 'deleted' => 0])) {
        throw new moodle_exception('error_fiscalcode_exists', 'local_euronics_preinserimento');
    }

    // Create the user record.
    $newuser = new stdClass();
    $newuser->username    = $username;
    $newuser->auth        = 'manual';
    $newuser->confirmed   = 1;
    $newuser->mnethostid  = $CFG->mnet_localhost_id;
    $newuser->firstname   = trim($data->firstname);
    $newuser->lastname    = trim($data->lastname);
    $newuser->email       = $username . '@placeholder.local';
    $newuser->institution = $company['name'];
    $newuser->timecreated = time();
    $newuser->timemodified = time();

    // Generate a random password (user will not log in directly with it).
    $newuser->password = hash_internal_user_password(random_string(15));

    $newuser->id = user_create_user($newuser, false, false);

    // Store the company name in the custom profile field if configured.
    $fieldshortname = get_config('local_euronics_preinserimento', 'company_field');
    if (!empty($fieldshortname)) {
        $fieldid = $DB->get_field('user_info_field', 'id', ['shortname' => $fieldshortname]);
        if ($fieldid) {
            $infodata = new stdClass();
            $infodata->userid = $newuser->id;
            $infodata->fieldid = $fieldid;
            $infodata->data = $company['name'];
            $DB->insert_record('user_info_data', $infodata);
        }
    }

    // Enrol in selected courses.
    $enrolled = [];

    if (!empty($data->sic_spec)) {
        $courseid = get_config('local_euronics_preinserimento', 'course_sic_spec');
        if (!empty($courseid) && $DB->record_exists('course', ['id' => $courseid])) {
            local_euronics_preinserimento_enrol_user($newuser->id, $courseid);
            $course = $DB->get_record('course', ['id' => $courseid], 'fullname');
            $enrolled[] = $course->fullname;
        }
    }

    if (!empty($data->sic_agg)) {
        $courseid = get_config('local_euronics_preinserimento', 'course_sic_agg');
        if (!empty($courseid) && $DB->record_exists('course', ['id' => $courseid])) {
            local_euronics_preinserimento_enrol_user($newuser->id, $courseid);
            $course = $DB->get_record('course', ['id' => $courseid], 'fullname');
            $enrolled[] = $course->fullname;
        }
    }

    $result = new stdClass();
    $result->user = $DB->get_record('user', ['id' => $newuser->id]);
    $result->enrolled_courses = $enrolled;
    return $result;
}

/**
 * Enrol a user in a course using the manual enrolment plugin.
 *
 * @param int $userid   The user ID.
 * @param int $courseid The course ID.
 */
function local_euronics_preinserimento_enrol_user(int $userid, int $courseid): void {
    global $DB;

    $enrolinstance = $DB->get_record('enrol', [
        'courseid' => $courseid,
        'enrol' => 'manual',
        'status' => ENROL_INSTANCE_ENABLED,
    ], '*', IGNORE_MULTIPLE);

    if (!$enrolinstance) {
        return;
    }

    $enrolplugin = enrol_get_plugin('manual');
    $enrolplugin->enrol_user($enrolinstance, $userid, $enrolinstance->roleid);
}
