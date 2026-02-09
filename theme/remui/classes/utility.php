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
 * Edwiser RemUI
 * @package   theme_remui
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_remui;
defined('MOODLE_INTERNAL') || die();

define('THEME_REMUI', 'theme_remui');

use curl;
use Exception;
use stdClass;
use moodle_url;
use user_picture;
use dml_exception;
use context_system;
use context_course;
use context_coursecat;
use coding_exception;
use core_completion\progress;
use theme_remui\toolbox as toolbox;
use theme_remui\customizer\customizer;
use core_course_list_element;

/**
 * Utility class
 * @copyright (c) 2020 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class utility {

    /**
     * Get user picture from user object
     * @param  object  $userobject User object
     * @param  integer $imgsize    Size of image in pixel
     * @return String              User picture link
     */
    public static function get_user_picture($userobject = null, $imgsize = 100) {
        global $USER, $PAGE;
        if (!$userobject) {
            $userobject = $USER;
        }

        $userimg = new user_picture($userobject);
        $userimg->size = $imgsize;
        return  $userimg->get_url($PAGE);
    }

    /**
     * Returns left navigation footer menus details.
     *
     * @return Array Menu details.
     */
    public static function get_left_nav_footer_menus() {
        global $CFG, $COURSE, $USER, $PAGE;
        $menudata = array (
            [
                'url' => $CFG->wwwroot.'/course/index.php',
                'iconclass' => 'fa-archive',
                'title' => get_string('createarchivepage', 'theme_remui')
            ]
        );
        // Return all menus for site administrator.
        if (is_siteadmin($USER)) {
            $menus = array (
                [
                    'url' => "{$CFG->wwwroot}/{$CFG->admin}/user.php",
                    'iconclass' => 'fa-users',
                    'title' => get_string('userlist')
                ],
                [
                    'url' => self::get_course_creation_link(),
                    'iconclass' => 'fa-file',
                    'title' => get_string('createanewcourse', 'theme_remui')
                ],
                [
                    'url' => $CFG->wwwroot . "/theme/remui/customizer.php?url=" . urlencode($PAGE->url->out()),
                    'iconclass' => 'fa-paint-brush customizer-editing-icon',
                    'title' => get_string('customizer', 'theme_remui')
                ]
            );
            if (get_config('theme_remui', 'showimportericon')) {
                $menus[] = [
                    'url' => "{$CFG->wwwroot}/{$CFG->admin}/settings.php?section=themesettingremui&activetab=edwisersiteimporter",
                    'iconclass' => 'fa-download',
                    'title' => get_string('importer', 'theme_remui')
                ];
            }
            $menus[] = [
                'url' => "{$CFG->wwwroot}/{$CFG->admin}/settings.php?section=themesettingremui",
                'iconclass' => 'fa-cogs',
                'title' => get_string('remuisettings', 'theme_remui')
            ];
            $menudata = array_merge($menudata, $menus);
            $temp = array_splice($menudata, 1, 1);
            array_splice($menudata, 0, 0, $temp);
            return $menudata;
        }

        // Return menus for course creator.
        $coursecontext = context_course::instance($COURSE->id);
        if (has_capability('moodle/course:create', $coursecontext)) {
            $menu = [
                'url' => self::get_course_creation_link(),
                'iconclass' => 'fa-file',
                'title' => get_string('createanewcourse', 'theme_remui')
            ];
            array_push($menudata, $menu);
            $temp = array_splice($menudata, 1, 1);
            array_splice($menudata, 0, 0, $temp);
            return $menudata;
        }
        return $menudata;
    }

    /**
     * Returns the link for creating new course.
     *
     * @return string Course creation link.
     */
    public static function get_course_creation_link() {
        global $DB, $CFG;
        $categories = $DB->get_records('course_categories', null, '', 'id');
        $createcourselink = "#";
        if (!empty($categories)) {
            $firstcategory = reset($categories);
            $createcourselink = $CFG->wwwroot. '/course/edit.php?category='.$firstcategory->id;
        }
        return $createcourselink;
    }

    /**
     * Check user is admin or manager
     * @param  object  $userobject User object
     * @return boolean             True if admin or manager
     */
    public static function check_user_admin_cap($userobject = null) {
        global $USER;
        if (!$userobject) {
            $userobject = $USER;
        }
        if (is_siteadmin()) {
            return true;
        }
        $context = context_system::instance();
        $roles = get_user_roles($context, $userobject->id, false);
        foreach ($roles as $role) {
            if ($role->roleid == 1 && $role->shortname == 'manager') {
                return true;
            }
        }
        return false;
    }

    /**
     * Return user's courses or all the courses
     *
     * Usually called to get usr's courese, or it could also be called to get all course.
     * This function will also be called whern search course is used.
     *
     * @param  bool   $totalcount     If true then returns total course count
     * @param  string $search         course name to be search
     * @param  int    $category       ids to be search of courses.
     * @param  int    $limitfrom      course to be returned from these number onwards, like from course 5 .
     * @param  int    $limitto        till this number course to be returned,
     *                                like from course 10, then 5 course will be returned from 5 to 10.
     * @param  array  $mycourses      Courses to return user's course which he/she enrolled into.
     * @param  bool   $categorysort   if true the categories are sorted
     * @param  array  $courses        pass courses if would like to load more data for those courses
     * @param  bool   $filtermodified If true then cache will be cleared and regenerated when filter is modified
     * @return array                  Course array
     */
    public static function get_courses(
        $totalcount = false,
        $search = null,
        $category = null,
        $limitfrom = 0,
        $limitto = 0,
        $mycourses = null,
        $categorysort = null,
        $courses = [],
        $filtermodified = true
    ) {
        $coursehandler = new \theme_remui_coursehandler();	
        return $coursehandler->get_courses(
            $totalcount,
            $search,
            $category,
            $limitfrom,
            $limitto,
            $mycourses,
            $categorysort,
            $courses,
            $filtermodified
        );

    }

    /**
     * Get card content for courses
     * @param  object $wdmdata Data to create cards
     * @param  string $date    Date filter
     * @return array           Courses cards
     */
    public static function get_course_cards_content($wdmdata, $date = 'all') {
        global $CFG, $OUTPUT;
        $courseperpage = \theme_remui\toolbox::get_setting('courseperpage');
        $categorysort = $wdmdata->sort;
        $search       = $wdmdata->search;
        $category     = $wdmdata->category;
        $courses      = isset($wdmdata->courses) ? $wdmdata->courses : [];
        $mycourses    = $wdmdata->tab;
        $page         = ($mycourses) ? $wdmdata->page->mycourses : $wdmdata->page->courses;
        $startfrom    = $courseperpage * $page;
        $limitto      = $courseperpage;
        $filtermodified = isset($wdmdata->isFilterModified) ? $wdmdata->isFilterModified : true;
        $allowfull = true;
        // Resultant Array.
        $result = array();

        if ($page == -1) {
            $startfrom = 0;
            $limitto = 0;
        }

        // This condition is for coursecategory page only, that is why on frontpage it is not
        // necessary so returning limiteddata.
        if (isset($wdmdata->limiteddata)) {
            $allowfull = false;
        }

        // Pagination Context creation.
        if ($wdmdata->pagination) {
            // First paremeter true means get_courses function will return count of the result and if false, returns actual data.
            list($totalcourses, $courses)  = self::get_courses(
                2,
                $search,
                $category,
                $startfrom,
                $limitto,
                $mycourses,
                $categorysort,
                $courses,
                $filtermodified
            );

            $pagingbar  = new \paging_bar($totalcourses, $page, $courseperpage, 'javascript:void(0);', 'page');
            $result['totalcoursescount'] = $totalcourses;
            $result['pagination'] = $OUTPUT->render($pagingbar);
        } else {
            // Fetch the courses.
            $courses = self::get_courses(
                false,
                $search,
                $category,
                $startfrom,
                $limitto,
                $mycourses,
                $categorysort,
                $courses,
                $filtermodified
            );
        }

        // Courses Data.
        $coursecontext = array();
        foreach ($courses as $key => $course) {
            if ($date != 'all') {
                // Get the current time value.
                $time = new \DateTime("now", \core_date::get_user_timezone_object());
                $time->add(new \DateInterval("P1D"));

                $timestamp = $time->getTimestamp();

                // Check if inprogress and not passed the course end date.
                if ($date == 'inprogress' && $timestamp < $course['epochenddate']) {
                    continue;
                }

                // Check if future and not passed course start date.
                if ($date == 'future' && $timestamp > $course['epochstartdate']) {
                    continue;
                }

                // Check if past and not passed end date.
                if ($date == 'past' && $timestamp < $course['epochenddate']) {
                    continue;
                }
            }
            $coursedata = array();
            $coursedata['id'] = $course['courseid'];
            $coursedata['grader']    = $course['grader'];
            $coursedata['shortname'] = strip_tags(format_text($course['shortname']));
            $coursedata['courseurl'] = $course['courseurl'];
            $coursedata['coursename'] = strip_tags(format_text($course['coursename']));
            $coursedata['enrollusers'] = $course['enrollusers'];
            $coursedata['editcourse']  = $course['editcourse'];
            $coursedata['activity']    = $course['activity'];
            $coursedata['myprogress']    = $course['myprogress'];
            $coursedata['isPrincipal']    = $course['isPrincipal'];
          //  if(isset($course['isConcluso']))
          //      $coursedata['isConcluso'] = $course['isConcluso'];
            $coursedata['categoryname'] = format_text($course['categoryname']);
            if ($course['visible']) {
                $coursedata['visible'] = $course['visible'];
            }

            // This condition to handle the string url or moodle_url object problem.
            if (is_object($course['courseimage'])) {
                $coursedata['courseimage'] = $course['courseimage']->__toString();
            } else {
                $coursedata['courseimage'] = $course['courseimage'];
            }
            $coursedata['coursesummary'] = $course['coursesummary'];
            if (isset($course['coursestartdate'])) {
                $coursedata['startdate']['day'] = substr($course['coursestartdate'], 0, 2);
                $coursedata['startdate']['month'] = substr($course['coursestartdate'], 3, 3);
                $coursedata['startdate']['year'] = substr($course['coursestartdate'], 8, 4);
            }
            // Course card - Footer context is different for mycourses and all courses tab.
            if ($mycourses) {
                // Context creation for mycourses.
                $coursedata['mycourses'] = true;
                if (isset($course['coursecompleted'])) {
                    $coursedata["coursecompleted"] = $course['coursecompleted'];
                }
                if (isset($course['courseinprogress'])) {
                    $coursedata["courseinprogress"] = $course['courseinprogress'];
                    $coursedata["percentage"] = $course['percentage'];
                }
                if (isset($course['coursetostart'])) {
                    $coursedata["coursetostart"] = $course['coursetostart'];
                }
            } else {
                // Context creation for all courses.
                if (isset($course['usercanmanage']) && $allowfull) {
                    $coursedata["usercanmanage"] = $course['usercanmanage'];
                }

                if (isset($course['enrollmenticons']) && $allowfull) {
                    $coursedata["enrollmenticons"] = $course['enrollmenticons'];
                }
            }

/*            if (isset($course['instructors']) && $allowfull) {
                $instructors = array();
                foreach ($course['instructors'] as $key2 => $instructor) {
                    $instructordetail['name'] = $instructor['name'];
                    $instructordetail['url'] = $instructor['url'];
                    $instructordetail['picture'] = $instructor['picture']->__toString();
                    $instructors[] = $instructordetail;
                }
                $coursedata['instructors'] = $instructors;
            }
*/
            $pagelayout = get_config('theme_remui', 'categorypagelayout');

            if ($pagelayout !== "0") {

                switch ($pagelayout) {
                    case '1':
                        if ($allowfull) {
                            $coursedata['widthclasses'] = 'card-main col-lg-4 col-sm-12 col-md-6';
                        } else {
                            $coursedata['widthclasses'] = 'col-12 h-p100 ';
                        }
                        break;

                    // Commented this code, add here new layout design condition.
                    // case '2':
                    //     $templatecontext['layout2'] = true;
                    //     break;
                    default:
                        if ($allowfull) {
                            $coursedata['widthclasses'] = 'col-lg-3 col-sm-12 col-md-6';
                        } else {
                            $coursedata['widthclasses'] = 'col-12 h-p100 ';
                        }
                        break;
                }

            } else {
                if ($allowfull) {
                    $coursedata['widthclasses'] = 'col-lg-3 col-sm-12 col-md-6';
                } else {
                    $coursedata['widthclasses'] = 'col-12 h-p100 ';
                }
            }

            // Animation Setting courseanimation.
 //           $coursedata['animation'] = \theme_remui\toolbox::get_setting('courseanimation');
 //           if (!\theme_remui\toolbox::get_setting('enablenewcoursecards')) {
 //               $coursedata['old_card'] = true;
 //           }
            $coursecontext[] = $coursedata;
        }
        $result['courses'] = $coursecontext;
        $result['view'] = get_user_preferences('course_view_state');

        if (\theme_remui\toolbox::get_setting('enablenewcoursecards')) {
            $result['latest_card'] = true;
        }

        return $result;
    }

    /**
     * Set all the child categories of parent category
     *
     * @param  integer $category Category id
     *
     * @return array             Child categories
     */
    public static function get_children_category($category) {
        global $DB;
        $childs = [$DB->get_record('course_categories', array('id' => $category))];
        $childcategories = $DB->get_records_sql('SELECT * FROM {course_categories} WHERE parent = ?', array($category));
        if (!empty($childcategories)) {
            foreach ($childcategories as $child) {
                $childs = array_merge($childs, self::get_children_category($child->id));
            }
        }
        return $childs;
    }


    /**
     * Sort function for ungraded items in the teachers personal menu.
     *
     * Compare on closetime, but fall back to openening time if not present.
     * Finally, sort by unique coursemodule id when the dates match.
     *
     * @param object $left  Left grade
     * @param object $right Right grade
     * @return int
     */
    public static function sort_graded($left, $right) {
        if (empty($left->closetime)) {
            $lefttime = $left->opentime;
        } else {
            $lefttime = $left->closetime;
        }

        if (empty($right->closetime)) {
            $righttime = $right->opentime;
        } else {
            $righttime = $right->closetime;
        }

        if ($lefttime === $righttime) {
            if ($left->coursemoduleid === $right->coursemoduleid) {
                return 0;
            } else if ($left->coursemoduleid < $right->coursemoduleid) {
                return -1;
            } else {
                return 1;
            }
        } else if ($lefttime < $righttime) {
            return  -1;
        } else {
            return 1;
        }
    }

    /**
     * Get items which have been graded.
     *
     * @return string grades
     * @throws \coding_exception
     */
    public static function graded() {
        $grades = self::events_graded();
        return $grades;
    }

    /**
     * Grading data
     * @return array Grading data
     */
    public static function grading() {
        global $USER, $PAGE;

        $grading = self::all_ungraded($USER->id);
        return $grading;
    }

    /**
     * Get everything graded from a specific date to the current date.
     *
     * @return mixed Event data
     */
    public static function events_graded() {
        global $DB, $USER;

        $params = [];
        $coursesql = '';
        $courses = enrol_get_my_courses();
        $courseids = array_keys($courses);
        $courseids[] = SITEID;
        list($coursesql, $params) = $DB->get_in_or_equal($courseids);
        $coursesql = 'AND gi.courseid '.$coursesql;

        $onemonthago = time() - (DAYSECS * 31);
        $showfrom = $onemonthago;

        $sql = "SELECT gg.*, gi.itemmodule, gi.iteminstance, gi.courseid, gi.itemtype
                  FROM {grade_grades} gg
                  JOIN {grade_items} gi
                    ON gg.itemid = gi.id $coursesql
                 WHERE gg.userid = ?
                   AND (gg.timemodified > ?
                    OR gg.timecreated > ?)
                   AND (gg.finalgrade IS NOT NULL
                    OR gg.rawgrade IS NOT NULL
                    OR gg.feedback IS NOT NULL)
                   AND gi.itemtype = 'mod'
                 ORDER BY gg.timemodified DESC";

        $params = array_merge($params, [$USER->id, $showfrom, $showfrom]);
        $grades = $DB->get_records_sql($sql, $params, 0, 5);

        $eventdata = array();
        foreach ($grades as $grade) {
            $eventdata[] = $grade;
        }

        return $eventdata;
    }

    /**
     * Get all ungraded items.
     * @param int $userid
     * @return array
     */
    public static function all_ungraded($userid) {
        $courseids = self::gradeable_courseids($userid);

        if (empty($courseids)) {
            return array();
        }

        $mods = \core_plugin_manager::instance()->get_installed_plugins('mod');
        $mods = array_keys($mods);

        $grading = [];
        foreach ($mods as $mod) {
            $class = '\theme_remui\activity';
            $method = $mod.'_ungraded';
            if (method_exists($class, $method)) {
                $grading = array_merge($grading, call_user_func([$class, $method], $courseids));
            }
        }

        usort($grading, array('self', 'sort_graded'));

        return $grading;
    }

    /**
     * Get courses where user has the ability to view the gradebook.
     *
     * @param int $userid
     * @return array
     * @throws \coding_exception
     */
    public static function gradeable_courseids($userid) {
        $courses = enrol_get_all_users_courses($userid);
        $courseids = [];
        $capability = 'gradereport/grader:view';
        foreach ($courses as $course) {
            if (has_capability($capability, \context_course::instance($course->id), $userid)) {
                $courseids[] = $course->id;
            }
        }
        return $courseids;
    }

    /**
     * This function is used to get the data for footer section.
     *
     * @param  bool $social True to skip social content
     * @return array        Footer sections data
     */
    public static function get_footer_data($social = false) {
        global $OUTPUT, $SITE;
        $footer = array();
        $customizer = customizer::instance();
        $colcount = $customizer->get_config('footercolumn');

        $colsizes = explode(',', $customizer->get_config('footercolumnsize'));
        // To handle number of columns in footer row.
        $classes = 'col-12 col-sm-6 col-lg-12';

        $socials = array(
            'facebook' => \theme_remui\toolbox::get_setting('facebooksetting'),
            'twitter'  => \theme_remui\toolbox::get_setting('twittersetting'),
            'linkedin' => \theme_remui\toolbox::get_setting('linkedinsetting'),
            'gplus'    => \theme_remui\toolbox::get_setting('gplussetting'),
            'youtube'  => \theme_remui\toolbox::get_setting('youtubesetting'),
            'instagram' => \theme_remui\toolbox::get_setting('instagramsetting'),
            'pinterest' => \theme_remui\toolbox::get_setting('pinterestsetting'),
            'quora' => \theme_remui\toolbox::get_setting('quorasetting')
        );
        $footer['social'] = $socials;
        $emptyprimary = true;
        for ($i = 1; $i <= 4; $i++) {
            $title = format_text(\theme_remui\toolbox::get_setting('footercolumn' . $i . 'title'), FORMAT_HTML);
            $content = format_text(\theme_remui\toolbox::get_setting('footercolumn' . $i . 'customhtml'), FORMAT_HTML);
            $type = $customizer->get_config('footercolumn' . $i . 'type');
            $menu = $customizer->get_config('footercolumn' . $i . 'menu');
            $orientation = $customizer->get_config('footercolumn' . $i . 'menuorientation');
            $enabledsocials = $customizer->get_config('footercolumn' . $i . 'social');
            $socialforcolumn = [];
            $hassocial = $hascontent = $hasmenu = false;
            if (!$enabledsocials = json_decode($enabledsocials, true)) {
                $enabledsocials = [];
            }
            foreach ($socials as $key => $value) {
                if (array_search($key, $enabledsocials) !== false) {
                    $socialforcolumn[$key] = $value;
                    if ($value != '') {
                        $hassocial = true;
                    }
                    continue;
                }
                $socialforcolumn[$key] = '';
            }
            switch ($type) {
                case 'social':
                    if (!empty($socialforcolumn) && $hassocial == true) {
                        $emptyprimary = false;
                    }
                    break;
                case 'customhtml':
                    if (trim(strip_tags($content)) != '' || stripos($content, 'img') !== false) {
                        $emptyprimary = false;
                        $hascontent = true;
                    }
                    break;
                case 'menu':
                    if (!empty($menu)) {
                        $emptyprimary = false;
                        $hasmenu = true;
                    }
                    break;
            }
            $footer['sections'][] = array(
                'index' => $i,
                'classes' => $classes,
                'width' => isset($colsizes[$i - 1]) ? $colsizes[$i - 1] : 0,
                'title' => $title,
                'content' => $content,
                'type' => $type,
                'visible' => $i <= $colcount,
                'menu' => $menu,
                'orientation' => $orientation,
                'social' => $socialforcolumn,
                'hassocial' => $hassocial,
                'hascontent' => $hascontent,
                'hasmenu' => $hasmenu
            );
        }
        $footer['emptyprimary'] = $emptyprimary;

        $logo = \theme_remui\toolbox::setting_file_url('logo', 'logo');
        if (empty($logo)) {
            $logo = \theme_remui\toolbox::image_url('logo', 'theme');
        }
        $footer['logo'] = $logo;

        $footer['bottomtext'] = format_text(\theme_remui\toolbox::get_setting('footerbottomtext'));
        $footer['bottomlink'] = strip_tags(format_text(\theme_remui\toolbox::get_setting('footerbottomlink')));
        $footer['poweredby']  = \theme_remui\toolbox::get_setting('poweredbyedwiser');

        // Secondary footer data.
        // Show footer logo.
        $footer['footershowlogo'] = $customizer->get_config('footershowlogo') == 'show';

        // Show social icons in secondary footer.
        $footer['footersecondarysocial'] = $customizer->get_config('footersecondarysocial') == 'show';

        // Show terms and conditions.
        $footer['footertermsandconditionsshow'] = $customizer->get_config('footertermsandconditionsshow') == 'show';
        $footer['footertermsandconditions'] = $customizer->get_config('footertermsandconditions');

        // Show privacy policy.
        $footer['footerprivacypolicyshow'] = $customizer->get_config('footerprivacypolicyshow') == 'show';
        $footer['footerprivacypolicy'] = $customizer->get_config('footerprivacypolicy');

        // Show copyrights condition.
        $copyrights = $customizer->get_config('footercopyrights');
        $copyrights = str_replace('[site]', $SITE->fullname, $copyrights);
        $copyrights = str_replace('[year]', date("Y"), $copyrights);
        $footer['footercopyrights'] = [
            'footercopyrightsshow' => $customizer->get_config('footercopyrightsshow') == 'show',
            'content' => strip_tags(format_text($copyrights)),
            'attributes' => [
                'data-site="' . $SITE->fullname . '"'
            ]
        ];
        return $footer;
    }

    /**
     * Function to get the remote data from url
     *
     * @param string $url
     * @return array
     */
    public static function url_get_contents ($url) {
        $urlgetcontentsdata = array();

        if (class_exists('curl')) {
            $curl = new curl();
            $curl->setopt(array(
                'CURLOPT_SSL_VERIFYPEER' => false,
                'CURLOPT_FRESH_CONNECT' => true,
                'CURLOPT_RETURNTRANSFER' => 1,
                'CURLOPT_TIMEOUT' => 3
            ));
            $urlgetcontentsdata = $curl->get($url);

            if ($curl->get_errno() !== 0) {
                $urlgetcontentsdata = [];
            }
        } else if (function_exists('curl_exec')) {
            $conn = curl_init($url);
            curl_setopt($conn, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($conn, CURLOPT_FRESH_CONNECT, true);
            curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($conn, CURLOPT_TIMEOUT, 3);
            if (defined('CURLOPT_IPRESOLVE') && defined('CURL_IPRESOLVE_V4')) {
                curl_setopt($conn, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4);
            }
            $urlgetcontentsdata = (curl_exec($conn));
            if (curl_errno($conn)) {
                $errormsg = curl_error($conn);
                $urlgetcontentsdata = array();
            }
            curl_close($conn);
        } else if (function_exists('file_get_contents')) {
            $urlgetcontentsdata = file_get_contents($url);
        } else if (function_exists('fopen') && function_exists('stream_get_contents')) {
            $handle = fopen($url, "r");
            $urlgetcontentsdata = stream_get_contents($handle);
        } else {
            $urlgetcontentsdata = array();
        }
        return $urlgetcontentsdata;
    }

    /**
     * Throw error with string and error code
     * @param string $error     Eigther error string id or direct string
     * @param int    $code      Numeric error code
     * @param bool   $getstring If true then $error will be treated as string id
     */
    public static function throw_error($error, $code = '', $getstring = true) {
        if ($getstring) {
            $error = get_string($error, 'theme_remui');
        }
        throw new Exception(json_encode(['error' => true, 'msg' => $error . " : " . $code]), $code);
    }

     /**
     * Generates an array of sections and an array of activities for the given course.
     *
     * This method uses the cache to improve performance and avoid the get_fast_modinfo call
     *
     * @param stdClass $course
     * @return array Array($sections, $activities)
     */
    public static function get_focus_mode_sections(stdClass $course, $coursemoduleid = false) {
        global $CFG, $USER;
        require_once($CFG->dirroot.'/course/lib.php');

        $modinfo = get_fast_modinfo($course);
        $sections = $modinfo->get_section_info_all();

        // For course formats using 'numsections' trim the sections list
        $courseformatoptions = course_get_format($course)->get_format_options();
        if (isset($courseformatoptions['numsections'])) {
            $sections = array_slice($sections, 0, $courseformatoptions['numsections']+1, true);
        }

        $allsections = array();
        $active = '';
        $previous = '';
        $current = '';
        $next = '';

        foreach ($sections as $sectiondata) {
            $section = new stdClass;
            $section->sectionid = 'Section-'.$sectiondata->id;
            $section->section = $sectiondata->section;
            $section->name = get_section_name($course, $sectiondata->section);
            $section->hasactivites = false;
            $section->activities = [];
            $section->active = '';
            if (!array_key_exists($sectiondata->section, $modinfo->sections)) {
                continue;
            }

            foreach ($modinfo->sections[$sectiondata->section] as $cmid) {
                $cm = $modinfo->cms[$cmid];

                // Only add activities the user can access, aren't in stealth mode and have a url (eg. mod_label does not).
                if (!$cm->uservisible || $cm->is_stealth() || empty($cm->url)) {
                    continue;
                }
                $activity = new stdClass;
                $activity->id = $cm->id;
                $activity->course = $course->id;
                $activity->section = $sectiondata->section;
                $activity->name = strip_tags(format_text($cm->name));
                $activity->icon = $cm->get_icon_url();
                $activity->hidden = (!$cm->visible);
                $activity->modname = $cm->modname;
                $activity->onclick = $cm->onclick;
                $activity->active = '';
                $url = $cm->url;
                if (!$url) {
                    $activity->url = null;
                    $activity->display = false;
                } else {
                    $activity->url = $url->out();
                    $activity->display = $cm->is_visible_on_course_page() ? true : false;
                }
                if ($activity->display) {
                    if ($coursemoduleid != false) {
                        if ($active == '') {
                            $previous = $current;
                            $current = $activity->url;
                        }
                        if ($active != '' && $next == '') {
                            $next = $activity->url;
                        }
                        if ($cm->id == $coursemoduleid) {
                            $activity->active = 'active';
                            $active = $activity->name;
                            $section->active = 'show';
                        }
                    }
                    $section->hasactivites = true;
                    $section->activities[] = $activity;
                }
            }
            $allsections[] = $section;
        }
        if (count($allsections) != 0 && $active == '') {
            $allsections[0]->active = 'show';
            $allsections[count($allsections) - 1]->last = true;
        }

        // Make forceview true for previous and next link.
        if ($previous != '') {
            $previous .= '&forceview=1';
        }
        if ($next != '') {
            $next .= '&forceview=1';
        }

        return [$allsections, $active, $previous, $next];
    }

    public static function remove_announcement_preferences() {
        global $DB;
        // Delete from DB.
        $DB->delete_records('user_preferences', array('name' => 'remui_dismised_announcement'));
    }


    /*
     * This function return data context for steps on Setup wizard page.
     */
    public static function setup_wizard_step_wise_data(){
        global $CFG, $OUTPUT;
        require_once($CFG->libdir.'/adminlib.php');
        $adminroot = admin_get_root();

        $remuisettings = get_all_settings_from($adminroot, THEME_REMUI);

        list($stepsNames, $steps, $finalSteps) = self::setup_wizard_steps();

        $swstepsStatus = $finalSteps;

        $hiddenSettings = array();

        foreach ($remuisettings as $setting) {
            $stepfound = false;
            $data = $setting->get_setting();
            if (is_null($data)) {
                $data = $setting->get_defaultsetting();
            }

            $setting->html = $setting->output_html($data);

            foreach ($steps as $stepkey => $step) {

                if (in_array($setting->name, $step)) {

                    $finalSteps[$stepkey]['index'] = $stepkey + 1; // Step Number
                    $finalSteps[$stepkey]['name'] = $stepsNames[$stepkey]; // Step Name

                    // Adding current setting in new Steps array at respective Step.
                    $finalSteps[$stepkey]['settings'][] = $setting;
                    $stepfound = true; // Setting the flag for current step.
                }

            }

            if (!$stepfound) {
                $hiddenSteps[] = $setting;
            }
        }

        // Following part is to add extra licensing step in above settings steps.

        // Increment the stepkey for licensing tab -
        $stepkey = $stepkey + 1;

        // Adding index value - by increment 1 - because array index is not started with 0;
        $finalSteps[$stepkey]['index'] = $stepkey + 1; // Step Number

        $finalSteps[$stepkey]['name'] = 'Licensing'; // Step Name

        $licensecontroller = new controller\LicenseController();
        $templatecontext['license'] = $licensecontroller->get_remui_license_template_context();

        $customizerurl = $CFG->wwwroot . '/theme/remui/customizer.php?url=' . urlencode($CFG->wwwroot ."/my");

        $licensingHTML = '<div class="container license-html">
            <div class="row">
            <div class="col-sm">'.$OUTPUT->render_from_template('theme_remui/license_form', $templatecontext['license']).'</div>
            <div class="col-sm">
                <a class="customizer-link" href="'.$customizerurl.'">
                <div class="w-100 d-flex align-items-center flex-column">
                    <img src="'.$OUTPUT->image_url('livecustomizer', 'theme').'" class="img-fluid" alt="Responsive image">
                    <h4 class="text-center p-3">'.get_string("customizemore", "theme_remui").' <br><span role="button" class="btn btn-info my-2 customizer-title">Edwiser RemUI Customizer</span></h4>
                </div>
                </a>
            </div>
            </div>
            </div>';

        // Adding current setting in new Steps array at respective Step.
        $finalSteps[$stepkey]['settings'][]['html'] = $licensingHTML;

        return array('steps'=> $finalSteps, 'hidden' => $hiddenSteps, 'totalSteps' => count($finalSteps),  'stepsStatus' => $swstepsStatus);
    }


    public static function setup_wizard_steps(){

        // Note -
        // $stepsNames and $steps always must be same in length.

        // stepsNames = will be title of particular step.
        $stepsNames = [
            0 => get_string("general", "theme_remui"),
            1 => get_string("coursepage", "theme_remui"),
            2 => get_string("pagelayout", "theme_remui"),
            3 => get_string("loginpage", "theme_remui")
        ];

        // Settings sorted as per Steps.
        $steps = [
            0 => [
                "logoorsitename",
                "siteicon",
                "logo",
                "logomini",
                "faviconurl",
                "enablerecentcourses",
                "enableheaderbuttons",
                "mergemessagingsidebar",
                "poweredbyedwiser"
            ],
            1 => [
                "enablefocusmode",
                "enablecoursestats",
                "activitynextpreviousbutton",
            ],
            2 => [
                "enrolment_page_layout",
                "showcoursepricing",
                "enrolment_payment",
                "categorypagelayout",
                "enablenewcoursecards",
                "courseanimation",
                "courseperpage"
            ],
            3 => [
                "navlogin_popup",
                "brandlogopos",
                "loginsettingpic",
                "brandlogotext",
                "signuptextcolor"
            ]
        ];

        $finalSteps = get_config(THEME_REMUI, 'swstepstatus');
        if (!$finalSteps) {
            // Final Steps has one extra array key, its for licensing step.
            $finalSteps = [
                0 => ["status" => "current", "isactive" => true],
                1 => ["status" => "notvisited", "isactive" => false],
                2 => ["status" => "notvisited", "isactive" => false],
                3 => ["status" => "notvisited", "isactive" => false],
                4 => ["status" => "notvisited", "isactive" => false]
            ];

            set_config('swstepstatus', json_encode($finalSteps), THEME_REMUI);
        } else {
            $finalSteps = json_decode($finalSteps, true); // true to decode it as an array.
        }

        return array($stepsNames, $steps, $finalSteps);
    }




    public static function get_site_loader(){
        global $CFG;
        $loaderimage = \theme_remui\toolbox::setting_file_url('loaderimage', 'loaderimage');
        if (empty($loaderimage)) {
            $loaderimage   = $CFG->wwwroot.'/theme/remui/pix/siteloader.svg';
        }
        return $loaderimage;
    }
	
	
    /**
     * Add extra body classes.
     * Do some data manipulation then add your classes.
     */
    public static function get_main_bg_class() {
        global $PAGE;
        $haystack = explode(" ", $PAGE->bodyclasses);

        if (in_array('ignore-main-bg', $haystack)) {
            return;
        }

        $bodyclasses = array(
            'pagelayout-mydashboard',
            'pagelayout-mycourses',
            'pagelayout-frontpage',
            'path-calendar',
            'pagelayout-course'
        );

        foreach ($bodyclasses as $key => $needle) {
            if (in_array($needle, $haystack)) {
                return;
            }
        }

        return "main-area-bg";
    }


   /*
     * To add menu in header bar for recently accessed cources.
     * @param $contextmenu -> $primarymenu['moremenu']
     */
    public static function get_recent_courses_menu($contextmenu) {

        $courses = \theme_remui_coursehandler::get_recent_accessed_courses(5);

        $mainarr = [];
        $mainarr['text'] = get_string('recent', 'theme_remui');
        $mainarr['srtext'] = get_string('recentcoursesmenu', 'theme_remui');
        $mainarr['key'] = 'recentcourses';
        $mainarr['url'] = "#";
        $mainarr['children'] = [];
        foreach ($courses as $key => $course) {

            $mainarr['haschildren'] = true;

            $obj = [];

            $obj['text'] = format_text($course->fullname);
            $obj['url'] = new moodle_url('/course/view.php?id=', array(
                'id' => $course->courseid
            ));
            $obj['title'] = format_text($course->fullname);
            $mainarr['children'][] = $obj;
        }

        if (isset($mainarr['haschildren']) && $mainarr['haschildren']) {
            // To add recent menu at start.
            // Code - $contextmenu['nodearray'] = array_merge(array($mainarr), $contextmenu['nodearray']).
            // To add recent menu at end.
            // Code - $contextmenu['moremenu']['nodearray'][] = $mainarr.

            // This code is to separate menu from primary menu.
            $contextmenu['edwisermenu']['nodearray'][] = $mainarr;

            // Mobile Menu addition.
            $contextmenu['mobileprimarynav'][] = $mainarr;
        }

        return $contextmenu;
    }
	

    /*
     * Returns course categories menu array context.
     * @param $contextmenu -> $primarymenu['moremenu']
     */
    public static function get_coursecategory_menu($contextmenu) {
        $categories = utility::get_categories_list();

        $mainarr = [];
        $coursecategorytext = get_config('theme_remui', 'coursecategoriestext');
        $mainarr['text'] = $coursecategorytext == "" ? get_string('coursecategories', 'theme_remui') : $coursecategorytext;
        $mainarr['key'] = 'coursecat';
        $mainarr['url'] = "#";
        $mainarr['children'] = [];
        $mainarr['classes'] = "catselector-menu";
        $mainarr['sort'] = "catselector-menu";

        $html = utility::generateCategoryStructure($categories);

        $mainarr['haschildren'] = true;
        $mainarr['children'] = false;
        $mainarr['categorytreedesign'] = $html;
        if (isset($mainarr['haschildren']) && $mainarr['haschildren']) {
            // To add recent menu at end $contextmenu['moremenu']['nodearray'][] = $mainarr.
            // To add recent menu at end $contextmenu['mobileprimarynav'][] = $mainarr.

            // To add recent menu at start.
            array_unshift($contextmenu['moremenu']['nodearray'], $mainarr);
            array_unshift($contextmenu['mobileprimarynav'], $mainarr);
        }
        return $contextmenu;
    }


   public static function get_categories_list() {
        global $DB, $USER;

        $systemcontext = context_system::instance();
        $categories = $DB->get_records('course_categories',array('visible'=>1));

        if(utility::check_user_admin_cap() || has_capability('moodle/category:viewhiddencategories', $systemcontext, $USER->id)){
            $categories = $DB->get_records('course_categories');
        }

        $filteredcategories = array();
        foreach ($categories as $category) {
            $categorycontext = context_coursecat::instance($category->id);
            if (has_capability('moodle/category:viewcourselist', $categorycontext)) {
                $filteredcategories[$category->id] = $category;
            }
        }

        return $filteredcategories;
    }

    public static function generateCategoryStructure($categories)
    {
        global $CFG;
        // sort by sortorder
        usort($categories, function ($a, $b) {
            return $a->sortorder - $b->sortorder;
        });

        $categoryTree = utility::buildCategoryTree($categories, 0);
        $html = '<div class="category-wrapper container d-flex flex-column">';
        $html .= '<div class="menu-wrapper">
                        <ul class="m-0 p-pl-5">
                            <li><a href="'.$CFG->wwwroot.'/course/index.php?categoryid=all" data-cat-id="0" class="category-link ellipsis">'.get_string('allcourescattext', 'theme_remui').'</a></li>
                        </ul>
                  </div>';
        $html .= utility::generateHTML($categoryTree);
        $html .= '</div>';

        return $html;
    }
	
	
    public static function buildCategoryTree($categories, $parentId)
    {
        $tree = array();

        foreach ($categories as $category) {
            if ($category->parent == $parentId) {
                $subcategory = utility::buildCategoryTree($categories, $category->id);
                if (!empty($subcategory)) {
                    $category->children = $subcategory;
                }
                $tree[] = $category;
            }
        }

        return $tree;
    }


    public static function generateHTML($categoryTree, $html = '')
    {
        global $CFG;
        foreach ($categoryTree as $category) {
            $uniquenumber = hexdec(uniqid());
            $hasChildren = !empty($category->children);
            $html .= '<div class="menu-wrapper">';

            if ($hasChildren) {
                $html .= '<div class="menu-heading d-flex flex-gap-2 w-100">
                            <div class="toggle-btn d-flex justify-content-center align-items-center collapsed" data-toggle="collapse" data-target="#collapse' . $uniquenumber . '" aria-controls="collapseTwo">
                                <span class="expande-icon edw-icon edw-icon-Down-Arrow" title=""></span>
                                <span class="collaps-icon edw-icon edw-icon-UpArrow" title=""></span>
                            </div>
                            <a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$category->id.'" data-cat-id="'.$category->id.'" class="category-link ellipsis catvisibility-'.$category->visible.' ">'.format_text($category->name, FORMAT_HTML).'</a>
                            <i class="hidden-label edw-icon edw-icon-Hide p-mt-0d5 catvisibility-'.$category->visible.'" aria-hidden="true"></i>
                        </div>';
                $html .= '<div id="collapse' .$uniquenumber. '" class="collapse">
                            <div class="menu-body">';
            } else {
                $html .= '<ul class="m-0 p-pl-5">
                                <li><div class="d-flex flex-gap-2 w-100">
                                <a href="'.$CFG->wwwroot.'/course/index.php?categoryid='.$category->id.'" data-cat-id="'.$category->id.'" class="category-link ellipsis catvisibility-'.$category->visible.' ">'.format_text($category->name, FORMAT_HTML).'</a>
                                <i class="hidden-label edw-icon edw-icon-Hide p-mt-0d5 catvisibility-'.$category->visible.'" aria-hidden="true"></i>
                                </div></li>
                            </ul>';
            }

            if ($hasChildren) {
                $html .= utility::generateHTML($category->children);
                $html .= '</div>';
                $html .= '</div>';
            }

            $html .= '</div>';
        }

        // $html .= '</div>';

        return $html;
    }

    /*
     * To add icons to profile menu dropdown in header.
     * @param $primarymenu
     * @return $primarymenu
     */
    public static function add_profile_dropdown_icons($primarymenu) {
        $customicons = array(
            "profile,moodle" => "edw-icon-User",
            "grades,grades" => "edw-icon-Grade",
            "calendar,core_calendar" => "edw-icon-Calendar",
            "privatefiles,moodle" => "edw-icon-Private-Files",
            "reports,core_reportbuilder" => "edw-icon-Report",
            "preferences,moodle" => "edw-icon-Preferences",
            "switchroleto,moodle" => "edw-icon-Grade",
            "language" => "edw-icon-Language",
            "logout,moodle" => "edw-icon-Logout",
        );
        foreach ($primarymenu['user']['items'] as $key => $user) {
            $item = $primarymenu['user']['items'][$key];
            if ($item->itemtype == "submenu-link" && !isset($item->titleidentifier) ) {
                $item->titleidentifier = 'language';
            }
            if (($item->itemtype == "link" || $item->itemtype == "submenu-link") && isset($item->titleidentifier) && isset($customicons[$item->titleidentifier])) {
                $item->profileicon = $customicons[$item->titleidentifier];
            }

            $primarymenu['user']['items'][$key] = $item;
        }
        return $primarymenu;
    }
	
    public static function get_inproduct_notification() {
        global $OUTPUT;
        // Init product notification configuration
        $notification = get_user_preferences('edwiser_inproduct_notification');

        if ($notification == null || $notification == "false" || $notification == false) {
            return false;
        }

        $notification = json_decode($notification);

        return [
            "msg" => $notification->msg,
            "imgclass" => $notification->img,
            "edwiserlogo" => $OUTPUT->image_url('edwiser-logo', 'theme_remui')->__toString(),
            "mainimg" => $OUTPUT->image_url($notification->img, 'theme_remui')->__toString()
        ];
    }
	
	
    public static function show_license_notice() {
        // Get license data from license controller.
		return '';
        $lcontroller = new \theme_remui\controller\LicenseController();
        $getlidatafromdb = $lcontroller->get_data_from_db();
        if (isloggedin() && !isguestuser()) {
            $content = '';

            $classes = ['alert', 'text-center', 'license-notice', ' alert-dismissible', 'site-announcement' , 'mb-0'];
            if ('available' != $getlidatafromdb) {
                $classes[] = 'alert-danger';
                if (is_siteadmin()) {
                    $content .= '<strong>'.get_string('licensenotactiveadmin', 'theme_remui').'</strong>';
                } else {
                    $content .= get_string('licensenotactive', 'theme_remui');
                }
            } else if ('available' == $getlidatafromdb) {
                $licensekeyactivate = \theme_remui\toolbox::get_setting(EDD_LICENSE_ACTION);

                if (isset($licensekeyactivate) && !empty($licensekeyactivate)) {
                    $classes[] = 'alert-success';
                    $content .= get_string('licensekeyactivated', 'theme_remui');
                } else {
                    // Show update notice if license is active and update is available.
                    $available  = \theme_remui\controller\RemUIController::check_remui_update();
                    if (is_siteadmin() && $available == 'available') {
                        $classes[] = 'update-nag bg-info moodle-has-zindex';
                        $url = new moodle_url(
                            '/admin/settings.php',
                            array(
                                'section' => 'themesettingremui',
                                'activetab' => 'informationcenter'
                            )
                        );
                        $content .= get_string('newupdatemessage', 'theme_remui', $url->out());
                    }
                }
            }
            if ($content != '') {
                $content .= '<button type="button" id="dismiss_announcement" class="close" data-dismiss="alert" aria-hidden="true"><span class="edw-icon edw-icon-Cancel  large"></span></button>';
                return html_writer::tag('div', $content, array('class' => implode(' ', $classes)));
            }
        }
        return '';
    }	
	
	
    public static function addblockfloatmenu() {
        global $OUTPUT, $CFG, $PAGE;
        $regionsid = [
            "content" => '#block-region-content',
            "side-pre" => '#block-region-side-pre',
            "side-top" => '#region-top-blocks',
            "side-bottom" => '#region-bottom-blocks',
            "full-width-top" => '#region-fullwidthtop-blocks',
            "full-bottom" => '#region-fullwidthbottom-blocks',
        ];
        $sortingarray = ['full-width-top', 'side-top', 'content', 'side-bottom',  'full-bottom', 'side-pre'];

        $addblockmodalcontext = [
            'editing' => $PAGE->user_is_editing(),
            'regiondata' => array()
        ];
        $regionsarray = $PAGE->blocks->get_regions();
        usort($regionsarray, function ($a, $b) use ($sortingarray) {
            $indexa = array_search($a, $sortingarray);
            $indexb = array_search($b, $sortingarray);
            return $indexa - $indexb;
        });

        foreach ($regionsarray as $region) {
            if (empty($OUTPUT->addblockbutton($region))) {
                continue;
            }
            $singleregiondata = array(
                'region' => $region,
                'regionname' => get_string($region, 'theme_remui'),
                'regionid' => $regionsid[$region],
                'regionaddblockbutton' => $OUTPUT->addblockbutton($region),
                'pageurl' => $PAGE->url
            );
            $addblockmodalcontext['regiondata'][] = $singleregiondata;
        }
        $PAGE->requires->data_for_js('blocksectiondata', $addblockmodalcontext['regiondata']);
        return $OUTPUT->render_from_template("theme_remui/add_block_float_menu", $addblockmodalcontext);
    }	
	
   /**
     * Adding Edwiser Page Builder functionality check.
     *
     * @return boolean
     */
    public static function can_create_page() {
        global $PAGE, $USER;

        // Edwiser Page builder component.
        $epbcomponent = 'local_edwiserpagebuilder';

        // Check if Edwiser page builder plugin is installed.
        // Also check whether Edwiser page builder is latest one and has page creation functionality.

        if (!is_plugin_available($epbcomponent)) {
            return false;
        }

        if (is_plugin_available($epbcomponent)) {
            $pluginman = \core_plugin_manager::instance();
            $epbinfo = $pluginman->get_plugin_info($epbcomponent);

            $epbversion = 2023101100;

            if ($epbinfo->versiondb < $epbversion) {
                return false;
            }
        }

        // System Context.
        $context = \context_system::instance();

        // Check if current user is admin OR has capability to create pages.
        if (is_siteadmin() || has_capability('local/edwiserpagebuilder:epb_can_manage_page', $context, $USER)) {
            return true;
        }

        return false;
    }	
	

}
