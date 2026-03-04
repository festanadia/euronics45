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
 * Library functions for the local_ard_csvexport plugin.
 *
 * @package    local_ard_csvexport
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Add settings navigation for the plugin.
 *
 * @param navigation_node $settingsnav The settings navigation node
 * @param context $context The current context
 */
function local_ard_csvexport_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Only add to site administration context.
    if ($context->contextlevel == CONTEXT_SYSTEM && has_capability('moodle/site:config', $context)) {
        if ($pluginnode = $settingsnav->find('localplugins', navigation_node::TYPE_CATEGORY)) {
            $url = new moodle_url('/admin/settings.php', ['section' => 'local_ard_csvexport']);
            $node = navigation_node::create(
                get_string('pluginname', 'local_ard_csvexport'),
                $url,
                navigation_node::NODETYPE_LEAF,
                'ard_csvexport',
                'ard_csvexport'
            );
            $pluginnode->add_node($node);
        }
    }
}
