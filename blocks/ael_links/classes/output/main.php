<?php

namespace block_ael_links\output;
defined('MOODLE_INTERNAL') || die();




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
        global $USER ,$CFG,$OUTPUT;


        
       
       

        return [
            'userid' => $USER->id,
            'typeEnrol' => $this->config->typeEnrol,
            'customfield' => $this->config->customfield,
            'customfieldvalue' => $this->config->customfieldvalue,
            'title' => $this->config->titleCard ? $this->config->titleCard  : get_string('title_card', 'block_ael_links')
        ];
    }


    

   


  
}
