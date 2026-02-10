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
 * Italian language strings for local_euronics_preinserimento.
 *
 * @package    local_euronics_preinserimento
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Euronics - Inserimento utenti';

// Navigation.
$string['menuitem'] = 'Inserimento utenti';

// Page.
$string['pagetitle'] = 'Inserimento nuovo utente';
$string['company_label'] = 'Azienda';

// Form: sections.
$string['section_anagrafici'] = 'Dati anagrafici';
$string['section_corsi'] = 'Iscrizione ai corsi di sicurezza';

// Form: fields.
$string['firstname'] = 'Nome';
$string['lastname'] = 'Cognome';
$string['fiscalcode'] = 'Codice fiscale';
$string['fiscalcode_help'] = 'Inserire il codice fiscale senza spazi (16 caratteri alfanumerici).';
$string['course_sic_spec'] = 'Sicurezza Specifica';
$string['course_sic_spec_help'] = 'Seleziona se l\'utente deve svolgere il corso di sicurezza specifica.';
$string['course_sic_agg'] = 'Sicurezza Aggiornamento';
$string['course_sic_agg_help'] = 'Seleziona se l\'utente deve svolgere il corso di aggiornamento.';
$string['course_sic_gen_info'] = 'L\'utente sarà automaticamente iscritto a <strong>Sicurezza Generale</strong>, se previsto per l\'azienda.';
$string['submit'] = 'Inserisci';

// Validation.
$string['error_fiscalcode_invalid'] = 'Il codice fiscale deve contenere esattamente 16 caratteri alfanumerici.';
$string['error_fiscalcode_exists'] = 'Esiste già un utente con questo codice fiscale.';
$string['error_firstname_required'] = 'Il campo Nome è obbligatorio.';
$string['error_lastname_required'] = 'Il campo Cognome è obbligatorio.';

// Success.
$string['success_title'] = 'Utente creato con successo';
$string['success_message'] = 'L\'utente <strong>{$a->fullname}</strong> è stato creato correttamente.<br>Username assegnata: <strong>{$a->username}</strong>';
$string['success_reminder_file'] = 'Ricorda di inserire l\'utente anche nel file di anagrafiche ordinario, per evitare che l\'account venga disattivato alla successiva elaborazione del file.';
$string['success_reminder_schedule'] = 'L\'elaborazione dei dati inseriti avverrà alle <strong>14:00</strong> e alle <strong>20:00</strong>: solo dopo questi orari i nuovi utenti potranno effettuare l\'accesso in piattaforma.';
$string['success_enrolled_courses'] = 'L\'utente è stato iscritto ai seguenti corsi: {$a}';
$string['success_insert_another'] = 'Inserisci un altro utente';

// Errors.
$string['error_title'] = 'Errore durante la creazione';
$string['error_generic'] = 'Si è verificato un errore durante la creazione dell\'utente. Contattare il supporto all\'indirizzo email indicato per la risoluzione.';
$string['error_no_company'] = 'Non è possibile determinare l\'azienda di appartenenza. Contattare il supporto.';
$string['error_nopermission'] = 'Non disponi dei permessi necessari per inserire utenti.';

// Settings.
$string['settings_heading'] = 'Configurazione Inserimento Utenti';
$string['settings_heading_desc'] = 'Configura i corsi di sicurezza e le opzioni per il pre-inserimento utenti.';
$string['setting_course_sic_spec'] = 'ID Corso Sicurezza Specifica';
$string['setting_course_sic_spec_desc'] = 'ID del corso Moodle per la Sicurezza Specifica.';
$string['setting_course_sic_agg'] = 'ID Corso Sicurezza Aggiornamento';
$string['setting_course_sic_agg_desc'] = 'ID del corso Moodle per la Sicurezza Aggiornamento.';
$string['setting_course_sic_gen'] = 'ID Corso Sicurezza Generale';
$string['setting_course_sic_gen_desc'] = 'ID del corso Moodle per la Sicurezza Generale (iscrizione automatica).';
$string['setting_support_email'] = 'Email supporto';
$string['setting_support_email_desc'] = 'Indirizzo email di supporto da mostrare nei messaggi di errore.';
$string['setting_company_field'] = 'Campo profilo azienda';
$string['setting_company_field_desc'] = 'Nome dello shortname del campo profilo personalizzato che contiene l\'azienda dell\'utente (es. "company").';
