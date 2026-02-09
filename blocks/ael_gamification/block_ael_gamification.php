<?php
defined('MOODLE_INTERNAL') || die();

class block_ael_gamification extends block_base {




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
       
        //die($this->config);
        $renderable = new block_ael_gamification\output\main();
        $renderable->setConfig($this->config);
        $renderer = $this->page->get_renderer('block_ael_gamification');
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
        return true;
    }


    public function get_config_for_external() {
        // Return all settings for all users since it is safe (no private keys, etc..).
        $configs = get_config('block_ael_gamification');

        return (object) [
            'instance' => new stdClass(),
            'plugin' => $configs,
        ];
    }





    
    
}
