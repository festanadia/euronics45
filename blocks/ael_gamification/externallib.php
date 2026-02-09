<?php


defined('MOODLE_INTERNAL') || die;


require_once($CFG->libdir . "/customlib/mycourse_custom_info.php");
require_once("$CFG->libdir/externallib.php");
require_once($CFG->libdir . '/badgeslib.php');
require_once("$CFG->libdir/externallib.php");
require_once($CFG->dirroot.'/user/lib.php');
use stdClass;


class ael_gamification_external extends external_api {
    
    /************/
    const TYPE_ENROL = "manual,database,self";
   public static function get_position_ranking_parameters() {
       return new external_function_parameters(
         array('param' => new external_value(PARAM_TEXT, 'optional param')) 
       );
    }

 
    public static function get_position_ranking($param) {
        global $CFG, $DB,$USER;

        
        $from = " FROM {eur_contest_classifica} b ";
        $sql =  "SELECT *  $from";
        $records = $DB->get_records_sql($sql);

        $userSession = self::getUserInRacking($records,$USER->id);

        if($userSession){
            return $userSession->pos."";
        }else{
            return null;
        }
        
    }

  
    public static function get_position_ranking_returns() {
        return new external_value(PARAM_TEXT, 'position');
    }



    /************/


    public static function get_point_parameters() {
        return new external_function_parameters(
          array('param' => new external_value(PARAM_TEXT, 'optional param')) 
        );
     }
 
    
     public static function get_point($param) {
        
        global $CFG, $DB,$USER;

        $from = " FROM {eur_contest_classifica} b ";
        $sql =  "SELECT *  $from";
        $records = $DB->get_records_sql($sql);
        
        $userSession = self::getUserInRacking($records,$USER->id);
        
        if($userSession){
            return $userSession->points."";
        }else{
            return "0";
        }
     
     }
 
   
     public static function get_point_returns() {
         return new external_value(PARAM_TEXT, 'point');
     }




    /************/

     

     public static function get_medal_parameters() {
        return new external_function_parameters(
          array('param' => new external_value(PARAM_TEXT, 'optional param')) 
        );
     }
 
    
     public static function get_medal($param) {
         global $CFG, $DB;
         $b = self::getLastBadgesUser();
         $image = null;
         if($b)
           $image = print_badge_image($b, $b->get_context(), 'large');
           
         return  $image;
     }
 
   
     public static function get_medal_returns() {
         return new external_value(PARAM_RAW, 'point');
     }

        /************/


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
         return new external_value(PARAM_INT, 'progress');
     }


           /************/



     /*
        tab  TAB_LEFT | TAB_CENTER | TAB_RIGHT
        type  left | right

     */
    public static function get_rancking_parameters() {
        return new external_function_parameters(
          array('type' => new external_value(PARAM_TEXT, 'type rancking'),
                'tab' => new external_value(PARAM_TEXT, 'tab filter')) 
          
        );
     }
 
    
     public static function get_rancking($type_param,$tab_param) {
         global $CFG, $DB;
         $params = self::validate_parameters(self::get_rancking_parameters(),array('type' => $type_param,'tab'=>$tab_param));
         $rancking = [];
         $type = $params['type'];
         $tab = $params['tab'];
        if($tab == "TAB_LEFT"){
            $rancking = self::loadRanckingLeft($type);
        }
        if($tab == "TAB_CENTER"){
            $rancking = self::loadRanckingCenter($type);
        }
        if($tab == "TAB_RIGHT"){
            $rancking = self::loadRanckingRight($type);
        }

         

         
        
         return  $rancking;
     }
 
   
     public static function get_rancking_returns() {
              return new external_multiple_structure(
                new external_single_structure(
                        array(
                            'pos' => new external_value(PARAM_INT, 'position user'),
                            'fullname' => new external_value(PARAM_TEXT, 'fullname user'),
                            'institution' => new external_value(PARAM_TEXT, 'institution user'),
                            'department' => new external_value(PARAM_TEXT, 'department user'),
                            'points' => new external_value(PARAM_INT, 'points user'),
                            'pathAvatar'=> new external_value(PARAM_TEXT, 'avatar user'),
                            'isUserSession' => new external_value(PARAM_BOOL, 'points user')
                        )
                ));
     }




     private static function loadRanckingRight($type){
        global $USER,$DB;

        $from = $type == "left" ? " FROM {eur_contest_classifica_7} b " : " FROM {eur_contest_classifica} b ";
    

        $sql =  "SELECT *  $from";

        $records = $DB->get_records_sql($sql);


        $rancking = self::generateRancking(5,$records,$USER->id);
        return $rancking;
     }

     private static function loadRanckingLeft($type){
         global $USER,$DB;

         $from = $type == "left" ? " FROM {eur_contest_classifica_7} b " : " FROM {eur_contest_classifica} b ";


        $sql =  "SELECT *  $from where puntovendita=:puntovendita and azienda=:azienda";

        $records = $DB->get_records_sql($sql, array('puntovendita' => $USER->department,'azienda'=>$USER->institution));
  

        $rancking = self::generateRancking(5,$records,$USER->id);
        return $rancking;
      
     }

     private static function  loadRanckingCenter($type){

        global $USER,$DB;

        $from = $type == "left" ? " FROM {eur_contest_classifica_7} b " : " FROM {eur_contest_classifica} b ";


        $sql =  "SELECT *  $from where  azienda=:azienda";

        $records = $DB->get_records_sql($sql, array('azienda'=>$USER->institution));


        $rancking = self::generateRancking(5,$records,$USER->id);
        return $rancking;
       
     }

     private static function generateRancking($maxUsers,$arr_total,$userId=null){
      

        $MAXNUM = $maxUsers;
        $rancking = [];
        $pos=0;
        $arr = array_slice($arr_total,0,$MAXNUM);

        foreach ($arr as $key=>$value) {
			$pos++;
			$checkuser= $userId && $value->userid==$userId;
    
		    $rancking[] = self::getUserRkData($pos,$value->nome." ".$value->cognome,$value->azienda,$value->puntovendita,$value->punti,$checkuser,$value->userid); 
   
             
        }


        if($userId){
            $userSession = self::getUserInRacking($arr_total,$userId);
            if($userSession  && $userSession->pos > $MAXNUM )
            $rancking[] = $userSession;
        }

        return $rancking;

     }



     private static function getUserInRacking($array,$userId){
        $pos=0;
        foreach ( $array as $key=>$value ) {
            $pos++;
            if ( $userId == $value->userid ) {
                return self::getUserRkData($pos,$value->nome." ".$value->cognome,$value->azienda,$value->puntovendita,$value->punti,true,$userId);
            }
        }
    
        return false;
     }



     private static function getUserRkData($pos,$fullname,$institution,$department,$points,$isUserSession,$idUser=null){
      
         $dataUser = new stdClass();
         $dataUser->pos = $pos;
         $dataUser->fullname = ucwords($fullname);
         $dataUser->institution = $institution;
         $dataUser->department = $department;
         $dataUser->points = $points;
         $dataUser->isUserSession = $isUserSession; 
         $dataUser->pathAvatar = self::getAvatarUser($idUser);
     
         return  $dataUser;

     }

     private static function getAvatarUser($idUser){
        global $PAGE;
		
        if(!$idUser)  return null;
		
		$context = context_user::instance($idUser);
		$PAGE->set_context($context);

        $userpicture = new user_picture(core_user::get_user($idUser));
        $userpicture->size = 1; // Size f1.
        
        return $userpicture->get_url($PAGE)->out(false);
     }



           /************/


    public static function get_badges_parameters() {
        return new external_function_parameters(
          array('param' => new external_value(PARAM_TEXT, 'optional param')) 
        );
     }
 
   
     public static function get_badges($param) {
         global $CFG, $DB,$PAGE;
         $badges = badges_get_badges(1, 0, '', '' , 0, 0);
      

         $badgesNoGet = [];
         $badgesGet = [];


         foreach ($badges as $b) {
               if($b->is_active()){
                    $image = print_badge_image($b, $b->get_context(), 'large');
                    $badge = self::getBadge( $image,false,$b->name,$b->description,'');
                    $date = self::isBadgeUser($b);
                    if($date){
                        
                        $badge->enabled = true;
                        $badge->date = date('d/m/Y H:i', $date);
                        $badgesGet[] = $badge;
                    }else{
                        $badgesNoGet[] = $badge;
                    }
             
               }
               
         }

         $response = new stdClass();
         $response->get = $badgesGet;
         $response->noget = $badgesNoGet;

         return $response;
     }


     private static function isBadgeUser($badge){
        global $DB,$USER;
        $sql = "SELECT b.dateissued as date
        FROM {badge_issued} b INNER JOIN {user} u
            ON b.userid = u.id
        WHERE b.badgeid = :badgeid AND u.deleted = 0 AND u.id=:userid";

        $record = $DB->get_record_sql($sql, array('badgeid' => $badge->id,'userid'=>$USER->id));
        
        return  $record ? $record->date : null ;
     }

     private static function getLastBadgesUser(){
        global $DB,$USER;
        
        $sql = "SELECT b.dateissued , bdg.id as badgeid
        FROM {badge_issued} b
        INNER JOIN {user} u ON b.userid = u.id
        INNER JOIN {badge} bdg ON b.badgeid = bdg.id
        WHERE  u.deleted = 0 AND u.id=:userid
        ORDER BY b.dateissued DESC
        ";
        
        $record = $DB->get_record_sql($sql, array('userid'=>$USER->id));
        if($record){
            $badge = new badge($record->badgeid);
            return $badge;
        }else{
            return null;
        }
    
       
     }
 
   
     public static function get_badges_returns() {

        return new external_single_structure(
                array(
                    'get'  => new external_multiple_structure(
                        new external_single_structure(
                                    array(
                                        'pathImage' => new external_value(PARAM_RAW, 'image badge'),
                                        'enabled' => new external_value(PARAM_BOOL, 'enabled for user'),
                                        'name' => new external_value(PARAM_TEXT, 'name badge'),
                                        'desc' => new external_value(PARAM_TEXT, 'desc badge'),
                                        'date' => new external_value(PARAM_RAW, 'date badge')
                                    )
                            )
                      ),
                    'noget' => new external_multiple_structure(
                        new external_single_structure(
                                    array(
                                        'pathImage' => new external_value(PARAM_RAW, 'image badge'),
                                        'enabled' => new external_value(PARAM_BOOL, 'enabled for user'),
                                        'name' => new external_value(PARAM_TEXT, 'name badge'),
                                        'desc' => new external_value(PARAM_TEXT, 'desc badge'),
                                        'date' => new external_value(PARAM_RAW, 'date badge')
                                    )
                            )
                      )
                    
                )
            );
    
     }


     private static function getBadge($pathImage,$enabled,$name,$desc,$date){

        $data = new stdClass();
        $data->pathImage = $pathImage;
        $data->enabled = $enabled;
        $data->name = $name;
        $data->desc = $desc;
        $data->date = $date; 
    
        return  $data;
     }



}
