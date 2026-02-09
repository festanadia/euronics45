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
 * Class definition for the Recently accessed courses block.
 *
 * @package    block_aelrecentlyaccessedcourses
 * @copyright  2018 Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
require_once($CFG->dirroot. '/course/lib.php');
defined('MOODLE_INTERNAL') || die();

/**
 * Recently accessed courses block class.
 *
 * @package    block_aelrecentlyaccessedcourses
 * @copyright  Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_aelrecentlyaccessedcourses extends block_base {
    /**
     * Initialize class member variables
     */
    public function init() {
        global $USER;
        $courses = course_get_recent_courses($USER->id);
        if( count($courses) == 0 ) $this->title='';
        else $this->title = get_string('aelmsgwelcome', 'block_aelrecentlyaccessedcourses');
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        if (isset($this->content)) {
            return $this->content;
        }

        $courses = course_get_recent_courses($USER->id);
        if( count($courses) == 0 ) return;
        $renderable = new block_aelrecentlyaccessedcourses\output\main();
        $renderer = $this->page->get_renderer('block_aelrecentlyaccessedcourses');

        $this->content = new stdClass();
        $this->content->text = $renderer->render($renderable);
        $this->content->footer = '';

        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return true;
    }

    /**
     * Return the plugin config settings for external functions.
     *
     * @return stdClass the configs for both the block instance and plugin
     * @since Moodle 3.8
     */
    public function get_config_for_external() {
        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = get_config('block_aelrecentlyaccessedcourses');

        return (object) [
            'instance' => new stdClass(),
            'plugin' => $configs,
        ];
    }
}
