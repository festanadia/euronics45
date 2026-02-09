<?php

namespace block_ael_training\output;
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
 

  

    public function export_for_template(renderer_base $output) {
        global $USER ,$CFG,$OUTPUT;
     
        $linkViewAll = $CFG->wwwroot.'/blocks/ael_training/view/view.php';
        
       
       

        return [
            'userid' => $USER->id,
            'linkAll' => $linkViewAll
            
        ];
    }


    

   


  
}
