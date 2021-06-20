<?php
require('../../config.php');
require_once($CFG->dirroot . '/totara/core/js/lib/setup.php');

require_login();

$PAGE->set_url(
    new \moodle_url(
        '/blocks/notes/manage_notes.php',
        ['id' => 77]
    )
);

$blockinstanceid = required_param('blockinstanceid', PARAM_INT);
$cm = context_block::instance($blockinstanceid);
$PAGE->set_context($cm);

$s = get_string('notestring', 'block_notes');
$PAGE->set_title($s);
$PAGE->set_heading('Notes Heading');
echo $OUTPUT->header();
print_object($cm);
echo $OUTPUT->footer();
