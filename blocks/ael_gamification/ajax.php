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
 * Snapshot upload handler
 *
 * @license   https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @package   block_avatar
 * @copyright 2015 MoodleFreak.com
 * @author    Luuk Verhoeven
 **/

define('AJAX_SCRIPT', true);
define('NO_DEBUG_DISPLAY', true);

require_once(__DIR__ . '/../../config.php');
defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/gdlib.php");
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot . '/files/externallib.php');
require_once($CFG->libdir . '/externallib.php');

require_login(get_site(), true, null, true, true);

$file = $_POST["file"];
$data = $_POST["data"];


$systemcontext = context_system::instance();
$result = new stdClass();
$result->status = false;
$result->error = null;



    if (stristr($file, 'base64,')) {
        // Convert webrtc.
        $file = explode('base64,', $file);
        $file = end($file);
    }

    // Decode.
    //$file = base64_decode($file);

    if (empty($file)) {
        $result->error = "File empty";
        echo json_encode($result);
        return;
    }
    if (empty($data)) {
        $result->error = "Data empty";
        echo json_encode($result);
        return;
    }

    $context = context_user::instance($USER->id, MUST_EXIST);

    $tempfile = tempnam(sys_get_temp_dir(), 'avatar');
    file_put_contents($tempfile, $file);

    $avatar =  $DB->get_record('ael_gamification_avatar', array('userid'=>$USER->id));

    if($avatar){
        $DB->set_field('ael_gamification_avatar', 'data', $data , ['userid' => $USER->id]);

    }else{
            $avatarData = new stdClass();
            $avatarData->userid = $USER->id;
            $avatarData->data = $data;
            $DB->insert_record('ael_gamification_avatar', $avatarData);
    }


    $fileinfo = avatar_upload($file);
    $itemid = $fileinfo['itemid'];
    if($itemid){
		$outcome = new stdClass();
        $outcome->itemid = $itemid;
        $user = $USER;
        $user->deletepicture = false;
        $user->imagefile = $itemid;
        
       $filemanageroptions = array('maxbytes' => $CFG->maxbytes, 'subdirs' => 0, 'maxfiles' => 1, 'accepted_types' => 'web_image');
        $success =  core_user::update_picture($USER,$filemanageroptions);
        $outcome->success = $success;
        $outcome->file = $fileinfo;
        if ($success) {
            $userpicture = new user_picture(core_user::get_user($user->id));
            $userpicture->size = 1; // Size f1.
            $outcome->profileimageurl = $userpicture->get_url($PAGE)->out(false);
            $USER->picture = $itemid;
            $result->status  = true;
        }
    }




echo json_encode($result);


function avatar_upload($file) {
    global $USER;
 
    $context = context_user::instance($USER->id);
    $contextid = $context->id;
    $component = "user";
    $filearea = "draft";
    $itemid = 0;
    $filepath = "/";
    $filename = 'avatar.png';
    $filecontent = $file;
    $contextlevel = null;
    $instanceid = null;
    $browser = get_file_browser();
    
 
    $file = $browser->get_file_info($context, $component, $filearea, $itemid, $filepath, $filename);
  
    

    $fileinfo = core_files_external::upload($contextid, $component, $filearea, $itemid, $filepath,
    $filename, $filecontent, $contextlevel, $instanceid);
    $fileinfo = external_api::clean_returnvalue(core_files_external::upload_returns(), $fileinfo);
    
    
    $itemid = $fileinfo['itemid'];
    
    return $fileinfo ;

}