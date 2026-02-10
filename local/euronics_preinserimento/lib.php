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
 * Get the company name for the given user.
 *
 * Reads the custom profile field configured in plugin settings.
 * Falls back to the standard 'institution' field.
 *
 * @param int $userid The user ID.
 * @return string|null The company name or null if not found.
 */
function local_euronics_preinserimento_get_user_company(int $userid): ?string {
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

/**
 * Check whether the company has automatic enrolment for General Safety.
 *
 * Checks if a self-enrolment method is active for the General Safety course.
 *
 * @param string $company The company name.
 * @return bool True if auto-enrolment is active.
 */
function local_euronics_preinserimento_has_auto_enrol_sic_gen(string $company): bool {
    global $DB;

    $courseid = get_config('local_euronics_preinserimento', 'course_sic_gen');
    if (empty($courseid)) {
        return false;
    }

    // Check if the course has an active self-enrolment instance.
    $enrolinstances = $DB->get_records('enrol', [
        'courseid' => $courseid,
        'enrol' => 'self',
        'status' => ENROL_INSTANCE_ENABLED,
    ]);

    return !empty($enrolinstances);
}
