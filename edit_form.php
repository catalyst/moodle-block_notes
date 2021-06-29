<?php

class block_notes_edit_form extends block_edit_form {

    /**
     * Override this if your block as configurable as rock.
     *
     * @return bool
     */
    protected function has_common_settings() {
        return false;
    }

    protected function specific_definition($mform) {

        // Section header title according to language file.
        //$mform->addElement('header', 'config_header', get_string('blocksettings', 'block'));

        // A sample string variable with a default value.
        /*$mform->addElement('text', 'config_text', get_string('notestring', 'block_notes'));
        $mform->setDefault('config_text', '');
        $mform->setType('config_text', PARAM_RAW);*/

    }
}