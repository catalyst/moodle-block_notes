<?php
require('../../config.php');

require_login();

$extraparams = '';
$blockinstanceid = required_param('blockinstanceid', PARAM_INT);
$urlparams['blockinstanceid'] = $blockinstanceid;
$baseurl = new moodle_url('/blocks/notes/manage_notes.php', $urlparams);

$block_ctx = context_block::instance($blockinstanceid);
$course_ctx = $block_ctx->get_course_context();
$PAGE->set_context($block_ctx);
$PAGE->set_url($baseurl);

$s = get_string('notestring', 'block_notes');
$PAGE->set_title($s);
$PAGE->set_heading('Notes Heading');
echo $OUTPUT->header();

$params = ['userid' => $USER->id, 'courseid' => $course_ctx->instanceid];

$sql = "SELECT n.id, n.description, n.url, lb.id AS labelid, lb.name, lb.timemodified AS labeltimemodified
        FROM {block_note_labels} lb
        LEFT JOIN {block_notes} n ON n.labelid = lb.id
        WHERE userid = :userid AND courseid = :courseid
        ORDER BY lb.timemodified DESC
";

$records = $DB->get_records_sql($sql, $params);

$table = new flexible_table('labels-view');

$table->define_columns(array('label', 'created', 'actions'));
$table->define_headers(array(get_string('label', 'block_notes'),
                             get_string('created', 'block_notes'),
                             get_string('actions', 'moodle')));
$table->define_baseurl($baseurl);

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'id');
$table->set_attribute('class', 'generaltable generalbox');

$table->set_control_variables(array(
    TABLE_VAR_SORT    => 'ssort',
    TABLE_VAR_HIDE    => 'shide',
    TABLE_VAR_SHOW    => 'sshow',
    TABLE_VAR_IFIRST  => 'sifirst',
    TABLE_VAR_ILAST   => 'silast',
    TABLE_VAR_PAGE    => 'spage'
));

$table->sortable(true);
$table->setup();
$core_renderer = $PAGE->get_renderer('core');
$sorted_records = array();

foreach ($records as $rec) {
    $sorted_records[$rec->labelid]['name'] = $rec->name;
    $sorted_records[$rec->labelid]['labeltimemodified'] = $rec->labeltimemodified;
    if (isset($rec->id)) {
        $sorted_records[$rec->labelid]['notes'][] = [
            'id' => $rec->id,
            'description' => $rec->description,
            'url' => $rec->url
        ];
    }
}

//print_object($sorted_records);

foreach ($sorted_records as $labelid => $record) {
    $ncontent = "";
    if (isset($record['notes'])) {
        $region_content = '';
        foreach($record['notes'] as $note) {
            $region_content .= $core_renderer->render_from_template('block_notes/note', $note);
        }

        $ncontent = print_collapsible_region($region_content, '', 'note-label-id'.$labelid,
            get_string('notes', 'block_notes'), '', true, true);
    }

    $labelinfo = '<div class="title">' . $record['name'] . '</div>'. $ncontent;

    $labeldate = strftime(get_string('strftimerecentfull', 'langconfig'), $record['labeltimemodified']/1000);

    //echo $core_renderer->render_from_template('block_notes/label', $label);
    $editurl = new moodle_url('/blocks/notes/edit_note.php?id=' . $labelid . $extraparams);
    $editaction = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));

    $deleteurl = new moodle_url('/blocks/notes/manage_notes.php?deletelabelid=' . $labelid . '&sesskey=' . sesskey() . $extraparams);
    $deleteicon = new pix_icon('t/delete', get_string('delete'));
    $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon, new confirm_action(get_string('deletelabelconfirm', 'block_notes')));

    $labelicons = $editaction . ' ' . $deleteaction;

    $table->add_data(array($labelinfo, $labeldate, $labelicons));

}
$table->finish_output();
echo $OUTPUT->footer();
