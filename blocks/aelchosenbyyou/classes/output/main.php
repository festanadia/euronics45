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
 * Class containing data for the Recently accessed courses block.
 *
 * @package    block_aelchosenbyyou
 * @copyright  2018 Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace block_aelchosenbyyou\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;
use block_aelchosenbyyou;
use moodle_url;
use core_course\external\course_summary_exporter;
use core_availability\info;
use core_course\customfield\course_handler;


require_once("$CFG->libdir/externallib.php");
require_once($CFG->libdir."/customlib/mycourse_custom_enrollment.php");
require_once($CFG->libdir."/customlib/mycourse_custom_info.php");

use mycourse_enrollment\mycourse_custom_enrollment;
use mycourse_info\mycourse_custom_info;
/**
 * Class containing data for Recently accessed courses block.
 *
 * @package    block_aelchosenbyyou
 * @copyright  2018 Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass|array
     */
    public function export_for_template(renderer_base $output) {
        global $USER;

        $nocoursesurl = $output->image_url('courses', 'block_aelchosenbyyou')->out(false);
        $config = get_config('block_aelchosenbyyou');

        $array['courses'] = $this->get_ael_chosenyou_courses($options);
        $array['language'] = 
            [
                'nocourses'=> get_string('nocourses', 'block_aelchosenbyyou'), 
                'veditutti'=> get_string('veditutti', 'block_aelchosenbyyou'),
                'esplora_catalogo' => get_string('esplora_catalogo', 'block_aelchosenbyyou')
            ];
        if( count($array['courses']) == 0 ) {
            $array['nocourse'] = '';
            $array['nocoursecss'] = 'nocourse';
        }
        else 
            $array['nocourse'] = 'yescourse';
        return $array;
    }

    public function get_ael_chosenyou_courses($options = array()){
        global $CFG, $DB, $OUTPUT, $USER;
        require_once($CFG->dirroot . "/course/lib.php");


        $courses = mycourse_custom_enrollment::get_course_with_type_enrollment($USER->id, 'self');
        //create return value
        $coursesinfo = array();
        $i=0;
        foreach ($courses as $course) {
            $courseinfo = array();
            $courseinfo['index'] = $i;
            $i++;
            $courseinfo['id'] = $course->id;
            $courseinfo['fullname'] = $course->fullname; 
            $courseinfo['coursename'] = $course->fullname; 
            $courseinfo['shortname'] = $course->shortname;
            $courseinfo['categoryid'] = $course->category;
            $courseinfo['format'] = $course->format;
            $courseinfo['startdate'] = $course->startdate;
            $courseinfo['enddate'] = $course->enddate;
            $courseinfo['showactivitydates'] = $course->showactivitydates;
            $courseinfo['showcompletionconditions'] = $course->showcompletionconditions;
            $courseinfo['courseimage'] = \cache::make('core', 'course_image')->get($course->id);
            $courseinfo['enddate'] = $course->enddate;
            $courseinfo['progress'] = mycourse_custom_info::percentual_progress($course, $USER->id);
            if(is_null($courseinfo['courseimage']))
                $courseinfo['courseimage'] = $OUTPUT->get_generated_image_for_id($course->id);
            $courseinfo['viewurl'] = (new moodle_url('/course/view.php', array('id' => $course->id)))->out(false);
            $handler = \core_course\customfield\course_handler::create();
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
            foreach($courseinfo['customfields'] as $name){
                if(strcmp($name['shortname'], "durata")== 0)
                {
                    $courseinfo['customfields'] = $name['value'];
                }
            }
            $coursesinfo[] = $courseinfo;
        }

        $courses = array_values($coursesinfo);

        return $courses;
    }
}
