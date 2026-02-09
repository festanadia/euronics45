<?php

class block_ael_bannermessage_edit_form extends block_edit_form {
        
    protected function specific_definition($mform) {
        
    
        $mform->addElement('header', 'config_header', get_string('blocksettings', 'block_ael_bannermessage'));
       
        $mform->addElement('text', 'config_titlecard', get_string('titleCard', 'block_ael_bannermessage'),'maxlength="100"');
        $mform->setDefault('config_titlecard', get_string('titleCardDefault', 'block_ael_bannermessage'));
        $mform->addRule('config_titlecard', get_string('required'), 'required', null, 'server');
        $mform->addRule('config_titlecard', get_string('maximumchars', '', 100), 'maxlength', 100, 'server');
        $mform->setType('config_titlecard', PARAM_CLEANHTML);

        $mform->addElement('textarea', 'config_textcard', get_string('desccard', 'block_ael_bannermessage'));
        $mform->setType('config_textcard', PARAM_RAW);
        
        $mform->addElement('text', 'config_ctabutton', get_string('labelCta', 'block_ael_bannermessage'));
        $mform->setDefault('config_ctabutton', get_string('labelCtaDefault', 'block_ael_bannermessage'));
        $mform->setType('config_ctabutton', PARAM_TEXT);      

        $mform->addElement('text', 'config_ctabuttonurl', get_string('urlCta', 'block_ael_bannermessage'));
        $mform->setType('config_ctabuttonurl', PARAM_URL);   
        


        $filemanageroptions = array(
            'accepted_types' => array('.jpeg', '.png'),
            'maxbytes' => 0,
            'maxfiles' => 1,
            'subdirs' => 0
        );
        $mform->addElement('filemanager', 'config_image', get_string('image', 'block_ael_bannermessage'),null,$filemanageroptions);
       
        

    }

    function set_data($defaults) {
       

        if (empty($entry->id)) {
            $entry = new stdClass;
            $entry->id = null;
        }

        $draftitemid = file_get_submitted_draft_itemid('config_image');

        file_prepare_draft_area($draftitemid, $this->block->context->id, 'block_ael_bannermessage', 'content', 0,
                    array('subdirs'=>true));

        $entry->attachments = $draftitemid;

        parent::set_data($defaults);	    

        if ($data = parent::get_data()) {
           
            file_save_draft_area_files($data->config_image, $this->block->context->id, 'block_ael_bannermessage', 'content', 0, 
                array('subdirs' => true));
        }

    }
}