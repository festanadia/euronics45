<?php

namespace block_ael_gamification\output;
defined('MOODLE_INTERNAL') || die();




use renderable;
use renderer_base;
use templatable;
use stdClass;
use block_ael_gamification\config\TypeCardAel;
/**
 * Class containing data for Recently accessed courses block.
 *
 * @package    block_aelrecentlyaccessedcourses
 * @copyright  2018 Victor Deniz <victor@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass|array
     */
    private $config;

    public function setConfig($c){
        $this->config = $c;
    }

    public function export_for_template(renderer_base $output) {
        global $USER ,$CFG,$OUTPUT;
        require_once($CFG->dirroot . '/blocks/ael_gamification/classes/typeCardAel.php');
       

        
        $imgBoxPoint = $output->image_url('bullseye-arrow-light', 'block_ael_gamification')->out(false);
        $imgBoxRanking = $output->image_url('trophy-alt-light', 'block_ael_gamification')->out(false);
        $imgBoxMedal = $output->image_url('medal-light', 'block_ael_gamification')->out(false);
        $titleCard = get_string('titleCardDefault', 'block_ael_gamification');

        if (!empty($this->config->titlecard)) {
            $titleCard = $this->config->titlecard;
        }
        
        $cardPoint = $this->getDataCard($imgBoxPoint,TypeCardAel::POINT,'tab2');
        $cardRanking = $this->getDataCard($imgBoxRanking, TypeCardAel::RANKING,'tab2');
        $cardMedal = $this->getDataCard($imgBoxMedal,TypeCardAel::MEDAL,'tab1');

        $card = new stdClass();
        $card->type = $type;
        $card->image = $image;

        return [
            'userid' => $USER->id,
            'cardPoint'=> $cardPoint,
            'cardRanking'=> $cardRanking,
            'cardMedal'=> $cardMedal,
            'userData' => $this->userData(),
            'titleCard' => $titleCard,
    
            
        ];
    }


    public function export_template_main_view() {
        global $USER ,$CFG,$OUTPUT;
        require_once($CFG->dirroot . '/blocks/ael_gamification/classes/typeCardAel.php');
        
        $imgBoxPoint = $OUTPUT->image_url('bullseye-arrow-light', 'block_ael_gamification')->out(false);
        $titleCard = get_string('titleCardDefault', 'block_ael_gamification');

        if (!empty($this->config->titlecard)) {
            $titleCard = $this->config->titlecard;
        }
        
        $cardPoint = $this->getDataCard($imgBoxPoint,TypeCardAel::POINT,'');
    
        $card = new stdClass();
        $card->type = $type;
        $card->image = $image;

        return [
            'userid' => $USER->id,
            'cardPoint'=> $cardPoint,
            'userData' => $this->userData(),
            'titleCard' => $titleCard 
            
        ];
    }

    private function getDataCard($image,$type='',$tab){
        global $CFG;
        
        $card = new stdClass();
        $card->type = $type;
        $card->image = $image;
        $card->link = $CFG->wwwroot."/blocks/ael_gamification/view/view.php#$tab";
        return $card;
    }


    private function userData(){
        global $USER,$OUTPUT,$CFG;
        $config = get_config('block_ael_gamification');
        $avatar =   $OUTPUT->user_picture($USER, array('size' => 109));
        $fullname = $USER->firstname . ' '.$USER->lastname;
        $company = $USER->institution;

        $data = new stdClass();
        $data->avatar = $avatar;
        $data->fullname = $fullname;
        $data->company = $company;
        $data->enabled_custom_avatar = $config->enabled_custom_avatar;
        $data->linkCustom = $CFG->wwwroot."/blocks/ael_gamification/view/avatar.php";

        return $data;
    }
}
