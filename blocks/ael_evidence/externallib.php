<?php


defined('MOODLE_INTERNAL') || die;

require_once("$CFG->libdir/externallib.php");
require_once($CFG->libdir . "/customlib/mycourse_custom_info.php");
require_once($CFG->libdir."/customlib/mycourse_custom_enrollment.php");


use core_course\external\course_summary_exporter;
use stdClass;
use mycourse_info;
use mycourse_enrollment;
class ael_evidence_external extends external_api {
    
    /************/
 

   
   public static function get_all_courses_parameters() {
       return new external_function_parameters(
         array('typeEnrol' => new external_value(PARAM_TEXT, 'optional param'),
               'customfield' => new external_value(PARAM_TEXT, 'optional param'),
               'customfieldvalue' => new external_value(PARAM_RAW, 'optional param'),
         ) 
       );
    }

   
    public static function get_all_courses($typeEnrol,$customfield,$customfieldvalue) {
        global $CFG, $DB, $USER,$OUTPUT;

       
         $typeEnrol = empty($typeEnrol) ? null : $typeEnrol;



        $courses =   $typeEnrol ?  \mycourse_enrollment\mycourse_custom_enrollment::get_course_with_type_enrollment($USER->id ,  $typeEnrol) : get_courses();
        
        $coursesEvidence = [];
     
        foreach ($courses as $course) {
            

            $c = new stdClass();
            $c->id = $course->id;
            $category = $DB->get_record('course_categories', array('id'=>$course->category));
            $c->coursecategory = $category->name;
            $c->courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
            $c->fullname = $course->fullname;
            $c->viewurl = (new moodle_url('/course/view.php', array('id' => $course->id)))->out(false);
            

          

            $c->time = self::getTimeCourse($course->id);
            if($c->time){
               $time =  str_replace(' ore','h',$c->time);
               $time =  str_replace(' min','m',$time);
               $c->time = $time;
            }
            
            $c->summary = self::getDescriptionCourse($course->id);
            if(!empty($customfield) ){
                if(self::isCustomField($c->id,$customfield,$customfieldvalue))
                 $coursesEvidence[] = $c;
            }
            else{
             $coursesEvidence[] = $c;
            }
        }

        return $coursesEvidence;
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
                        'time' => new external_value(PARAM_TEXT, 'time'),
                        'summary' => new external_value(PARAM_RAW, 'summary')
                    )
            ));
    }


  
 
    private static function getDescriptionCourse($course_id){
        global $DB;
        $course = $DB->get_record('course', array('id'=>$course_id));
        
        return $course->summary;
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

    private static function isCustomField($courseId,$customfield,$value){
        $handler = core_course\customfield\course_handler::create();
        $customfields = $handler->export_instance_data($courseId);
        if ($customfields) {
           
            foreach ($customfields as $data) {
                if($data->get_shortname() == $customfield && !empty($value)){
                    
                    return  strtoupper($data->get_data_controller()->get_value()) ==  strtoupper($value);
                    
                }
                
            }
            return null;
           
        }else{
            return null;
        }
    }




}
