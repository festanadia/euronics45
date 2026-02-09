<?php
$functions = array(
    'ael_chosenbyyou_get_category' => array(
        'classname' => 'ael_chosenbyyou_category_external',
        'methodname' => 'ael_chosenbyyouael__category',
        'classpath' => 'blocks/aelchosenbyyou/externallib.php',
        'description' => 'Return course details',
        'type' => 'read',
        'capabilities' => 'moodle/course:view, moodle/course:update, moodle/course:viewhiddencourses',
        'ajax' => true,
        'services' => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
);