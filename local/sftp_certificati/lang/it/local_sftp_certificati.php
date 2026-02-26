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
 * Italian language strings.
 *
 * @package    local_sftp_certificati
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'SFTP Certificati';

// Scheduled task.
$string['task_sync_certificates'] = 'Sincronizzazione certificati sicurezza su SFTP';

// SFTP connection settings.
$string['sftp_heading']           = 'Connessione SFTP';
$string['sftp_heading_desc']      = 'Parametri di connessione al server SFTP dove verranno caricati i certificati.';
$string['sftp_host']              = 'Host SFTP';
$string['sftp_host_desc']         = 'Nome host o indirizzo IP del server SFTP.';
$string['sftp_port']              = 'Porta SFTP';
$string['sftp_port_desc']         = 'Numero di porta per la connessione SFTP (predefinito: 22).';
$string['sftp_username']          = 'Utente SFTP';
$string['sftp_username_desc']     = 'Nome utente per l\'autenticazione SFTP.';
$string['sftp_keyfile']           = 'File chiave PPK';
$string['sftp_keyfile_desc']      = 'Nome del file di chiave privata .ppk. Il file deve essere posizionato nella directory <code>keys/</code> del plugin (<code>local/sftp_certificati/keys/</code>).';
$string['sftp_keypassphrase']     = 'Passphrase chiave';
$string['sftp_keypassphrase_desc'] = 'Passphrase del file di chiave PPK. Lasciare vuoto se la chiave non ha una passphrase.';

// Moodle authentication settings.
$string['auth_heading']           = 'Autenticazione Moodle';
$string['auth_heading_desc']      = 'Credenziali di un account Moodle utilizzato per scaricare i PDF dei certificati. L\'account deve avere il permesso di visualizzare i certificati di tutti gli utenti.';
$string['moodle_auth_user']       = 'Utente Moodle';
$string['moodle_auth_user_desc']  = 'Nome utente dell\'account Moodle.';
$string['moodle_auth_pass']       = 'Password Moodle';
$string['moodle_auth_pass_desc']  = 'Password dell\'account Moodle.';

// Company settings.
$string['companies_heading']      = 'Percorsi aziende socie';
$string['companies_heading_desc'] = 'Per ciascuna azienda socia, inserire il/i codice/i <em>aziendasocia</em> presenti nelle tabelle dei report e il percorso SFTP di base. Le sotto-cartelle <code>CERTIFICATI_SICUREZZA/GENERALE</code>, <code>CERTIFICATI_SICUREZZA/SPECIFICA</code> e <code>CERTIFICATI_SICUREZZA/AGGIORNAMENTO</code> verranno create automaticamente.';
$string['aziendasocia']           = 'Codice aziendasocia — {$a}';
$string['aziendasocia_desc']      = 'Codice/i nelle tabelle dei report per questa azienda (separare con virgola se più di uno).';
$string['sftp_path']              = 'Percorso SFTP — {$a}';
$string['sftp_path_desc']         = 'Percorso base della directory SFTP per questa azienda.';
