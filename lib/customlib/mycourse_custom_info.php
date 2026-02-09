<?php
	namespace mycourse_info;
	require_once($CFG->libdir."/customlib/mycourse_custom_enrollment.php");
	
	defined('MOODLE_INTERNAL') || die();
	use core_course\customfield\course_handler;
	use mycourse_enrollment\mycourse_custom_enrollment;
	global $CFG;

	class mycourse_custom_info {

	   /**
	    * The function return the progress of course
	    * @param $course, object of course
	    * @param $user_id, id of user
	    * 
	    * @return value integer of progress bar course
	    */
		public static function percentual_progress($course, $user_id) {
			global $USER;
			if($user_id == null) $user_id = $USER->id;
			$percentage = \core_completion\progress::get_course_progress_percentage($course, $user_id);
			
			if ($course->id==89 && $USER->phone1=='GRUPPO3' && $percentage>0) { 
				$percentage=100;
			}
			
			return $percentage;
		} 


	   /**
	    * The function return the status of course
	    * @param $course, object of course
	    * @param $user_id, id of user
	    * 
	    * @return value integer of status course
	    */
		public static function status_progress($course, $user_id) {
			global $USER;
			define("FUTURE", "0");
			define("IN_PROGRESS", "1");
			define("PAST", "2");
			if($user_id == null) $user_id = $USER->id;
			$progress = \core_completion\progress::get_course_progress_percentage($course, $user_id);
			
			if ($course->id == 89 && $USER->phone1=='GRUPPO3' && $progress>0) $progress = 100;
			
			if($progress==0) return FUTURE;
			if($progress>0 && $progress!=100) return IN_PROGRESS;
			if($progress==100) return PAST;
		}


	   /**
	    * The function check if course is principal
	    * @param $course_id,id of object of course
	    * @param $user_id, id of user
	    * 
	    * @return value boolean if course is principal
	    */
		public static function isPrincipale($course_id, $user_id) {
			global $CFG, $DB, $OUTPUT, $USER;
			if($user_id == null) $user_id = $USER->id;
			$sql = "select isPrincipale40(".$course_id.",".$user_id.") as pr";
			try{
				$check = $DB->get_record_sql($sql);
			}
			catch(\Error $e){
				return true;
			}
			return $check->pr;
		}


		/**
		 * The function return percentual progress completed total of all courses
		 * @param $user_id, id user
		 * @param $type, type of enrollment
		 * 
		 * @return percentual progress completed of all courses
		 */
		public static function percentual_progress_total_user($user_id) {
			global $USER;
			if($user_id == null) $user_id = $USER->id;
			$courses = mycourse_custom_enrollment::get_course_with_type_enrollment($user_id);
			$total_course = 0;
			$percentaul_total = 0;
			foreach ( $courses as $course ) {
				if ( $course->visible == 0) continue;
				$percentaul_partial = self::percentual_progress($course, $user_id);
				$total_course++;
				$percentaul_total += $percentaul_partial;
			}
			if( $total_course == 0 || $percentaul_total == 0 ) return 0;
			$percentaul_total = (int)$percentaul_total / $total_course;
			if( $percentaul_total>100 ) return 100;
			return $percentaul_total;
		}


		/**
		 * The function return percentual courses completed of all courses
		 * @param $user_id, id user
		 * @param $type, type of enrollment
		 * 
		 * @return percentual courses completed of all courses
		 */
		public static function percentual_progress_completed_of_all_courses($user_id, $type=null){
			$courses = mycourse_custom_enrollment::get_course_with_type_enrollment($user_id, $type);
			$course_future_progress_and_progress = array();
			$course_past = array();
			foreach ( $courses as $course ) {
				if( self::status_progress($course, $user_id)==2 )
				{
					array_push($course_past, $course);
				}
				else
				{
					array_push($course_future_progress_and_progress, $course);
				}
			}
			if (count($courses) ==0 ) return 0;
			$calc = (count($course_past) * 100) / count($courses);
			return round($calc,1,PHP_ROUND_HALF_UP);
		}

		public static function getImageOfCourses($courses){
			$courses_with_images = array();
			if( count($courses)==0 ) return $courses;
			$i=0;
			foreach( $courses as $course) {
				$course->courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
				$courses_with_images[$i] = $course;
				$i++;
			}
			return $courses_with_images;
		}

		public static function getUrlCourses($courses) {
			global $CFG;
			$courses_with_url = array();
			if( count($courses)==0 ) return $courses;
			$i=0;
			foreach( $courses as $course) {
				$course->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
				$courses_with_url[$i] = $course;
				$i++;
			}
			return $courses_with_url;
		}

		public static function getCategory($courses){
			global $CFG;
			global $DB;
			$courses_with_category = array();
			if( count($courses)==0 ) return $courses;
			$i=0;
			foreach( $courses as $course) {
				$course->categoryname = $DB->get_record('course_categories', array('id' => $course->category))->name;
				$courses_with_category[$i] = $course;
				$i++;
			}
			return $courses_with_category;
		}

		public static function getCustomfieldDurata($courses){
			global $CFG;
			global $DB;
			$courses_with_durata = array();
			if( count($courses)==0 ) return $courses;
			$i=0;

			foreach( $courses as $course) {
				// recupera il customfield corretto
            	$handler = \core_course\customfield\course_handler::create();
	            if ($customfields = $handler->export_instance_data($course->id)) {
	                $courseinfo['customfields'] = [];
	                foreach ($customfields as $data) {
	                    $courseinfo['customfields'][] = [
	                        'type' => $data->get_type(),
	                        'value' => $data->get_value(),
	                        'valueraw' => $data->get_data_controller()->get_value(),
	                        'name' => $data->get_name(),
	                        'shortname' => $data->get_shortname()
	                    ];
	                }
	            }
	            foreach($courseinfo['customfields'] as $name){
	                if(strcmp($name['shortname'], "durata")== 0)
	                {
	                    $course->customfielddurata = $name['value'];
	                }
	            }
				$courses_with_durata[$i] = $course;
				$i++;
			}

			return $courses_with_durata;
		}

		public static function getProgressCourses($courses, $user_id) { 
			global $CFG;
			$courses_with_progress = array();
			if( count($courses)==0 ) return $courses;
			$i=0;
			foreach( $courses as $course) {
				$course->progress = self::percentual_progress($course, $user_id);
				$courses_with_progress[$i] = $course;
				$i++;
			}
			return $courses_with_progress;
		}

		public static function getIsPrincipal($courses, $user_id) { 
			global $CFG;
			$courses_with_principal = array();
			if( count($courses)==0 ) return $courses;
			$i=0;
			foreach( $courses as $course) {
				$course->isPrincipal = self::isPrincipale($course->id, $user_id);
				$courses_with_principal[$i] = $course;
				$i++;
			}
			return $courses_with_principal;
		}

		public static function getIsConcluso($courses, $user_id) { 
			global $CFG;
			$courses_with_concluso = array();
			if( count($courses)==0 ) return $courses;
			$i=0;
			foreach( $courses as $course) {
				$progress = \core_completion\progress::get_course_progress_percentage($course, $user_id);
				if( $progress == 100 ) $course->isConcluso = 1;
				$courses_with_concluso[$i] = $course;
				$i++;
			}
			return $courses_with_concluso;
		}
		
		public static function getAllCoursesClassificForProgressNF($courses) {
		
		
			$allCoursesFuture = array();
			$allCoursesFuture1 = array_filter($courses, function($c) {
			// Includi solo se priority è settato (non NULL)
			if (isset($c->priority)) {
				// Includi solo se progress è esattamente 0 (float o stringa '0')
				return $c->progress === 0 || $c->progress === '0';
			}
			});		
			$allCoursesFuture = array_values($allCoursesFuture1);

			$allCoursesProgress = array();
			$allCoursesProgress2 = array_filter($courses, function($c) {
			// Includi solo se priority NON è NULL
			if (isset($c->priority)) {
				// Includi solo se progress è > 0 e < 100
				return is_numeric($c->progress) && $c->progress > 0 && $c->progress < 100;
			}
			});
			$allCoursesProgress = array_values($allCoursesProgress2);

			$allCoursesPast = array();
			$allCoursesPast3 = array_filter($courses, function($c) {
			// Includi solo se priority è settato (non NULL)
			if (isset($c->priority)) {
				// Includi solo se progress è esattamente 0 (float o stringa '0')
				return is_numeric($c->progress) && floatval($c->progress) === 100.0;
			}
			});	
			$allCoursesPast = array_values($allCoursesPast3);


			$allCourses = array();
			$object_course = new \stdClass();
			$object_course->name = "In corso";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $allCoursesProgress;
			$object_course->length= count($allCoursesProgress);
			array_push($allCourses, $object_course);

			$object_course = new \stdClass();
			$object_course->name = "Da completare";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $allCoursesFuture;
			$object_course->length= count($allCoursesFuture);
			array_push($allCourses, $object_course);

			$object_course = new \stdClass();
			$object_course->name = "Conclusi";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $allCoursesPast;
			$object_course->length= count($allCoursesPast);
			array_push($allCourses, $object_course);

			$completedArray['normalized']=$allCourses;
			$completedArray['json'] = json_encode($allCourses);

			return $completedArray;	
		}
		

		public static function getAllCoursesClassificForProgress($user_id=null, $type=null) {
			global $USER;
			if( $user_id == null ) $user_id = $USER->id;
			$courses = mycourse_custom_enrollment::get_course_with_type_enrollment($user_id, $type);
			//$courses = mycourse_custom_info::getAllInfo($courses, $user_id);

			$allCoursesFuture = array();
			$allCoursesProgress = array();
			$allCoursesPast = array();
			if( count($courses)>0 ) {
				foreach ($courses as $course) {
					if ( $course->visible == 0) continue;
					self::getAllInfoSingleCourse($course);
					$status = self::status_progress($course, $user_id);
					if($status == 0) {
						array_push($allCoursesFuture, $course);
					}
					if($status == 1) {
						array_push($allCoursesProgress, $course);
					}
					if($status == 2) {
						array_push($allCoursesPast, $course);
					}

				}
			}

			$allCourses = array();
			$object_course = new \stdClass();
			$object_course->name = "In corso";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $allCoursesProgress;
			$object_course->length= count($allCoursesProgress);
			array_push($allCourses, $object_course);

			$object_course = new \stdClass();
			$object_course->name = "Da completare";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $allCoursesFuture;
			$object_course->length= count($allCoursesFuture);
			array_push($allCourses, $object_course);

			$object_course = new \stdClass();
			$object_course->name = "Conclusi";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $allCoursesPast;
			$object_course->length= count($allCoursesPast);
			array_push($allCourses, $object_course);

			$completedArray['normalized']=$allCourses;
			$completedArray['json'] = json_encode($allCourses);

			return $completedArray;

		}

		public static function getAllCoursesPriority($user_id) {
			global $USER;
			if( $user_id == null ) $user_id = $USER->id;
			$courses1 = mycourse_custom_enrollment::get_course_with_type_enrollment_completed_info($user_id, 'database,manual');
			$courses2 = mycourse_custom_enrollment::get_course_with_type_enrollment_completed_info($user_id, 'self,link');

			$allCourses = array();
			$object_course = new \stdClass();
			$object_course->name = "Obbligatori";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $courses1;
			$object_course->length= count($courses1);
			array_push($allCourses, $object_course);

			$object_course = new \stdClass();
			$object_course->name = "Facoltativi";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $courses2;
			$object_course->length= count($courses2);
			array_push($allCourses, $object_course);

			$completedArray = array();
			$completedArray['normalized']=$allCourses;
			$completedArray['json'] = json_encode($allCourses);

			return $completedArray;

		}



		public static function getAllCoursesPriorityNF($courses) {
			global $USER;
			if( $user_id == null ) $user_id = $USER->id;
//			$courses1 = mycourse_custom_enrollment::get_course_with_type_enrollment_completed_info($user_id, 'database,manual');
//			$courses2 = mycourse_custom_enrollment::get_course_with_type_enrollment_completed_info($user_id, 'self,link');


$courses1 = array();
$filtered_courses1 = array_filter($courses, function($c) {
    return isset($c->priority) && $c->priority === '1';
});
$courses1 = array_values($filtered_courses1);


$courses2 = array();
$filtered_courses2 = array_filter($courses, function($c) {
    return isset($c->priority) && $c->priority === '2';
});
$courses2 = array_values($filtered_courses2);



			$allCourses = array();
			$object_course = new \stdClass();
			$object_course->name = "Obbligatori";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $courses1;
			$object_course->length= count($courses1);
			array_push($allCourses, $object_course);

			$object_course = new \stdClass();
			$object_course->name = "Facoltativi";
			$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
			$object_course->content = $courses2;
			$object_course->length= count($courses2);
			array_push($allCourses, $object_course);

			$completedArray = array();
			$completedArray['normalized']=$allCourses;
			$completedArray['json'] = json_encode($allCourses);

			return $completedArray;

		}

		

		public static function getAllInfo($courses, $user_id){
			global $DB;
			global $CFG;
			global $USER;
			if( $user_id == null ) $user_id = $USER->id;
			$courses_with_info = array();
			if( count($courses)==0 ) return $courses;
			$i=0;
			foreach( $courses as $course) {
				$course->courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
				$course->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
				$course->categoryname = $DB->get_record('course_categories', array('id' => $course->category))->name;
				if ($course->priority==1 || $course->priority==2) {
					$course->progress = self::percentual_progress($course, $user_id);
					$course->myprogress = $course->progress;
				}
//				$course->isPrincipal = self::isPrincipale($course->id, $user_id);
				self::getCustomfieldDurataSingleCourse($course);
				$courses_with_info[$i] = $course;
				$i++;
			}
			return $courses_with_info;
		}

		public static function getAllInfoSingleCourse($course){
			global $DB;
			global $CFG;
			global $USER;
			if ( $course == null || $course == '') return $course;
			$course->courseimage = \core_course\external\course_summary_exporter::get_course_image($course);
			$course->courseurl = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
			$course->categoryname = $DB->get_record('course_categories', array('id' => $course->category))->name;
////			$course->progress = self::percentual_progress($course, $USER->id);
			$course->isPrincipal = self::isPrincipale($course->id, $USER->id);
			self::getCustomfieldDurataSingleCourse($course);
		}

		public static function getCustomfieldDurataSingleCourse($course){
			$handler = \core_course\customfield\course_handler::create();
            if ($customfields = $handler->export_instance_data($course->id)) {
                $courseinfo['customfields'] = [];
                foreach ($customfields as $data) {
                    $courseinfo['customfields'][] = [
                        'type' => $data->get_type(),
                        'value' => $data->get_value(),
                        'valueraw' => $data->get_data_controller()->get_value(),
                        'name' => $data->get_name(),
                        'shortname' => $data->get_shortname()
                    ];
                }
            }
            foreach($courseinfo['customfields'] as $name){
                if(strcmp($name['shortname'], "durata")== 0)
                {
                    $course->customfielddurata = $name['value'];
                }
            }
		}

	}
