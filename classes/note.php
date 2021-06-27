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
class note
{
    static function get_by_id($noteid) {
        global $DB, $USER;
        $sql = "SELECT n.* FROM {block_notes} AS n 
        JOIN {block_note_labels} AS lb ON n.labelid = lb.id
        WHERE lb.userid = :userid AND n.id = :noteid";
        $note = $DB->get_record_sql($sql, ['userid' => $USER->id, 'noteid' => $noteid]);
        if (!$note) {
            throw new \invalid_parameter_exception(get_string('notenotfound', 'block_notes'));
        }
        return $note;
    }

    /**
     * @param $labelid id of label where notes belong to
     * @return mixed array of note records
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \invalid_parameter_exception
     */
    static function get_by_labelid($labelid, $exceptiononerror = false) {
        global $DB, $USER;
        $sql = "SELECT n.* FROM {block_notes} AS n 
        JOIN {block_note_labels} AS lb ON n.labelid = lb.id
        WHERE lb.userid = :userid AND lb.id = :labelid";
        $notes = $DB->get_records_sql($sql, ['userid' => $USER->id, 'labelid' => $labelid]);
        if ($exceptiononerror && !$notes) {
            throw new \invalid_parameter_exception(get_string('notenotfound', 'block_notes'));
        }
        return $notes;
    }

    static function delete($noteid, $transact = true) {
        global $DB, $USER;
        $note = self::get_by_id($noteid);
        $fs = get_file_storage();

        if ($transact) {
            $transaction = $DB->start_delegated_transaction();
        }
        try {
            $DB->delete_records('block_notes', ['id' => $note->id]);
            $fileid = $note->fileid;
            $file = $fs->get_file_by_id($fileid);
            $file->delete();
            if ($transact) {
                $transaction->allow_commit();
            }
        } catch (\Exception $e) {
            if ($transact) {
                $transaction->rollback($e);
            }
        }
    }
}