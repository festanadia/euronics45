<?php

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_heading('block_ael_gamification_settings', '',
        get_string('blocksettings', 'block_ael_gamification')));

    $settings->add(new admin_setting_configcheckbox('block_ael_gamification/enabled_custom_avatar',
        get_string('enabled_custom_avatar', 'block_ael_gamification'), '', 1));

    
}