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
 * Activity completion condition.
 *
 * @package availability_advancedcompletion
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace availability_advancedcompletion;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

/**
 * Activity completion condition.
 *
 * @package availability_advancedcompletion
 * @copyright 2014 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class condition extends \core_availability\condition {
    /** @var int ID of module that this depends on */
    protected $cmid;

    /** @var int Expected completion type (one of the COMPLETE_xx constants) */
    protected $expectedcompletion;

    /** @var array Array of modules used in these conditions for course */
    protected static $modsusedincondition = array();
    
        /** @var string Availabile only from specified date. */
    const DIRECTION_FROM = '>=';

    /** @var string Availabile only until specified date. */
    const DIRECTION_UNTIL = '<';
    
        /** @var string Availabile only until specified date. */
    const DIRECTION_NONE = 'not applied';

    /** @var string One of the DIRECTION_xx constants. */
    private $direction;

    /** @var int Time (Unix epoch seconds) for condition. */
    private $time;

    /** @var int Forced current time (for unit tests) or 0 for normal. */
    private static $forcecurrenttime = 0;

    /**
     * Constructor.
     *
     * @param \stdClass $structure Data structure from JSON decode
     * @throws \coding_exception If invalid data structure.
     */
    public function __construct($structure) {
        // Get cmid.
        if (isset($structure->cm) && is_int($structure->cm)) {
            $this->cmid = $structure->cm;
        } else {
            throw new \coding_exception('Missing or invalid ->cm for completion condition');
        }

        // Get expected completion.
        if (isset($structure->e) && in_array($structure->e,
                array(COMPLETION_COMPLETE, COMPLETION_INCOMPLETE,
                        COMPLETION_COMPLETE_PASS, COMPLETION_COMPLETE_FAIL))) {
            $this->expectedcompletion = $structure->e;
        } else {
            throw new \coding_exception('Missing or invalid ->e for completion condition');
        }
        
        // Get direction.
        if (isset($structure->d) && in_array($structure->d,
                array(self::DIRECTION_NONE,self::DIRECTION_FROM, self::DIRECTION_UNTIL))) {
            $this->direction = $structure->d;
        } else {
            throw new \coding_exception('Missing or invalid ->d for date condition');
        }

        // Get time.
        if (isset($structure->t) && is_int($structure->t)) {
            $this->time = $structure->t;
        } else {
            throw new \coding_exception('Missing or invalid ->t for date condition');
        }
    }

    public function save() {
        return (object)array('type' => 'advancedcompletion',
                'cm' => $this->cmid, 'e' => $this->expectedcompletion,
                'd' => $this->direction, 't' => $this->time);
    }

    /**
     * Returns a JSON object which corresponds to a condition of this type.
     *
     * Intended for unit testing, as normally the JSON values are constructed
     * by JavaScript code.
     *
     * @param int $cmid Course-module id of other activity
     * @param int $expectedcompletion Expected completion value (COMPLETION_xx)
     * @return stdClass Object representing condition
     */
    public static function get_json($cmid, $expectedcompletion) {
        return (object)array('type' => 'advancedcompletion', 'cm' => (int)$cmid,
                'e' => (int)$expectedcompletion,'date', 'd' => $direction, 't' => (int)$time);
    }

    public function is_available($not, \core_availability\info $info, $grabthelot, $userid) {
        $modinfo = $info->get_modinfo();
        $completion = new \completion_info($modinfo->get_course());
        if (!array_key_exists($this->cmid, $modinfo->cms)) {
            // If the cmid cannot be found, always return false regardless
            // of the condition or $not state. (Will be displayed in the
            // information message.)
            $allow = false;
        } else {
            // The completion system caches its own data so no caching needed here.
            $completiondata = $completion->get_data((object)array('id' => $this->cmid),
                    $grabthelot, $userid, $modinfo);

            $allow = true;
            if ($this->expectedcompletion == COMPLETION_COMPLETE) {
                // Complete also allows the pass, fail states.
                switch ($completiondata->completionstate) {
                    case COMPLETION_COMPLETE:
                    case COMPLETION_COMPLETE_FAIL:
                    case COMPLETION_COMPLETE_PASS:
                        break;
                    default:
                        $allow = false;
                }
            } else {
                // Other values require exact match.
                if ($completiondata->completionstate != $this->expectedcompletion) {
                    $allow = false;
                }
            }

            if ($not) {
                $allow = !$allow;
            }
        }
        $allowdate = true;
        // Check condition.
        $acttime = $completiondata->timemodified;
        switch ($this->direction) {
            case self::DIRECTION_NONE:
                break;
            case self::DIRECTION_FROM:
                $allowdate = $acttime >= $this->time;
                break;
            case self::DIRECTION_UNTIL:
                $allowdate = $acttime < $this->time;
                break;
            default:
                throw new \coding_exception('Unexpected direction');
        }
        if ($not) {
            $allowdate = !$allowdate;
        }
        $allowreturn = $allow && $allowdate;
        return $allowreturn;
    }
    
        /**
     * Obtains the actual direction of checking based on the $not value.
     *
     * @param bool $not True if condition is negated
     * @return string Direction constant
     * @throws \coding_exception
     */
    protected function get_logical_direction($not) {
        switch ($this->direction) {
            case self::DIRECTION_NONE:
                return self::DIRECTION_NONE;
            case self::DIRECTION_FROM:
                return $not ? self::DIRECTION_UNTIL : self::DIRECTION_FROM;
            case self::DIRECTION_UNTIL:
                return $not ? self::DIRECTION_FROM : self::DIRECTION_UNTIL;
            default:
                throw new \coding_exception('Unexpected direction');
        }
    }
    


    public function get_standalone_description(
            $full, $not, \core_availability\info $info) {
        return $this->get_either_description($not, true);
    }


    /**
     * Returns a more readable keyword corresponding to a completion state.
     *
     * Used to make lang strings easier to read.
     *
     * @param int $completionstate COMPLETION_xx constant
     * @return string Readable keyword
     */
    protected static function get_lang_string_keyword($completionstate) {
        switch($completionstate) {
            case COMPLETION_INCOMPLETE:
                return 'incomplete';
            case COMPLETION_COMPLETE:
                return 'complete';
            case COMPLETION_COMPLETE_PASS:
                return 'complete_pass';
            case COMPLETION_COMPLETE_FAIL:
                return 'complete_fail';
            default:
                throw new \coding_exception('Unexpected completion state: ' . $completionstate);
        }
    }

    public function get_description($full, $not, \core_availability\info $info) {
        // Get name for module.
        $modinfo = $info->get_modinfo();
        if (!array_key_exists($this->cmid, $modinfo->cms)) {
            $modname = get_string('missing', 'availability_advancedcompletion');
        } else {
            $modname = '<AVAILABILITY_CMNAME_' . $modinfo->cms[$this->cmid]->id . '/>';
        }

        // Work out which lang string to use.
        if ($not) {
            // Convert NOT strings to use the equivalent where possible.
            switch ($this->expectedcompletion) {
                case COMPLETION_INCOMPLETE:
                    $str = 'requires_' . self::get_lang_string_keyword(COMPLETION_COMPLETE);
                    break;
                case COMPLETION_COMPLETE:
                    $str = 'requires_' . self::get_lang_string_keyword(COMPLETION_INCOMPLETE);
                    break;
                default:
                    // The other two cases do not have direct opposites.
                    $str = 'requires_not_' . self::get_lang_string_keyword($this->expectedcompletion);
                    break;
            }
        } else {
            $str = 'requires_' . self::get_lang_string_keyword($this->expectedcompletion);
        }
        
        $return = $this->get_either_description($not, false);

        return get_string($str, 'availability_advancedcompletion', $modname). ' '.$return;
    }
    
        /**
     * Shows the description using the different lang strings for the standalone
     * version or the full one.
     *
     * @param bool $not True if NOT is in force
     * @param bool $standalone True to use standalone lang strings
     */
    protected function get_either_description($not, $standalone) {
        $direction = $this->get_logical_direction($not);
        $midnight = self::is_midnight($this->time);
        $midnighttag = $midnight ? '_date' : '';
        $satag = $standalone ? 'short_' : 'full_';
        switch ($direction) {
            case self::DIRECTION_NONE:
                return  get_string('direction_none', 'availability_advancedcompletion');;
            case self::DIRECTION_FROM:
                return get_string($satag . 'from' . $midnighttag, 'availability_date',
                        self::show_time($this->time, $midnight, false));
            case self::DIRECTION_UNTIL:
                return get_string($satag . 'until' . $midnighttag, 'availability_date',
                        self::show_time($this->time, $midnight, true));
        }
    }


    protected function get_debug_string() {
        switch ($this->expectedcompletion) {
            case COMPLETION_COMPLETE :
                $type = 'COMPLETE';
                break;
            case COMPLETION_INCOMPLETE :
                $type = 'INCOMPLETE';
                break;
            case COMPLETION_COMPLETE_PASS:
                $type = 'COMPLETE_PASS';
                break;
            case COMPLETION_COMPLETE_FAIL:
                $type = 'COMPLETE_FAIL';
                break;
            default:
                throw new \coding_exception('Unexpected expected completion');
        }
        return 'cm' . $this->cmid . ' ' . $type.' '.$this->direction . ' ' . gmdate('Y-m-d H:i:s', $this->time);
    }

    public function update_after_restore($restoreid, $courseid, \base_logger $logger, $name) {
        global $DB;
        $rec = \restore_dbops::get_backup_ids_record($restoreid, 'course_module', $this->cmid);
        if (!$rec || !$rec->newitemid) {
            // If we are on the same course (e.g. duplicate) then we can just
            // use the existing one.
            if ($DB->record_exists('course_modules',
                    array('id' => $this->cmid, 'course' => $courseid))) {
                return false;
            }
            // Otherwise it's a warning.
            $this->cmid = 0;
            $logger->process('Restored item (' . $name .
                    ') has availability condition on module that was not restored',
                    \backup::LOG_WARNING);
        } else {
            $this->cmid = (int)$rec->newitemid;
        }
        

        $dateoffset = \core_availability\info::get_restore_date_offset($restoreid);
        if ($dateoffset) {
            $this->time += $dateoffset;
            return true;
        }
        return true;
    }

    /**
     * Used in course/lib.php because we need to disable the completion JS if
     * a completion value affects a conditional activity.
     *
     * @param \stdClass $course Moodle course object
     * @param int $cmid Course-module id
     * @return bool True if this is used in a condition, false otherwise
     */
    public static function completion_value_used($course, $cmid) {
        // Have we already worked out a list of required completion values
        // for this course? If so just use that.
        if (!array_key_exists($course->id, self::$modsusedincondition)) {
            // We don't have data for this course, build it.
            $modinfo = get_fast_modinfo($course);
            self::$modsusedincondition[$course->id] = array();

            // Activities.
            foreach ($modinfo->cms as $othercm) {
                if (is_null($othercm->availability)) {
                    continue;
                }
                $ci = new \core_availability\info_module($othercm);
                $tree = $ci->get_availability_tree();
                foreach ($tree->get_all_children('availability_advancedcompletion\condition') as $cond) {
                    self::$modsusedincondition[$course->id][$cond->cmid] = true;
                }
            }

            // Sections.
            foreach ($modinfo->get_section_info_all() as $section) {
                if (is_null($section->availability)) {
                    continue;
                }
                $ci = new \core_availability\info_section($section);
                $tree = $ci->get_availability_tree();
                foreach ($tree->get_all_children('availability_advancedcompletion\condition') as $cond) {
                    self::$modsusedincondition[$course->id][$cond->cmid] = true;
                }
            }
        }
        return array_key_exists($cmid, self::$modsusedincondition[$course->id]);
    }

    /**
     * Wipes the static cache of modules used in a condition (for unit testing).
     */
    public static function wipe_static_cache() {
        self::$modsusedincondition = array();
    }

    public function update_dependency_id($table, $oldid, $newid) {
        if ($table === 'course_modules' && (int)$this->cmid === (int)$oldid) {
            $this->cmid = $newid;
            return true;
        } else {
            return false;
        }
    }
    
        /**
     * Gets time. This function is implemented here rather than calling time()
     * so that it can be overridden in unit tests. (Would really be nice if
     * Moodle had a generic way of doing that, but it doesn't.)
     *
     * @return int Current time (seconds since epoch)
     */
    protected static function get_time() {
        if (self::$forcecurrenttime) {
            return self::$forcecurrenttime;
        } else {
            return time();
        }
    }

    /**
     * Forces the current time for unit tests.
     *
     * @param int $forcetime Time to return from the get_time function
     */
    public static function set_current_time_for_test($forcetime = 0) {
        self::$forcecurrenttime = $forcetime;
    }

    /**
     * Shows a time either as a date or a full date and time, according to
     * user's timezone.
     *
     * @param int $time Time
     * @param bool $dateonly If true, uses date only
     * @param bool $until If true, and if using date only, shows previous date
     * @return string Date
     */
    protected function show_time($time, $dateonly, $until = false) {
        // For 'until' dates that are at midnight, e.g. midnight 5 March, it
        // is better to word the text as 'until end 4 March'.
        $daybefore = false;
        if ($until && $dateonly) {
            $daybefore = true;
            $time = strtotime('-1 day', $time);
        }
        return userdate($time,
                get_string($dateonly ? 'strftimedate' : 'strftimedatetime', 'langconfig'));
    }

    /**
     * Checks whether a given time refers exactly to midnight (in current user
     * timezone).
     *
     * @param int $time Time
     * @return bool True if time refers to midnight, false otherwise
     */
    protected static function is_midnight($time) {
        return usergetmidnight($time) == $time;
    }


}
