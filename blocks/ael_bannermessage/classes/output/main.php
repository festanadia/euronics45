<?php

namespace block_ael_bannermessage\output;
defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir.'/formslib.php');



use renderable;
use renderer_base;
use templatable;



class main implements renderable, templatable {
    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param renderer_base $output
     * @return \stdClass|array
     */
 
    private $config;
    private $contextId;
  
    public function setConfig($c,$contextId){
        $this->config = $c;
        $this->contextId = $contextId;
    }

    public function export_for_template(renderer_base $output) {
        global $USER ,$CFG,$OUTPUT, $DB;

     
        $idImage= $this->config->image;
        $urlImage = $this->getUrlImage($output,$idImage);
       

        return [
            'userid' => $USER->id,
            'title' => $this->config->titlecard,
            'desc' => $this->config->textcard,
            'cta' => $this->config->ctabutton,
            'url' => $this->config->ctabuttonurl,
            'image' => $urlImage 
        ];
    }


    private function getUrlImage($output,$idImage){
        global $USER ,$CFG,$OUTPUT, $DB;
        
        $urlImage =  $output->image_url('bkg', 'block_ael_bannermessage')->out(false);
        if($idImage){
             $table = 'files';
             $select = "component = 'block_ael_bannermessage' AND contextid = '" . $this->contextId ."' AND filename != '.'";
             $fields = 'filename';
             $sort = 'filename';
             $images = $DB->get_records_select($table, $select, NULL, $sort, $fields);
           
             foreach ($images as $image) { 
                 $imagefile = $image->filename;
                 $urlImage  = $CFG->wwwroot . '/pluginfile.php/' . $this->contextId . '/block_ael_bannermessage/content/' . $imagefile;
             }
         }

         return $urlImage;
    }


    

   


  
}
