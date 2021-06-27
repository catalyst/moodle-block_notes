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
class label
{
    static function get_from_db($labelid)
    {
        global $DB, $USER;
        $label = $DB->get_record('block_note_labels', array('id' => $labelid, 'userid' => $USER->id));
        if (!$label) {
            throw new \invalid_parameter_exception(get_string('labelnotfound', 'block_notes'));
        }
        return $label;
    }

    static function delete($labelid)
    {
        global $DB, $USER;
        $label = self::get_from_db($labelid);
        $notes = \block_notes\note::get_by_labelid($label->id, false);
        $transaction = $DB->start_delegated_transaction();
        try {
            if ($notes) {
                foreach ($notes as $note) {
                    \block_notes\note::delete($note->id, false);
                }
            }
            $DB->delete_records('block_note_labels', ['id' => $label->id, 'userid' => $USER->id]);
            $transaction->allow_commit();
        } catch (\Exception $e) {
            $transaction->rollback($e);
        }
    }

    static function delete_course_labels($courseid)
    {
        global $DB;
        $DB->delete_records_select('block_notes', ' labelid IN (SELECT id FROM {block_note_labels} WHERE courseid = :courseid)', ['courseid' => $courseid]);
        $DB->delete_records('block_note_labels', ['courseid' => $courseid]);
    }
}