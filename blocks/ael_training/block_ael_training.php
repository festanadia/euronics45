<?php
defined('MOODLE_INTERNAL') || die();

class block_ael_training extends block_base {




    /**
     * Initialize class member variables
     */
    public function init() {
        $this->title = '';
    
    }

    /**
     * Returns the contents.
     *
     * @return stdClass contents of block
     */
    public function get_content() {
        if (isset($this->content)) {
            return $this->content;
        }
        
        $renderable = new block_ael_training\output\main();
        $renderer = $this->page->get_renderer('block_ael_training');
$this->content = new stdClass();           
        $this->content->text = $renderer->render($renderable);
        $this->content->footer = '';


        return $this->content;
    }

    /**
     * Locations where block can be displayed.
     *
     * @return array
     */
    public function applicable_formats() {
        return array('my' => true);
    }

    /**
     * Allow the block to have a configuration page
     *
     * @return boolean
     */
    public function has_config() {
        return false;
    }


    





    
    
}
