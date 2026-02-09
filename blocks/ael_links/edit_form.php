<?php

class block_ael_links_edit_form extends block_edit_form {
        
    protected function specific_definition($mform) {
        
    
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block_ael_links'));
       
        $mform->addElement('text', 'config_titleCard', get_string('titleSetting', 'block_ael_links'));
        
        $mform->addElement('text', 'config_typeEnrol', get_string('typeEnrol', 'block_ael_links'));

        $mform->setType('config_typeEnrol', PARAM_TEXT);  

        $mform->addElement('text', 'config_customfield', get_string('customfield', 'block_ael_links'));
        $mform->setType('config_custofield', PARAM_TEXT);  


        $mform->addElement('text', 'config_customfieldvalue', get_string('customfieldvalue', 'block_ael_links'));
        $mform->setType('config_customfieldvalue', PARAM_TEXT);  
        


    }

   
}