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
 * English language strings.
 *
 * @package    local_sftp_certificati
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'SFTP Certificati';

// Scheduled task.
$string['task_sync_certificates'] = 'Sync safety certificates to SFTP';

// SFTP connection settings.
$string['sftp_heading']           = 'SFTP Connection';
$string['sftp_heading_desc']      = 'Connection parameters for the SFTP server where certificates will be uploaded.';
$string['sftp_host']              = 'SFTP Host';
$string['sftp_host_desc']         = 'Hostname or IP address of the SFTP server.';
$string['sftp_port']              = 'SFTP Port';
$string['sftp_port_desc']         = 'Port number for the SFTP connection (default: 22).';
$string['sftp_username']          = 'SFTP Username';
$string['sftp_username_desc']     = 'Username for SFTP authentication.';
$string['sftp_keyfile']           = 'PPK Key File';
$string['sftp_keyfile_desc']      = 'Filename of the .ppk private key. The file must be placed in the plugin\'s <code>keys/</code> directory (<code>local/sftp_certificati/keys/</code>).';
$string['sftp_keypassphrase']     = 'Key Passphrase';
$string['sftp_keypassphrase_desc'] = 'Passphrase for the PPK key file. Leave empty if the key has no passphrase.';

// Moodle authentication settings.
$string['auth_heading']           = 'Moodle Authentication';
$string['auth_heading_desc']      = 'Credentials of a Moodle account used to download the certificate PDFs. The account must have permission to view all users\' certificates.';
$string['moodle_auth_user']       = 'Moodle Username';
$string['moodle_auth_user_desc']  = 'Username of the Moodle account.';
$string['moodle_auth_pass']       = 'Moodle Password';
$string['moodle_auth_pass_desc']  = 'Password of the Moodle account.';

// Company settings.
$string['companies_heading']      = 'Partner Company Paths';
$string['companies_heading_desc'] = 'For each partner company, enter the <em>aziendasocia</em> code(s) used in the report tables and the base SFTP directory. The sub-folders <code>CERTIFICATI_SICUREZZA/GENERALE</code>, <code>CERTIFICATI_SICUREZZA/SPECIFICA</code> and <code>CERTIFICATI_SICUREZZA/AGGIORNAMENTO</code> will be created automatically.';
$string['aziendasocia']           = 'Aziendasocia code — {$a}';
$string['aziendasocia_desc']      = 'Code(s) in the report tables for this company (comma-separated if more than one).';
$string['sftp_path']              = 'SFTP path — {$a}';
$string['sftp_path_desc']         = 'Base SFTP directory path for this company.';
