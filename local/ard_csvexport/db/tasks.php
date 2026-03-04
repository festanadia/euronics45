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
 * Task schedule configuration for the local_ard_csvexport plugin.
 *
 * @package    local_ard_csvexport
 * @copyright  2025 Your Organization
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$tasks = [
    [
        'classname' => 'local_ard_csvexport\task\monthly_csv_export',
        'blocking' => 0,                    // Non-blocking task
        'minute' => '0',                    // At minute 0
        'hour' => '3',                      // At 3:00 AM
        'day' => '1',                       // First day of month
        'dayofweek' => '*',                 // Any day of week
        'month' => '*'                      // Every month
    ]
];
