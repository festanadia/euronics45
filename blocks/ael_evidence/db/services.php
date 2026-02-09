<?php
$functions = array(
    'ael_evidence_get_all_courses' => array(
        'classname' => 'ael_evidence_external',
        'methodname' => 'get_all_courses',
        'classpath' => 'blocks/ael_evidence/externallib.php',
        'description' => 'Return all courses user in evidence ',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
    
);

