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

require('../../config.php');

$blockinstanceid = optional_param('blockinstanceid', 0, PARAM_INT);
$deletenoteid = optional_param('deletenoteid', 0, PARAM_INT);
$deletelabelid = optional_param('deletelabelid', 0, PARAM_INT);

$urlparams = array();
$extraparams = '';

if ($blockinstanceid) {
    $urlparams['blockinstanceid'] = $blockinstanceid;
    $extraparams = "&blockinstanceid=" . $blockinstanceid;
}

$baseurl = new moodle_url('/blocks/notes/manage_notes.php', $urlparams);
$PAGE->set_url($baseurl);
require_login();
if (isguestuser() || !isloggedin())
{
    throw new moodle_exception('noguestallowed', 'block_notes');
}

// Process note deleting.
if ($deletenoteid && confirm_sesskey()) {
    \block_notes\note::delete($deletenoteid);
    redirect($PAGE->url, get_string('notedeleted', 'block_notes'));
}

// Process label deleting
if ($deletelabelid && confirm_sesskey()) {
    \block_notes\label::delete($deletelabelid);
    redirect($PAGE->url, get_string('labeldeleted', 'block_notes'));
}

// If block instance is known we can set a context, otherwise - no context
if ($blockinstanceid) {
    $blockctx = context_block::instance($blockinstanceid);
    $coursectx = $blockctx->get_course_context();
    $PAGE->set_context($blockctx);
}

$s = get_string('explorebuttontip', 'block_notes');
$PAGE->set_title($s);
$PAGE->set_heading('Notes Heading');
echo $OUTPUT->header();
$newlabelurl = new moodle_url('/blocks/notes/editlabel.php?'. $extraparams);
if ($blockinstanceid > 0) {
    echo '<div><a href="' . $newlabelurl . '">' . get_string('addlabel', 'block_notes') . '</a></div>';
}

$sql = "SELECT CONCAT(n.id, '_', lb.id) AS uniquestr, n.id, n.description, n.url, n.fileid, lb.id AS labelid, lb.name, lb.timemodified AS labeltimemodified,
        n.timemodified AS ntimemodified
        FROM {block_note_labels} lb
        LEFT JOIN {block_notes} n ON n.labelid = lb.id
        WHERE userid = :userid
        ORDER BY labeltimemodified DESC, ntimemodified DESC
";

$records = $DB->get_records_sql($sql, ['userid' => $USER->id]);

/*
 * Set up the flexible table to display all labels
 */
$table = new flexible_table('labels-view');
$table->define_columns(array('label', 'created', 'actions'));
$table->define_headers(array(get_string('label', 'block_notes'),
                             get_string('modified', 'block_notes'),
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

$renderer = $PAGE->get_renderer('core');
$fs = get_file_storage();
$sorted = array();

/*
 * Transform the array from flat result rows into the array containing
 * 'labelid' as key and containing a nested array 'notes' containing data
 * for notes that are marked with this label.
 */
foreach ($records as $rec) {
    $sorted[$rec->labelid]['name'] = $rec->name;
    $sorted[$rec->labelid]['labeltimemodified'] = $rec->labeltimemodified;
    if (isset($rec->id)) {
        $sorted[$rec->labelid]['notes'][] = [
            'id' => $rec->id,
            'description' => $rec->description,
            'url' => $rec->url,
            'fileid' => $rec->fileid
        ];
    }
}

$deleteicon = new pix_icon('t/delete', get_string('delete'));
$editicon = new pix_icon('t/edit', get_string('edit'));
foreach ($sorted as $labelid => $record) {
    $regioncontent = "";
    if (isset($record['notes'])) {
        // Generate the notes content for the collapsible region.
        $regioncontent = print_collapsible_region_start('', 'note-label-id'.$labelid,
            get_string('notes', 'block_notes'), '', false, true);
        foreach ($record['notes'] as $note) {
            $file = $fs->get_file_by_id($note['fileid']);
            $url = moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    'block_notes',
                    'note',
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename()
            );
            $note['furl'] = $url;

/*          TODO: add editing for notes
            $editurl = new moodle_url('/blocks/notes/edit_notes.php?noteid='. $note['id'] );
            $note['editaction'] = $OUTPUT->action_icon($editurl, $editicon);*/

            $deleteurl = new moodle_url('/blocks/notes/manage_notes.php?deletenoteid='. $note['id'] . '&sesskey=' . sesskey() . $extraparams);
            $note['deleteaction'] = $OUTPUT->action_icon($deleteurl, $deleteicon,
                new confirm_action(get_string('deletenoteconfirm', 'block_notes')));

            $regioncontent .= $renderer->render_from_template('block_notes/note', $note);
        }
        $regioncontent .= print_collapsible_region_end(true);
    }

    $labelinfo = '<div class="title">' . $record['name'] . '</div>'. $regioncontent;
    $labeldate = userdate($record['labeltimemodified'], get_string('strftimerecentfull', 'langconfig'));
    $editurl = new moodle_url('/blocks/notes/editlabel.php?labelid=' . $labelid . $extraparams);
    $editaction = $OUTPUT->action_icon($editurl, $editicon);
    $deleteurl = new moodle_url('/blocks/notes/manage_notes.php?deletelabelid='.
        $labelid . '&sesskey=' . sesskey() . $extraparams);
    $deleteaction = $OUTPUT->action_icon($deleteurl, $deleteicon,
        new confirm_action(get_string('deletelabelconfirm', 'block_notes')));
    $labelicons = $editaction . ' ' . $deleteaction;
    $table->add_data(array($labelinfo, $labeldate, $labelicons));
}
$table->finish_output();
?>
<div onclick="document.getElementById('note_display_note_block').style.display='none'" class="note-display-block-wait" data-html2canvas-ignore id="note_display_note_block">
    <img id="noteimagepreview">
</div>
<?php
echo $OUTPUT->footer();
?>
<script>
    function uncollapseImg(elem) {
        let obj = document.getElementById(elem);
        document.getElementById('noteimagepreview').src = obj.src;
        document.getElementById('note_display_note_block').style.display = 'block';
    }
</script>
