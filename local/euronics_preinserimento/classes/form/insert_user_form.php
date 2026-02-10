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
 * User pre-registration form.
 *
 * @package    local_euronics_preinserimento
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_euronics_preinserimento\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/formslib.php');

/**
 * Form for HR users to pre-register new users.
 *
 * Custom data expected:
 *  - 'is_admin'  (bool)   Whether the user is an admin operator.
 *  - 'companies' (array)  code => name pairs for the company dropdown (admin only).
 *  - 'company'   (string) Pre-selected company code (admin only, optional).
 */
class insert_user_form extends \moodleform {

    /**
     * Define form elements.
     */
    protected function definition() {
        $mform = $this->_form;
        $customdata = $this->_customdata;

        $isadmin   = !empty($customdata['is_admin']);
        $companies = $customdata['companies'] ?? [];

        // Admin users: company selector.
        if ($isadmin) {
            $options = ['' => get_string('select_company', 'local_euronics_preinserimento')];
            foreach ($companies as $code => $name) {
                $options[$code] = $name;
            }
            $mform->addElement('select', 'company_code',
                get_string('company_label', 'local_euronics_preinserimento'),
                $options);
            $mform->addRule('company_code',
                get_string('error_company_required', 'local_euronics_preinserimento'),
                'required', null, 'client');
            // Disallow the empty placeholder.
            $mform->addRule('company_code',
                get_string('error_company_required', 'local_euronics_preinserimento'),
                'nonzero', null, 'client');
        }

        // Section: personal data.
        $mform->addElement('header', 'header_anagrafici',
            get_string('section_anagrafici', 'local_euronics_preinserimento'));
        $mform->setExpanded('header_anagrafici', true);

        $mform->addElement('text', 'firstname',
            get_string('firstname', 'local_euronics_preinserimento'));
        $mform->setType('firstname', PARAM_TEXT);
        $mform->addRule('firstname',
            get_string('error_firstname_required', 'local_euronics_preinserimento'),
            'required', null, 'client');

        $mform->addElement('text', 'lastname',
            get_string('lastname', 'local_euronics_preinserimento'));
        $mform->setType('lastname', PARAM_TEXT);
        $mform->addRule('lastname',
            get_string('error_lastname_required', 'local_euronics_preinserimento'),
            'required', null, 'client');

        $mform->addElement('text', 'fiscalcode',
            get_string('fiscalcode', 'local_euronics_preinserimento'));
        $mform->setType('fiscalcode', PARAM_ALPHANUMEXT);
        $mform->addRule('fiscalcode',
            get_string('error_fiscalcode_invalid', 'local_euronics_preinserimento'),
            'required', null, 'client');
        $mform->addHelpButton('fiscalcode', 'fiscalcode', 'local_euronics_preinserimento');

        // Section: safety courses.
        $mform->addElement('header', 'header_corsi',
            get_string('section_corsi', 'local_euronics_preinserimento'));
        $mform->setExpanded('header_corsi', true);

        $mform->addElement('advcheckbox', 'sic_spec',
            get_string('course_sic_spec', 'local_euronics_preinserimento'),
            get_string('course_sic_spec_help', 'local_euronics_preinserimento'));

        $mform->addElement('advcheckbox', 'sic_agg',
            get_string('course_sic_agg', 'local_euronics_preinserimento'),
            get_string('course_sic_agg_help', 'local_euronics_preinserimento'));

        $mform->addElement('static', 'sic_gen_info', '',
            '<div class="euronics-badge-info">' .
            get_string('course_sic_gen_info', 'local_euronics_preinserimento') .
            '</div>');

        // Submit button.
        $mform->addElement('submit', 'submitbutton',
            get_string('submit', 'local_euronics_preinserimento'),
            ['class' => 'btn-euronics']);
    }

    /**
     * Server-side validation.
     *
     * @param array $data  Form data.
     * @param array $files Uploaded files.
     * @return array Validation errors.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate company selection for admin users.
        $isadmin = !empty($this->_customdata['is_admin']);
        if ($isadmin && empty($data['company_code'])) {
            $errors['company_code'] = get_string('error_company_required',
                'local_euronics_preinserimento');
        }

        // Validate fiscal code format: exactly 16 alphanumeric characters.
        $cf = strtoupper(trim($data['fiscalcode']));
        if (!preg_match('/^[A-Z0-9]{16}$/', $cf)) {
            $errors['fiscalcode'] = get_string('error_fiscalcode_invalid',
                'local_euronics_preinserimento');
        }

        // Check fiscal code uniqueness on eur_utenti.
        if (empty($errors['fiscalcode'])) {
            if (local_euronics_preinserimento_fiscalcode_exists($cf)) {
                $errors['fiscalcode'] = get_string('error_fiscalcode_exists',
                    'local_euronics_preinserimento');
            }
        }

        // Check username uniqueness on eur_utenti.
        if (empty($errors['firstname']) && empty($errors['lastname'])) {
            $username = local_euronics_preinserimento_generate_username(
                $data['firstname'], $data['lastname']
            );
            if (local_euronics_preinserimento_username_exists($username)) {
                $errors['lastname'] = get_string('error_username_exists',
                    'local_euronics_preinserimento');
            }
        }

        return $errors;
    }
}
