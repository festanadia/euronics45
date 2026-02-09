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
 * External course API
 *
 * @package    core_course
 * @category   external
 * @copyright  2009 Petr Skodak
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

use core_course\external\course_summary_exporter;
use core_availability\info;


require_once("$CFG->libdir/externallib.php");

/**
 * Course external functions
 *
 * @package    core_course
 * @category   external
 * @copyright  2011 Jerome Mouneyrac
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since Moodle 2.2
 */
class ael_course_external extends external_api {
    
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function get_ael_courses_parameters() {
        return new external_function_parameters(
                array('options' => new external_single_structure(
                            array('ids' => new external_multiple_structure(
                                        new external_value(PARAM_INT, 'Course id')
                                        , 'List of course id. If empty return all courses
                                            except front page course.',
                                        VALUE_OPTIONAL)
                            ), 'options - operator OR is used', VALUE_DEFAULT, array())
                )
        );
    }

    /**
     * Get courses
     *
     * @param array $options It contains an array (list of ids)
     * @return array
     * @since Moodle 2.2
     */
    public static function get_ael_courses($options = array()) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");

        //validate parameter
        $params = self::validate_parameters(self::get_ael_courses_parameters(),
                        array('options' => $options));

        //retrieve courses
        if (!array_key_exists('ids', $params['options'])
                or empty($params['options']['ids'])) {
            $courses = $DB->get_records('course');
        } else {
            $courses = $DB->get_records_list('course', 'id', $params['options']['ids']);
        }

        //create return value
        $coursesinfo = array();
        foreach ($courses as $course) {

            $courseinfo = array();
            $courseinfo['id'] = $course->id;
            $courseinfo['fullname'] = $course->fullname;
            $courseinfo['shortname'] = $course->shortname;
            $courseinfo['categoryid'] = $course->category;
            $courseinfo['startdate'] = $course->startdate;
            $courseinfo['enddate'] = $course->enddate;
            $courseinfo['showactivitydates'] = $course->showactivitydates;
            $courseinfo['showcompletionconditions'] = $course->showcompletionconditions;
 /*           if (array_key_exists('numsections', $courseformatoptions)) {
                // For backward-compartibility
                $courseinfo['numsections'] = $courseformatoptions['numsections'];
            }
*/
            $handler = core_course\customfield\course_handler::create();
            if ($customfields = $handler->export_instance_data($course->id)) {
                $courseinfo['customfields'] = [];
                foreach ($customfields as $data) {
                    $courseinfo['customfields'][] = [
                        'type' => $data->get_type(),
                        'value' => $data->get_value(),
                        'valueraw' => $data->get_data_controller()->get_value(),
                        'name' => $data->get_name(),
                        'shortname' => $data->get_shortname()
                    ];
                }
            }

            if (1) {
                $coursesinfo[] = $courseinfo;
            }
        }

        return $coursesinfo;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function get_ael_courses_returns() {
        return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'course id'),
                            'shortname' => new external_value(PARAM_RAW, 'course short name'),
                            'categoryid' => new external_value(PARAM_INT, 'category id'),
                            'fullname' => new external_value(PARAM_RAW, 'full name'),
                            'startdate' => new external_value(PARAM_INT,
                                    'timestamp when the course start'),
                            'enddate' => new external_value(PARAM_INT,
                                    'timestamp when the course end'),
                            'showactivitydates' => new external_value(PARAM_BOOL, 'Whether the activity dates are shown or not'),
                            'showcompletionconditions' => new external_value(PARAM_BOOL,
                                'Whether the activity completion conditions are shown or not'),
                            'customfields' => new external_multiple_structure(
                                new external_single_structure(
                                    ['name' => new external_value(PARAM_RAW, 'The name of the custom field'),
                                     'shortname' => new external_value(PARAM_ALPHANUMEXT, 'The shortname of the custom field'),
                                     'type'  => new external_value(PARAM_COMPONENT,
                                         'The type of the custom field - text, checkbox...'),
                                     'valueraw' => new external_value(PARAM_RAW, 'The raw value of the custom field'),
                                     'value' => new external_value(PARAM_RAW, 'The value of the custom field')]
                                ), 'Custom fields and associated values', VALUE_OPTIONAL),
                        ), 'course'
                )
        );
    }
}
