<?php


/*$PAGE->set_url('/blocks/simplehtml/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
$PAGE->set_heading(get_string('edithtml', 'block_simplehtml'));

*/
require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die;

global $CFG, $USER ,$PAGE;

$renderable = new block_ael_gamification\output\main();

require_login();

$PAGE->set_url('/blocks/ael_gamification/view/view.php', array('id' => $courseid));
$PAGE->set_pagelayout('standard');
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_ael_gamification/viewmain',$renderable->export_template_main_view());
echo $OUTPUT->footer();