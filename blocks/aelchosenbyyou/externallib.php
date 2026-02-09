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
class ael_chosenbyyou_category_external extends external_api {
    
    /**
     * Returns description of method parameters
     *
     * @return external_function_parameters
     * @since Moodle 2.3
     */
    public static function ael_chosenbyyouael__category_parameters() {
        return new external_function_parameters(
            array(
                'options' => new external_single_structure(
                    array(
                        'ids' => new external_value(PARAM_INT, 'id')
                    )
                )
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
    public static function ael_chosenbyyouael__category($options = array()) {
        global $CFG, $DB;
        require_once($CFG->dirroot . "/course/lib.php");

        //validate parameter
        $params = self::validate_parameters(self::ael_chosenbyyouael__category_parameters(),
                        array('options' => $options));


        $category = $DB->get_record('course_categories', array('id'=>$params['options']['ids']));
        $categoryinfo = Array();
        $categoryinfo['id'] = $category->id; 
        $categoryinfo['name'] = $category->name;


      //  $categoryinfo = array_values($categoryinfo);

        return $categoryinfo;
    }

    /**
     * Returns description of method result value
     *
     * @return external_description
     * @since Moodle 2.2
     */
    public static function ael_chosenbyyouael__category_returns() {
        return 
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'id'),
                    'name' => new external_value(PARAM_RAW, 'name')
                )
        );
    }
}
