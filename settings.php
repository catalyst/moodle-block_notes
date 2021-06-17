<?php
defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configtext('block_notes_storagelimit', get_string('storagelimit', 'block_notes'),
        get_string('configstoragelimit', 'block_notes'), 25, PARAM_INT));
}
