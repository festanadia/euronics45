<?php


defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->libdir . "/customlib/mycourse_custom_info.php");
require_once($CFG->libdir."/customlib/mycourse_custom_enrollment.php");


use core_course\external\course_summary_exporter;
use stdClass;
use mycourse_info;
use mycourse_enrollment;
class ael_training_external extends external_api {
    
    /************/
 

    const TYPE_ENROL = "manual,database";


   public static function get_all_courses_parameters() {
       return new external_function_parameters(
         array('param' => new external_value(PARAM_TEXT, 'optional param')) 
       );
    }

   
    public static function get_all_courses($param) {
        global $CFG, $DB, $USER,$OUTPUT;
        $courses = \mycourse_enrollment\mycourse_custom_enrollment::get_course_with_type_enrollment($USER->id , self::TYPE_ENROL);
        $courserUser = [];
     
        foreach ($courses as $course) {
            $c = new stdClass();
            $c->id = $course->id;
            $c->status = self::getStatusCourse($course,$USER->id);
            $category = $DB->get_record('course_categories', array('id'=>$course->category));
            $c->coursecategory = $category->name;
            $c->courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
            $c->fullname = $course->fullname;
            $c->viewurl = (new moodle_url('/course/view.php', array('id' => $course->id)))->out(false);
            $c->progress = \mycourse_info\mycourse_custom_info::percentual_progress($course,$USER->id);			
            $c->isPrincipal = \mycourse_info\mycourse_custom_info::isPrincipale($course->id,$USER->id);
            

            $c->time = self::getTimeCourse($course->id);
            /*if($c->time){
               $time =  str_replace(' ore','h',$c->time);
               $time =  str_replace(' min','m',$time);
               $c->time = $time;
            }*/
            
            $courserUser[] = $c;
        }

        return $courserUser;
    }

  
    
    public static function get_all_courses_returns() {
        return new external_multiple_structure(
            new external_single_structure(
                    array(
                        'id' => new external_value(PARAM_TEXT, 'id'),
                        'coursecategory' => new external_value(PARAM_TEXT, 'coursecategory'),
                        'courseimage' => new external_value(PARAM_TEXT, 'courseimage'),
                        'fullname' => new external_value(PARAM_TEXT, 'fullname'),
                        'viewurl' => new external_value(PARAM_TEXT, 'viewurl'),
                        'status' => new external_value(PARAM_TEXT, 'status'),
                        'progress' => new external_value(PARAM_TEXT, 'progress'),
                        'time' => new external_value(PARAM_TEXT, 'time'),
                        'isPrincipal'=> new external_value(PARAM_BOOL, 'isPrincipal')
                    )
            ));
    }


    private static function getStatusCourse($course,$idUser){
		global $USER;
		
        $status = ['START','INPROGRESS','FINISH'];
        $codeStatuse = \mycourse_info\mycourse_custom_info::status_progress($course,$idUser);	
		return $status[$codeStatuse];
        
    }

    private static function getTimeCourse($courseId){
        $handler = core_course\customfield\course_handler::create();
        $customfields = $handler->export_instance_data($courseId);
        if ($customfields) {
            foreach ($customfields as $data) {
                if($data->get_shortname() == 'durata')
                 return  $data->get_value();
            }
            return null;
           
        }else{
            return null;
        }
    }



    public static function get_progress_parameters() {
        return new external_function_parameters(
          array('param' => new external_value(PARAM_TEXT, 'optional param')) 
        );
     }
 
    
     public static function get_progress($param) {
         global $CFG, $DB ,$USER ;
         $perc = \mycourse_info\mycourse_custom_info::percentual_progress_completed_of_all_courses($USER->id,self::TYPE_ENROL);
         return  intval( $perc );
         
     }
 
   
     public static function get_progress_returns() {
         return new external_value(PARAM_INT, 'position');
     }
 



    


   



}
