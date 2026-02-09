<?php
	namespace mycourse_enrollment;
	require_once($CFG->libdir."/customlib/mycourse_custom_info.php");

	use mycourse_info\mycourse_custom_info;

	class mycourse_custom_enrollment {
		/**
		 * The function get_course_with_type_enrollment return all course of user with particolar method enrollment
		 * @param $user_id, id of the user
		 * @param $type, method of enrollment
		 * 
		 * @return list courses with particolar method enrollment
		*/
		public static function get_course_with_type_enrollment($user_id, $type=null){
			global $CFG, $DB, $OUTPUT, $USER;
			$courses = enrol_get_all_users_courses($user_id, true);
			$course_with_enrollment = array();
			if ( $type==null ) {
				return $courses;
			}
			foreach ( $courses as $course ){
				if ( $course->visible == 0) continue;
				if( self::is_enrollment_of_type($course->id, $user_id, $type) ){
					array_push($course_with_enrollment,$course);
				}
			}
			return $course_with_enrollment;
		}

		public static function get_course_with_type_enrollment_completed_info($user_id, $type=null){
			global $CFG, $DB, $OUTPUT, $USER;
			$courses = enrol_get_all_users_courses($user_id, true);
			$course_with_enrollment = array();
			if ( $type==null ) {
				return $courses;
			}
			foreach ( $courses as $course ){
				if ( $course->visible == 0) continue;
				if( self::is_enrollment_of_type($course->id, $user_id, $type) ){
					mycourse_custom_info::getAllInfoSingleCourse($course);
					array_push($course_with_enrollment,$course);
				}
			}			
			return $course_with_enrollment;
		}

	   /**
	    * The function return all courses where the type enroll is $type
		 * @param $user_id, id of the user
		 * @param $type, method of enrollment
		 * 
		 * @return list courses with particolar method enrollment
		*/
		public static function get_course_with_type_enrollment_link($user_id, $type=null){
			global $CFG, $DB, $OUTPUT, $USER;
			$courses = get_courses();
			$course_with_enrollment = array();
			if ( $type==null ) {
				return $courses;
			}
			foreach ( $courses as $course ){
				if ( $course->visible == 0) continue;
				if( self::is_enrollment_of_type_link($course->id, $type) ){
					array_push($course_with_enrollment,$course);
				}
			}
			return $course_with_enrollment;
		}

		public static function normalizedTypeEnrol($type){
			$pieces = explode(",", $type);
			$array_element = array();
			foreach ( $pieces as $piece ) {
				$piece = trim($piece);
				$piece = "'" . $piece . "'";
				array_push($array_element, $piece);
			}
			$string = implode(",",$array_element);
			return "(".$string.")";

		}

	   /**
	    * The function return true or false if course is type method enrollment and user_id is $user_id
		* @param $user_id, id of the user
		* @param $type, method of enrollment
		* @param $course_id, id of the course
		* 
		* @return true o false
		*/
		public static function is_enrollment_of_type($course_id, $user_id, $type){
			global $CFG, $DB, $OUTPUT, $USER;
			$all_type = self::normalizedTypeEnrol($type);
			$count = $DB->get_record_sql("SELECT count(*) as num FROM  {$CFG->prefix}enrol as enrol,  {$CFG->prefix}user_enrolments as  user_enrolments where enrol.courseid= ". $course_id ." and enrol.enrol in " . $all_type . " and user_enrolments.userid=" . $user_id ." and user_enrolments.enrolid = enrol.id and user_enrolments.status=0 and enrol.status=0" );
			if( $count->num>0 ) return true;
			return false;
		}
		
	   /**
	    * 
	    * The function return true or false if course is type method enrollment
		* @param $user_id, id of the user
		* @param $type, method of enrollment
		* @param $course_id, id of the course
		* 
		* @return true o false
		*/
		public static function is_enrollment_of_type_link($course_id, $type){
			global $CFG, $DB, $OUTPUT, $USER;
			$all_type = self::normalizedTypeEnrol($type);
			$count = $DB->get_record_sql("SELECT count(*) as num FROM  {$CFG->prefix}enrol as enrol where enrol.courseid= ". $course_id ." and enrol.enrol in " . $all_type . " and enrol.status=0" );
			if( $count->num>0 ) return true;
			return false;
		}

	}
