<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Script to let users manage their notes and labels.
 *
 * @package   block_notes
 * @author    Kateryna Degtyariova katerynadegtyariova@catalyst-au.net
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

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
        return new \external_single_structure(
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
     * @param $courseid course id
     * @param $name name of the label
     * @return \stdClass
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function create_label($userid, $courseid, $name) {
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

    /**
     * get_labels function will get the list of labels created by a user for a course
     * @return \external_function_parameters
     */
    public static function get_labels_parameters() {
        return new \external_function_parameters(
            [
                'courseid' => new \external_value(PARAM_INT, 'id of the course where labels are created')
            ]
        );
    }

    /**
     * Returns the list of labels
     * @return \external_function_parameters
     */
    public static function get_labels_returns() {
        return new \external_multiple_structure(
            new  \external_single_structure(
                [
                    'id' => new \external_value(PARAM_INT, 'Label id'),
                    'userid' => new \external_value(PARAM_INT, 'id of the user creating a label'),
                    'courseid' => new \external_value(PARAM_INT, 'id of the course where a label is created'),
                    'name' => new \external_value(PARAM_TEXT, 'The name of the label to be created')
                ]
            )
        );
    }

    /**
     * @param $userid id of the user
     * @param $courseid id of the user creating a label
     * @return \stdClass
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    public static function get_labels($courseid) {
        self::validate_parameters(self::get_labels_parameters(), [
            'courseid' => $courseid
        ]);
        global $DB, $USER;
        $labels = array();
        $records = $DB->get_records('block_note_labels', ['userid' => $USER->id, 'courseid' => $courseid],'timemodified DESC', 'id,userid,courseid,name');
        foreach ($records as $rec) {
            $label = [
                'id' => $rec->id,
                'userid' => $rec->userid,
                'courseid' => $rec->courseid,
                'name' => $rec->name
            ];
            $labels[] = $label;
        }
        return $labels;
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
                'filename'  => new \external_value(PARAM_FILE, 'file name'),
                'filecontent' => new \external_value(PARAM_TEXT, 'file content'),
                'instanceid' => new \external_value(PARAM_INT,
                    'The Instance id of item associated with the context level', VALUE_DEFAULT, null),
                'labelid' => new \external_value(PARAM_INT,
                    'The Label id associated with the note'),
                'newlabelname' => new \external_value(PARAM_TEXT,
                    'If a new label needs to be created this would specify the name'),
                'noteurl'  => new \external_value(PARAM_LOCALURL, 'Note URL'),
                'notedescription'  => new \external_value(PARAM_TEXT, 'Note Description'),
            )
        );
    }

    /**
     * Uploading a note.
     *
     * @param int $contextid context id
     * @param string $filename file name
     * @param string $filecontent file content
     * @param int $instanceid Instance id of the item associated with the context level
     * @param int $labelid Label id assiciated with the note
     * @param string $newlabelname If a new label needs to be created this would specify the name
     * @param string $noteurl Local URL of a note
     * @param string $notedescription Text description of a note
     * @return array
     * @throws \moodle_exception
     */
    public static function upload($contextid, $filename, $filecontent, $instanceid, $labelid, $newlabelname, $noteurl, $notedescription) {
        global $DB, $USER, $COURSE;
        $component = 'block_notes';
        $filearea = 'note';
        $itemid = 0;
        $contextlevel = 'block';
        $filepath = '/';

        $fileinfo = self::validate_parameters(self::upload_parameters(), array(
            'contextid' => $contextid, 'filename' => $filename, 'filecontent' => $filecontent,
            'instanceid' => $instanceid, 'labelid' => $labelid, 'newlabelname' => $newlabelname,
            'noteurl' => $noteurl, 'notedescription' => $notedescription));

        $fileinfo['component'] = $component;
        $fileinfo['filearea'] = $filearea;
        $fileinfo['itemid'] = $itemid;
        $fileinfo['filepath'] = $filepath;
        $fileinfo['contextlevel'] = $contextlevel;

        // Get and validate context.
        $context = self::get_context_from_params($fileinfo);
        self::validate_context($context);

        if (strlen($newlabelname) > 0) {
            $courseid = $COURSE->id;

            $label = new \stdClass();
            $label->userid = $USER->id;
            $label->courseid = $courseid;
            $label->name = $newlabelname;
            $label->timecreated = time();
            $label->timemodified = time();
            $labelid = $DB->insert_record('block_note_labels', $label);
        }

        if (!$DB->get_record('block_note_labels', ['id' => $labelid, 'userid' => $USER->id])) {
            throw new invalid_parameter_exception('Label does not exist');
        }

        // Decode the content
        $filecontent = str_replace('data:image/png;base64,', '', $filecontent);
        $decoded = base64_decode($filecontent);

        $fs = get_file_storage();
        // Need to add userid before the file is created
        $fileinfo['userid'] = $USER->id;
        $fs->create_file_from_string($fileinfo, $decoded);
        $file = $fs->get_file($contextid, $component, $filearea, $itemid, $filepath, $filename);

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
        return new \external_single_structure(
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


