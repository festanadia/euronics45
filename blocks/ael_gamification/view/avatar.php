<?php





require_once(dirname(__FILE__) . '/../../../config.php');
defined('MOODLE_INTERNAL') || die;

global $CFG, $USER ,$PAGE,$DB;

$renderable = new block_ael_gamification\output\main();

require_login();

$PAGE->set_url('/blocks/ael_gamification/view/avatar.php');
$PAGE->set_pagelayout('standard');
$PAGE->requires->js( $CFG->root.'/blocks/ael_gamification/js/swiper-lib.js',true);

$avatar =  $DB->get_record('ael_gamification_avatar', array('userid'=>$USER->id));
$data = null;
if($avatar && $avatar->data) 
    $data = $avatar->data;

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('block_ael_gamification/avatarview',['data'=>$data ]);
echo $OUTPUT->footer();