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
 * Strings for component 'block_notes', language 'en'
 *
 * @package   block_notes
 * @author    Kateryna Degtyariova katerynadegtyariova@catalyst-au.net
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/formslib.php');

class label_form extends moodleform {

    protected $blockinstanceid;

    public function __construct($actionurl)
    {
        parent::__construct($actionurl);
    }

    public function definition() {
        global $CFG;

        $mform = $this->_form;
        $mform->addElement('text', 'name', get_string('labelname', 'block_notes'));
        $mform->setType('name', PARAM_NOTAGS);
        $mform->setDefault('name', 'New Label');
        $this->add_action_buttons(true, get_string('savelabel', 'block_notes'));
    }

    // TODO: add validation
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        return $errors;
    }

    function get_data() {
        $data = parent::get_data();
        return $data;
    }
}

$extraparams = '';

$blockinstanceid = required_param('blockinstanceid', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$labelid = optional_param('labelid', 0, PARAM_INT); // 0 mean create new.

$urlparams = array('labelid' => $labelid);
$urlparams['blockinstanceid'] = $blockinstanceid;
$extraparams = "&blockinstanceid=" . $blockinstanceid;

if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
}
else
{
    $urlparams['returnurl'] = '/blocks/notes/manage_notes.php?' . $extraparams;
}

$managenotes = new moodle_url('/blocks/notes/manage_notes.php', $urlparams);

$baseurl = new moodle_url('/blocks/notes/editlabel.php', $urlparams);
$PAGE->set_url($baseurl);
$blockctx = context_block::instance($blockinstanceid);
$coursectx = $blockctx->get_course_context();
$PAGE->set_context($blockctx);

if ($labelid) {
    $isadding = false;
    $labelrecord = $DB->get_record('block_note_labels', array('id' => $labelid), '*', MUST_EXIST);
} else {
    $isadding = true;
    $labelrecord = new stdClass;
}

$mform = new label_form($PAGE->url);

$labelrecord->blockinstanceid = $blockinstanceid;
$mform->set_data($labelrecord);


if ($mform->is_cancelled()) {
    redirect($managenotes);
} else if ($data = $mform->get_data()) {
    $data->userid = $USER->id;
    if ($isadding) {
        $data->courseid = $coursectx->instanceid;
        $data->timecreated = time();
        $data->timemodified = time();
        $DB->insert_record('block_note_labels', $data);
    } else {
        $data->id = $labelid;
        $data->timemodified = time();
        $DB->update_record('block_note_labels', $data);
    }
    redirect($managenotes);
} else {
    if ($isadding) {
        $strtitle = get_string('addlabel', 'block_notes');
    } else {
        $strtitle = get_string('editlabel', 'block_notes');
    }

    $PAGE->set_title($strtitle);
    $PAGE->set_heading($strtitle);

    $PAGE->navbar->add(get_string('blocks'));
    $PAGE->navbar->add(get_string('pluginname', 'block_notes'));
    $PAGE->navbar->add(get_string('managelabels', 'block_notes'), $managenotes );
    $PAGE->navbar->add($strtitle);

    echo $OUTPUT->header();
    echo $OUTPUT->heading($strtitle, 2);

    $mform->display();

    echo $OUTPUT->footer();
}