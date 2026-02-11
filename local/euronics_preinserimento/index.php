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
require_once(__DIR__ . '/lib.php');

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
    'is_admin'             => $isadmin,
    'companies'            => $companies,
    'auto_enrol_companies' => local_euronics_preinserimento_get_auto_enrol_companies(),
    'company_code'         => $isadmin ? '' : $company['code'],
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
        $successdata->fullname = strtoupper(trim($data->firstname)) . ' ' . strtoupper(trim($data->lastname));
        $successdata->username = $result->username;
        echo $OUTPUT->notification(
            get_string('success_message', 'local_euronics_preinserimento', $successdata),
            \core\output\notification::NOTIFY_SUCCESS
        );

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
        $errormsg = $e->getMessage();
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
 * Create a new user: INSERT into exteuronics.eur_utenti + INSERT into mdl_user.
 *
 * @param stdClass $data    Form data.
 * @param array    $company Company array with 'code' and 'name' keys.
 * @return stdClass Object with 'username' (lowercase) property.
 * @throws moodle_exception On failure.
 */
function local_euronics_preinserimento_create_user(stdClass $data, array $company): stdClass {
    global $DB;

    $firstname   = strtoupper(trim($data->firstname));
    $lastname    = strtoupper(trim($data->lastname));
    $fiscalcode  = strtoupper(trim($data->fiscalcode));
    $companycode = $company['code'];
    $companyname = $company['name'];

    // Generate normalized username (uppercase).
    $usernameUpper = local_euronics_preinserimento_generate_username($data->firstname, $data->lastname);
    $usernameLower = strtolower($usernameUpper);

    // Uniqueness checks on eur_utenti (race condition guard).
    if (local_euronics_preinserimento_username_exists($usernameUpper)) {
        throw new moodle_exception('error_username_exists', 'local_euronics_preinserimento');
    }
    if (local_euronics_preinserimento_fiscalcode_exists($fiscalcode)) {
        throw new moodle_exception('error_fiscalcode_exists', 'local_euronics_preinserimento');
    }

    // Build course enrolment string ("1", "2", "12", or "").
    $coursestring = local_euronics_preinserimento_build_course_string(
        !empty($data->sic_spec),
        !empty($data->sic_agg)
    );

    $email = 'preinserimento@email.it';
    $now   = userdate(time(), '%d/%m/%Y %H:%M');

    $extdb = local_euronics_preinserimento_get_external_dbname();

    // 1) INSERT into exteuronics.eur_utenti.
    $sql1 = "INSERT INTO `{$extdb}`.`eur_utenti`
             (`username`, `codicefiscale`, `nome`, `cognome`, `aziendasocia`,
              `puntovendita`, `email`, `nazione`, `bitutente`, `stato`)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $DB->execute($sql1, [
        $usernameUpper,  // username (MAIUSCOLO).
        $fiscalcode,     // codicefiscale.
        $firstname,      // nome.
        $lastname,       // cognome.
        $companycode,    // aziendasocia (es. S03).
        'PDS',           // puntovendita.
        $email,          // email.
        $coursestring,    // nazione (stringa corsi: 1, 2, 12).
        '00000000',      // bitutente.
        'Active',        // stato.
    ]);

    // 2) INSERT into mdl_user.
    $moodleuser = new stdClass();
    $moodleuser->auth         = 'db';
    $moodleuser->confirmed    = 1;
    $moodleuser->policyagreed = 0;
    $moodleuser->deleted      = 0;
    $moodleuser->suspended    = 0;
    $moodleuser->mnethostid   = 1;
    $moodleuser->username     = $usernameLower; // username (minuscolo).
    $moodleuser->idnumber     = $fiscalcode;
    $moodleuser->firstname    = $firstname;
    $moodleuser->lastname     = $lastname;
    $moodleuser->email        = $email;
    $moodleuser->institution  = $companyname;
    $moodleuser->department   = 'Personale di Sede';
    $moodleuser->description  = 'utente preinserito il ' . $now;
    $moodleuser->timecreated  = time();
    $moodleuser->timemodified = time();

    $DB->insert_record('user', $moodleuser);

    $result = new stdClass();
    $result->username = $usernameLower;
    return $result;
}
