<?php 
	namespace mycustomfield_info;
	require_once($CFG->libdir."/customlib/mycourse_custom_enrollment.php");
	require_once($CFG->libdir."/customlib/mycourse_custom_info.php");

	defined('MOODLE_INTERNAL') || die();
	use mycourse_enrollment\mycourse_custom_enrollment;
	use mycourse_info\mycourse_custom_info;
	global $CFG;
	global $DB;

	class mycustomfield_custom_info {

		/**
		 * The function return the value of a customefield
		 * 
		 * @param $course_id, id of the course
		 * @param $key_of_search, name of the customfield
		 * 
		 * @return string, value of customfield
		 */ 
		public static function getValueOfCustomfield($course_id, $key_of_search) {
			$handler = \core_course\customfield\course_handler::create();
			if ($customfields = $handler->export_instance_data($course_id)) {
				foreach ($customfields as $data) {
					if( strcmp($data->get_shortname(), $key_of_search) == 0 ) 
						return $data->get_value();
				}
			}
			return '';
		}


		public static function getValueOfCustomfieldSTATICO($valueid, $key_of_search) {
			
			if ($key_of_search=='slot_durata') {
						 if ($valueid==1) return '< 10 min';
						 if ($valueid==2) return '10-30 min';
						 if ($valueid==3) return '30-60 min';
						 if ($valueid==4) return '1-2 ore';
						 if ($valueid==5) return '> 2 ore';
			} else if ($key_of_search=='brand') {
						if ($valueid==1) return 'AEG';
						if ($valueid==2) return 'BELLISSIMA';
						if ($valueid==3) return 'ELECTROLUX';
						if ($valueid==4) return 'GOOGLE';
						if ($valueid==5) return 'HUAWEI';
						if ($valueid==6) return 'INTEL';
						if ($valueid==7) return 'NVIDIA';
						if ($valueid==8) return 'PHILIPS';
						if ($valueid==9) return 'POLTI';
						if ($valueid==10) return 'SAMSUNG';
						if ($valueid==11) return 'HAIER';
						if ($valueid==12) return 'MICROSOFT';
						if ($valueid==13) return 'ADM';
						if ($valueid==14) return 'LENOVO';				
			}
			
			return '';
		}



		

		public static function getAllCourseOfCustomfieldnotEnroll($key_of_search, $value_of_search, $courses) {
			$course_all = array();
			
			$courses_of_return = array();	
			$rest_courses = array();	
			foreach ( $courses as $course ) {
				$value_customfield = self::getValueOfCustomfield($course->id, $key_of_search);
				$val_temp = $value_of_search;
				$value_of_search = str_replace('<', "minore",  $value_of_search);
				$value_customfield = str_replace("&lt;", "minore", $value_customfield);
				$value_of_search = str_replace('>', "maggiore",  $value_of_search);
				$value_customfield = str_replace("&gt;", "maggiore", $value_customfield);
				if ( strcmp($value_customfield, $value_of_search) == 0 ) {
					array_push($courses_of_return, $course);
				}
				else {
					array_push($rest_courses, $course);
				}
				$value_of_search = $val_temp;
			}
			return [$courses_of_return, $rest_courses];
		}

		public static function getAllCourseOfCustomfieldnotEnrollSTATICO_DURATA($key_of_search, $value_of_search, $courses) {
			$course_all = array();
			
			$courses_of_return = array();	
			$rest_courses = array();	
			foreach ( $courses as $course ) {
				$value_customfield = self::getValueOfCustomfieldSTATICO($course->slot_durata, $key_of_search);				
				$val_temp = $value_of_search;
				if ( strcmp($value_customfield, $value_of_search) == 0 ) {
					array_push($courses_of_return, $course);
				}
				else {
					array_push($rest_courses, $course);
				}
				$value_of_search = $val_temp;
			}
			return [$courses_of_return, $rest_courses];
		}

		public static function getAllCourseOfCustomfieldnotEnrollSTATICO_BRAND($key_of_search, $value_of_search, $courses) {
			$course_all = array();
			
			$courses_of_return = array();	
			$rest_courses = array();	
			foreach ( $courses as $course ) {
				$value_customfield = self::getValueOfCustomfieldSTATICO($course->brand, $key_of_search);				
				$val_temp = $value_of_search;
				if ( strcmp($value_customfield, $value_of_search) == 0 ) {
					array_push($courses_of_return, $course);
				}
				else {
					array_push($rest_courses, $course);
				}
				$value_of_search = $val_temp;
			}
			return [$courses_of_return, $rest_courses];
		}

		public static function getAllCourseOfCustomfield($user_id, $key_of_search, $value_of_search, $type=null) {
			$courses = mycourse_custom_enrollment::get_course_with_type_enrollment($user_id, $type);
			$courses = mycourse_custom_info::getAllInfo($courses, $user_id);

			
			$courses_of_return = array();		
			foreach ( $courses as $course ) {
				$value_customfield = self::getValueOfCustomfield($course->id, $key_of_search);
				if ( strcmp($value_customfield, $value_of_search) == 0 )
					array_push($courses_of_return, $course);
			}
			return $courses_of_return;
		}

		public static function getAllValuesOfCustomfieldSlotDurata() {
			global $DB;
			$record = $DB->get_record('customfield_field', ['shortname'=>'slot_durata']);
			$confidata = str_replace('\r\n', "#", $record->configdata);
			$objectJson = json_decode($confidata);
			$pieces = explode("#", $objectJson->options);
			$values_return = array();
			foreach ( $pieces as $piece ) {
				$values = new \stdClass();
				$values->value = $piece;
				array_push($values_return, $values);
			}
			return $values_return;
		}

		public static function getAllCoursesClassificForDurata($courses) {
			
			$allCustomFieldDurata = self::getAllValuesOfCustomfieldSlotDurata();

			$completedArray = array();
			$allCourses = array();
			foreach ($allCustomFieldDurata as $value) {
				$result_courses = self::getAllCourseOfCustomfieldnotEnrollSTATICO_DURATA('slot_durata' , $value->value, $courses);
					
				$fetch_courses = $result_courses[0];
				$courses = $result_courses[1];
				$object_course = new \stdClass();
				$object_course->content = $fetch_courses;
				$object_course->length= count($fetch_courses);
				$object_course->name = $value->value;
				$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
				$object_course->name_strip_space = str_replace('<', 'minore', $object_course->name_strip_space);
				$object_course->name_strip_space = str_replace('>', "maggiore", $object_course->name_strip_space);
				$object_course->name_strip_space = str_replace("&gt;", "maggiore", $object_course->name_strip_space);
				array_push($allCourses, $object_course);

			}
			
		
			$completedArray['normalized']=$allCourses;
			$completedArray['json'] = json_encode($allCourses);
			return $completedArray;
		}



		public static function getAllValuesOfCustomfieldSlotBrand() {
			global $DB;
			$record = $DB->get_record('customfield_field', ['shortname'=>'brand']);
			$confidata = str_replace('\r\n', "#", $record->configdata);
			$objectJson = json_decode($confidata);
			$pieces = explode("#", $objectJson->options);
			$values_return = array();
			foreach ( $pieces as $piece ) {
				$values = new \stdClass();
				$values->value = $piece;
				array_push($values_return, $values);
			}
			return $values_return;
		}

		public static function getAllCoursesClassificForBrand($courses) {
			$allCustomField = self::getAllValuesOfCustomfieldSlotBrand();
			$completedArray = array();
			$allCourses = array();
			foreach ($allCustomField as $value) {
				$result_courses = self::getAllCourseOfCustomfieldnotEnrollSTATICO_BRAND('brand' , $value->value, $courses);
				$fetch_courses = $result_courses[0];
				$courses = $result_courses[1];				
				$object_course = new \stdClass();
				$object_course->content = $fetch_courses;
				$object_course->length= count($fetch_courses);
				$object_course->name = $value->value;
				$object_course->name_strip_space = str_replace(' ', '', $object_course->name);
				array_push($allCourses, $object_course);

			}
			$completedArray['normalized']=$allCourses;
			$completedArray['json'] = json_encode($allCourses);
			return $completedArray;
		}

	}