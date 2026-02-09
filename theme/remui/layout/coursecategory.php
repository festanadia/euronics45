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

require_once($CFG->libdir."/customlib/mycustomfield_custom_info.php");
require_once($CFG->libdir."/customlib/mycourse_custom_info.php");
defined('MOODLE_INTERNAL') || die();
use mycustomfield_info\mycustomfield_custom_info;
use mycourse_info\mycourse_custom_info;
global $CFG, $PAGE, $USER, $SITE, $COURSE;


if (stripos($PAGE->url->get_path(), '/course/index.php') !== false) {

    require_once('common.php');

    // Generate page url.
    $pageurl = new moodle_url('/course/index.php');
    $mycourses  = optional_param('mycourses', 0, PARAM_INT);

    // Get the filters first.
    $filterdata = \theme_remui_coursehandler::get_course_filters_data();

    $templatecontext['hasregionmainsettingsmenu'] = !$OUTPUT->region_main_settings_menu();

    $pagelayout = get_config('theme_remui', 'categorypagelayout');

    if ($pagelayout !== "0") {
        $pagelayout = 'layout'.$pagelayout;
        $templatecontext[$pagelayout] = true;
    } else {
        $templatecontext['oldlayout'] = true;
    }

    $templatecontext['categories'] = $filterdata['catdata'];
    $templatecontext['searchhtml'] = $filterdata['searchhtml'];

//    $courses = get_courses();
// NF sostituisco con alternativa
$courses=$DB->get_records_sql("select c.*,cd1.value as slot_durata,cd2.value as durata,cd3.value as brand, if (obbligatorio=1, 1, if (facoltativo=1,2,null) ) as priority
from mdl_course c
inner join mdl_customfield_data cd1 on c.id=cd1.instanceid and cd1.fieldid in (select id from mdl_customfield_field where shortname='slot_durata')
inner join mdl_customfield_data cd2 on c.id=cd2.instanceid and cd2.fieldid in (select id from mdl_customfield_field where shortname='durata')
inner join mdl_customfield_data cd3 on c.id=cd3.instanceid and cd3.fieldid in (select id from mdl_customfield_field where shortname='brand')
left join
(
	select courseid, max(obb) as obbligatorio, max(fac) as facoltativo from (
	select e.courseid, if (enrol in ('db','manual'),1,0) as obb, if (enrol in ('self','link'), 1, 0) as fac
	from mdl_enrol e 
	inner join mdl_user_enrolments ue on e.id=ue.enrolid and userid=$USER->id
	) enr
	group by courseid
) enrol on c.id=enrol.courseid
where visible=1 and c.id<>1");

    $courses = mycourse_custom_info::getAllInfo($courses, $USER->id);

////    $templatecontext['loading'] = $CFG->wwwroot."/theme/remui/pix/loader.gif";

    $templatecontext['customfieldsAll'] = mycustomfield_custom_info::getAllCoursesClassificForDurata($courses);
    $templatecontext['customfields'] = $templatecontext['customfieldsAll']['normalized'];
    $templatecontext['customfieldsjson'] = $templatecontext['customfieldsAll']['json'];

    $templatecontext['customfieldsBrandAll'] = mycustomfield_custom_info::getAllCoursesClassificForBrand($courses);
    $templatecontext['customfieldsBrand'] = $templatecontext['customfieldsBrandAll']['normalized'];
    $templatecontext['customfieldsBrandjson'] = $templatecontext['customfieldsBrandAll']['json'];

    $templatecontext['prorityAll'] = mycourse_custom_info::getAllCoursesPriorityNF($courses);
    $templatecontext['prorityNormal'] = $templatecontext['prorityAll']['normalized'];
    $templatecontext['prorityjson'] = $templatecontext['prorityAll']['json'];

    $templatecontext['courseStatusAll'] = mycourse_custom_info::getAllCoursesClassificForProgressNF($courses);
    $templatecontext['coursestatus'] = $templatecontext['courseStatusAll']['normalized'];
    $templatecontext['coursestatusjson'] = $templatecontext['courseStatusAll']['json'];

    $categoryid = 'all';
    $categoryid = optional_param('category', $categoryid, PARAM_RAW);

    if ($categoryid != 'all') {
        if (core_course_category::get($categoryid, IGNORE_MISSING) == null) {
            $categoryid = 'all';
        }
    }

    $templatecontext['defaultcat'] = $categoryid;

    echo $OUTPUT->render_from_template('theme_remui/coursearchive', $templatecontext);
	
} else {
    require_once('columns2.php');
}
