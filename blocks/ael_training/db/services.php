<?php
$functions = array(
    'ael_training_get_all_courses' => array(
        'classname' => 'ael_training_external',
        'methodname' => 'get_all_courses',
        'classpath' => 'blocks/ael_training/externallib.php',
        'description' => 'Return all courses user ',
        'type' => 'read',
       // 'capabilities' => 'moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'ael_training_get_progress' => array(
        'classname' => 'ael_training_external',
        'methodname' => 'get_progress',
        'classpath' => 'blocks/ael_training/externallib.php',
        'description' => 'Return progress ',
        'type' => 'read',
       // 'capabilities' => 'moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
    
);

