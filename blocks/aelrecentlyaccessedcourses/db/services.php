<?php
$functions = array(
    'ael_course_get_courses' => array(
        'classname' => 'ael_course_external',
        'methodname' => 'get_ael_courses',
        'classpath' => 'blocks/aelrecentlyaccessedcourses/externallib.php',
        'description' => 'Return course details',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);