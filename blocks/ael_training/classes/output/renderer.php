<?php

namespace block_ael_training\output;

defined('MOODLE_INTERNAL') || die;

use plugin_renderer_base;


class renderer extends plugin_renderer_base {

    
    protected function render_card(main $main) {
    
        return $this->render_from_template('block_ael_training/main', $main->export_for_template($this));
    }


  
}
