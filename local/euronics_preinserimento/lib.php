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
 * Library functions for local_euronics_preinserimento.
 *
 * @package    local_euronics_preinserimento
 * @copyright  2026 Euronics
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add navigation node to the navigation tree.
 *
 * @param global_navigation $navigation The global navigation instance.
 */
function local_euronics_preinserimento_extend_navigation(global_navigation $navigation) {
    global $USER, $PAGE;

    if (!isloggedin() || isguestuser()) {
        return;
    }

    $context = \context_system::instance();
    if (!has_capability('local/euronics_preinserimento:insertuser', $context)) {
        return;
    }

    $url = new \moodle_url('/local/euronics_preinserimento/index.php');
    $node = $navigation->add(
        get_string('menuitem', 'local_euronics_preinserimento'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'euronics_preinserimento',
        new \pix_icon('i/user', '')
    );
    $node->showinflatnavigation = true;
}

/**
 * Parse the configured partner companies list.
 *
 * Returns an associative array code => name, e.g. ['S03' => 'BRUNO SPA', ...].
 *
 * @return array<string, string>
 */
function local_euronics_preinserimento_get_companies(): array {
    $raw = get_config('local_euronics_preinserimento', 'companies');
    if (empty($raw)) {
        return [];
    }

    $companies = [];
    $lines = explode("\n", $raw);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '|') === false) {
            continue;
        }
        [$code, $name] = explode('|', $line, 2);
        $code = trim($code);
        $name = trim($name);
        if ($code !== '' && $name !== '') {
            $companies[$code] = $name;
        }
    }
    return $companies;
}

/**
 * Get the list of admin usernames from plugin settings.
 *
 * @return string[]
 */
function local_euronics_preinserimento_get_admin_users(): array {
    $raw = get_config('local_euronics_preinserimento', 'admin_users');
    if (empty($raw)) {
        return [];
    }
    $users = [];
    foreach (explode("\n", $raw) as $line) {
        $line = trim($line);
        if ($line !== '') {
            $users[] = $line;
        }
    }
    return $users;
}

/**
 * Check if the current user is an admin operator (can choose any company).
 *
 * @return bool
 */
function local_euronics_preinserimento_is_admin_user(): bool {
    global $USER;
    return in_array($USER->username, local_euronics_preinserimento_get_admin_users(), true);
}

/**
 * Get the list of company codes that have automatic safety enrolment.
 *
 * @return string[]
 */
function local_euronics_preinserimento_get_auto_enrol_companies(): array {
    $raw = get_config('local_euronics_preinserimento', 'auto_enrol_companies');
    if (empty($raw)) {
        return [];
    }
    $codes = [];
    foreach (explode(',', $raw) as $code) {
        $code = trim($code);
        if ($code !== '') {
            $codes[] = $code;
        }
    }
    return $codes;
}

/**
 * Check whether the given company code has automatic safety enrolment.
 *
 * @param string $companycode The company code (e.g. 'S03').
 * @return bool
 */
function local_euronics_preinserimento_has_auto_enrol(string $companycode): bool {
    return in_array($companycode, local_euronics_preinserimento_get_auto_enrol_companies(), true);
}

/**
 * Get the external database name from settings.
 *
 * @return string
 */
function local_euronics_preinserimento_get_external_dbname(): string {
    $dbname = get_config('local_euronics_preinserimento', 'external_dbname');
    return !empty($dbname) ? $dbname : 'exteuronics';
}

/**
 * Generate a normalized username from first name and last name.
 *
 * Format: FIRSTNAME.LASTNAME (uppercase), with spaces removed and
 * accented characters transliterated to ASCII equivalents.
 *
 * @param string $firstname First name.
 * @param string $lastname  Last name.
 * @return string Normalized username in uppercase.
 */
function local_euronics_preinserimento_generate_username(string $firstname, string $lastname): string {
    $first = strtoupper(trim($firstname));
    $last  = strtoupper(trim($lastname));

    // Remove spaces within name parts.
    $first = str_replace(' ', '', $first);
    $last  = str_replace(' ', '', $last);

    $username = $first . '.' . $last;

    // Transliterate accented characters to ASCII equivalents.
    $translitmap = [
        'À' => 'A', 'Á' => 'A', 'Â' => 'A', 'Ã' => 'A', 'Ä' => 'A', 'Å' => 'A',
        'È' => 'E', 'É' => 'E', 'Ê' => 'E', 'Ë' => 'E',
        'Ì' => 'I', 'Í' => 'I', 'Î' => 'I', 'Ï' => 'I',
        'Ò' => 'O', 'Ó' => 'O', 'Ô' => 'O', 'Õ' => 'O', 'Ö' => 'O',
        'Ù' => 'U', 'Ú' => 'U', 'Û' => 'U', 'Ü' => 'U',
        'Ñ' => 'N', 'Ç' => 'C',
        'à' => 'A', 'á' => 'A', 'â' => 'A', 'ã' => 'A', 'ä' => 'A', 'å' => 'A',
        'è' => 'E', 'é' => 'E', 'ê' => 'E', 'ë' => 'E',
        'ì' => 'I', 'í' => 'I', 'î' => 'I', 'ï' => 'I',
        'ò' => 'O', 'ó' => 'O', 'ô' => 'O', 'õ' => 'O', 'ö' => 'O',
        'ù' => 'U', 'ú' => 'U', 'û' => 'U', 'ü' => 'U',
        'ñ' => 'N', 'ç' => 'C',
    ];
    $username = strtr($username, $translitmap);

    // Remove any remaining non-ASCII or special characters (keep A-Z, 0-9, dot).
    $username = preg_replace('/[^A-Z0-9.]/', '', $username);

    return $username;
}

/**
 * Build the course enrolment string based on checkbox selections.
 *
 * "1" = Sicurezza Specifica, "2" = Sicurezza Aggiornamento, "12" = both.
 *
 * @param bool $sicspec  Sicurezza Specifica selected.
 * @param bool $sicagg   Sicurezza Aggiornamento selected.
 * @return string The enrolment string.
 */
function local_euronics_preinserimento_build_course_string(bool $sicspec, bool $sicagg): string {
    $result = '';
    if ($sicspec) {
        $result .= '1';
    }
    if ($sicagg) {
        $result .= '2';
    }
    return $result;
}

/**
 * Check if a username already exists in the external eur_utenti table.
 *
 * @param string $username Username in uppercase.
 * @return bool
 */
function local_euronics_preinserimento_username_exists(string $username): bool {
    global $DB;
    $extdb = local_euronics_preinserimento_get_external_dbname();
    $sql = "SELECT COUNT(*) AS cnt FROM `{$extdb}`.`eur_utenti` WHERE `username` = ?";
    $result = $DB->get_record_sql($sql, [$username]);
    return $result && $result->cnt > 0;
}

/**
 * Check if a fiscal code already exists in the external eur_utenti table.
 *
 * @param string $fiscalcode Fiscal code in uppercase.
 * @return bool
 */
function local_euronics_preinserimento_fiscalcode_exists(string $fiscalcode): bool {
    global $DB;
    $extdb = local_euronics_preinserimento_get_external_dbname();
    $sql = "SELECT COUNT(*) AS cnt FROM `{$extdb}`.`eur_utenti` WHERE `codicefiscale` = ?";
    $result = $DB->get_record_sql($sql, [$fiscalcode]);
    return $result && $result->cnt > 0;
}

/**
 * Resolve the company for the current user.
 *
 * For admin users: returns null (they must select from the dropdown).
 * For regular HR users: reads the profile field and matches it against
 * the configured partner companies list.
 *
 * @return array{code: string, name: string}|null Matched company or null.
 */
function local_euronics_preinserimento_resolve_user_company(): ?array {
    global $USER;

    if (local_euronics_preinserimento_is_admin_user()) {
        return null;
    }

    $companyvalue = local_euronics_preinserimento_get_user_field_value($USER->id);
    if (empty($companyvalue)) {
        return null;
    }

    // Match against the configured companies (by name).
    $companies = local_euronics_preinserimento_get_companies();
    foreach ($companies as $code => $name) {
        if (strcasecmp($name, $companyvalue) === 0) {
            return ['code' => $code, 'name' => $name];
        }
    }

    return null;
}

/**
 * Get the company field value for a user (custom profile field or institution).
 *
 * @param int $userid The user ID.
 * @return string|null The raw field value.
 */
function local_euronics_preinserimento_get_user_field_value(int $userid): ?string {
    global $DB;

    // Try the configured custom profile field first.
    $fieldshortname = get_config('local_euronics_preinserimento', 'company_field');
    if (!empty($fieldshortname)) {
        $sql = "SELECT uid.data
                  FROM {user_info_data} uid
                  JOIN {user_info_field} uif ON uif.id = uid.fieldid
                 WHERE uid.userid = :userid
                   AND uif.shortname = :shortname";
        $record = $DB->get_record_sql($sql, [
            'userid' => $userid,
            'shortname' => $fieldshortname,
        ]);
        if ($record && !empty($record->data)) {
            return $record->data;
        }
    }

    // Fall back to the standard institution field.
    $user = $DB->get_record('user', ['id' => $userid], 'institution');
    if ($user && !empty($user->institution)) {
        return $user->institution;
    }

    return null;
}
