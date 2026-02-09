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
 * link enrol plugin external functions and service definitions.
 *
 * @package   enrol_link
 * @copyright 2013 Rajesh Taneja <rajesh@moodle.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since     Moodle 2.6
 */

$functions = array(
    'enrol_link_get_instance_info' => array(
        'classname'   => 'enrol_link_external',
        'methodname'  => 'get_instance_info',
        'classpath'   => 'enrol/link/externallib.php',
        'description' => 'link enrolment instance information.',
        'type'        => 'read',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),

    'enrol_link_enrol_user' => array(
        'classname'   => 'enrol_link_external',
        'methodname'  => 'enrol_user',
        'classpath'   => 'enrol/link/externallib.php',
        'description' => 'link enrol the current user in the given course.',
        'type'        => 'write',
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);
