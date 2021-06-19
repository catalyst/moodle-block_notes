<?php

namespace block_notes;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');

class external extends \external_api {

    /**
     * create_labels function will create a label that can be used for labelling notes
     * @return \external_function_parameters
     */
    public static function create_label_parameters() {
        return new \external_function_parameters(
            [
                'userid' => new \external_value(PARAM_INT, 'id of the user creating a label'),
                'name' => new \external_value(PARAM_TEXT, 'The name of the label to be created')
            ]
        );
    }

    /**
     * Returns id, userid and name of the newly created label
     * @return \external_function_parameters
     */
    public static function create_label_returns() {
        return new \external_function_parameters(
            [
                'id' => new \external_value(PARAM_INT, 'Label id'),
                'userid' => new \external_value(PARAM_INT, 'id of the user creating a label'),
                'name' => new \external_value(PARAM_TEXT, 'The name of the label to be created')
            ]
        );
    }

    /**
     * @param $userid id of the user creating a label
     * @param $name name of the label
     * @return \stdClass
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function create_label($userid, $name) {
        self::validate_parameters(self::create_label_parameters(), [
            'userid' => $userid,
            'name' => $name
        ]);
        global $DB, $USER;
        if ($DB->get_record('block_note_labels', ['userid' => $userid, 'name' => $name])) {
            throw new invalid_parameter_exception('Lable with the same name already exists for the user');
        }

        $label = new \stdClass();
        $label->userid = $userid;
        $label->name = $name;
        $label->timecreated = time();
        $label->timemodified = time();
        $label->id = $DB->insert_record('block_note_labels', $label);
        return $label;
    }

}

