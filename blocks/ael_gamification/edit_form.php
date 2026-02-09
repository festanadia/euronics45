<?php

class block_ael_gamification_edit_form extends block_edit_form {
        
    protected function specific_definition($mform) {
        
    
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block_ael_gamification'));

       
        $mform->addElement('text', 'config_titlecard', get_string('titleCard', 'block_ael_gamification'));
        $mform->setDefault('config_titlecard', get_string('titleCardDefault', 'block_ael_gamification'));
        $mform->setType('config_titlecard', PARAM_RAW);        

    }
}