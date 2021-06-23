<?php

namespace block_notes;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/files/externallib.php');

class external extends \external_api {

    /**
     * create_labels function will create a label that can be used for labelling notes
     * @return \external_function_parameters
     */
    public static function create_label_parameters() {
        return new \external_function_parameters(
            [
                'userid' => new \external_value(PARAM_INT, 'id of the user creating a label'),
                'courseid' => new \external_value(PARAM_INT, 'id of the course where a label is created'),
                'name' => new \external_value(PARAM_TEXT, 'The name of the label to be created')
            ]
        );
    }

    /**
     * Returns id, userid, courseid and name of the newly created label
     * @return \external_function_parameters
     */
    public static function create_label_returns() {
        return new \external_function_parameters(
            [
                'id' => new \external_value(PARAM_INT, 'Label id'),
                'userid' => new \external_value(PARAM_INT, 'id of the user creating a label'),
                'courseid' => new \external_value(PARAM_INT, 'id of the course where a label is created'),
                'name' => new \external_value(PARAM_TEXT, 'The name of the label to be created')
            ]
        );
    }

    /**
     * @param $userid id of the user creating a label
     * @param $courseid id of the user creating a label
     * @param $name name of the label
     * @return \stdClass
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function create_label($userid, id $courseid, $name) {
        self::validate_parameters(self::create_label_parameters(), [
            'userid' => $userid,
            'courseid' => $courseid,
            'name' => $name
        ]);
        global $DB, $USER;
        if ($DB->get_record('block_note_labels', ['userid' => $userid, 'name' => $name])) {
            throw new invalid_parameter_exception('Lable with the same name already exists for the user');
        }

        $label = new \stdClass();
        $label->userid = $userid;
        $label->courseid = $courseid;
        $label->name = $name;
        $label->timecreated = time();
        $label->timemodified = time();
        $label->id = $DB->insert_record('block_note_labels', $label);
        return $label;
    }
}

class upload extends \core_files_external {

    /**
     * Returns description of note upload parameters
     *
     * @return \external_function_parameters
     */
    public static function upload_parameters() {
        return new \external_function_parameters(
            array(
                'contextid' => new \external_value(PARAM_INT, 'context id', VALUE_DEFAULT, null),
                'component' => new \external_value(PARAM_COMPONENT, 'component'),
                'filearea'  => new \external_value(PARAM_AREA, 'file area'),
                'itemid'    => new \external_value(PARAM_INT, 'associated id'),
                'filepath'  => new \external_value(PARAM_PATH, 'file path'),
                'filename'  => new \external_value(PARAM_FILE, 'file name'),
                'userid'  => new \external_value(PARAM_INT, 'file name'),
                'filecontent' => new \external_value(PARAM_TEXT, 'file content'),
                'contextlevel' => new \external_value(PARAM_ALPHA, 'The context level to put the file in,
                        (block, course, coursecat, system, user, module)', VALUE_DEFAULT, null),
                'instanceid' => new \external_value(PARAM_INT, 'The Instance id of item associated
                         with the context level', VALUE_DEFAULT, null),
                'labelid' => new \external_value(PARAM_INT, 'The Label id associated with the note'),
                'noteurl'  => new \external_value(PARAM_LOCALURL, 'Note URL'),
                'notedescription'  => new \external_value(PARAM_TEXT, 'Note Description'),
            )
        );
    }

    /**
     * Uploading a note.
     *
     * @param int $contextid context id
     * @param string $component component
     * @param string $filearea file area
     * @param int $itemid item id
     * @param string $filepath file path
     * @param string $filename file name
     * @param int $userid user id
     * @param string $filecontent file content
     * @param string $contextlevel Context level (block, course, coursecat, system, user or module)
     * @param int $instanceid Instance id of the item associated with the context level
     * @param int $labelid Label id assiciated with the note
     * @param string $noteurl Local URL of a note
     * @param string $notedescription Text description of a note
     * @return array
     * @throws \moodle_exception
     */
    public static function upload($contextid, $component, $filearea, $itemid, $filepath, $filename, $userid,
                                  $filecontent, $contextlevel, $instanceid, $labelid, $noteurl, $notedescription) {
        global $DB, $USER;

        $fileinfo = self::validate_parameters(self::upload_parameters(), array(
            'contextid' => $contextid, 'component' => $component, 'filearea' => $filearea, 'itemid' => $itemid,
            'filepath' => $filepath, 'filename' => $filename, 'userid' => $userid, 'filecontent' => $filecontent,
            'contextlevel' => $contextlevel, 'instanceid' => $instanceid, 'labelid' => $labelid,
            'noteurl' => $noteurl, 'notedescription' => $notedescription));

        // Get and validate context.
        $context = self::get_context_from_params($fileinfo);
        self::validate_context($context);

        // Decode the content
        $filecontent = str_replace('data:image/png;base64,', '', $filecontent);
        $decoded = base64_decode($filecontent);

        $fs = get_file_storage();
        $fs->create_file_from_string($fileinfo, $decoded);

        // Upload the file.
        //$result = parent::upload($contextid, $component, $filearea, $itemid, $filepath, $filename, $decoded, $contextlevel, $instanceid);

        $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);

        global $DB, $USER;
        if (!$DB->get_record('block_note_labels', ['id' => $labelid])) {
            throw new invalid_parameter_exception('Lable does not exist');
        }

        $note = new \stdClass();
        $note->id = null;
        $note->fileid = $file->get_id();
        $note->labelid = $labelid;
        $note->description = $notedescription;
        $note->url = $noteurl;
        $note->timecreated = time();
        $note->timemodified = time();
        $note->id = $DB->insert_record('block_notes', $note);
        return $note;
    }

    /**
     * Returns id, fileid, labelid, description and url of the newly created note
     * @return \external_function_parameters
     */
    public static function upload_returns() {
        return new \external_function_parameters(
            [
                'id' => new \external_value(PARAM_INT, 'Note id'),
                'fileid' => new \external_value(PARAM_INT, 'id of the screenshot file'),
                'labelid' => new \external_value(PARAM_INT, 'id of the label for the note'),
                'description' => new \external_value(PARAM_TEXT, 'The note text description'),
                'url' => new \external_value(PARAM_TEXT, 'The URL from the page note was taken from')
            ]
        );
    }
}


