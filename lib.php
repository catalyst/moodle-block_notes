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

/**
 * @param $course
 * @param $birecord_or_cm
 * @param context $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 * @param array $options
 * @throws coding_exception
 * @throws dml_exception
 * @throws moodle_exception
 * @throws require_login_exception
 */
function block_notes_pluginfile($course, $birecord_or_cm, context $context, $filearea, $args, $forcedownload, array $options=array()) {
    global $DB, $CFG, $USER;

    $block_notes = block_instance('notes', $birecord_or_cm);
    if (!$block_notes || !$block_notes->user_can_view()) {
        send_file_not_found();
    }

    if ($context->contextlevel != CONTEXT_BLOCK) {
        send_file_not_found();
    }

    $blockinstance = $DB->get_record('block_instances', ['id' => $context->instanceid]);
    if (!$blockinstance) {
        send_file_not_found();
    }

    // Get parent context and see if user have proper permission.
    $parentcontext = $context->get_parent_context();
    if (!$parentcontext) {
        send_file_not_found();
    }

    if ($context->is_user_access_prevented()) {
        send_file_not_found();
    }

    $forcedownload = false;
    if ($parentcontext->contextlevel == CONTEXT_COURSECAT) {
        // Check if category is visible and user can view this category.
        $category = coursecat::get($parentcontext->instanceid);
        if (!$category->is_uservisible()) {
            send_file_not_found();
        }
    } else if ($parentcontext->contextlevel == CONTEXT_USER) {
        // force download on all personal pages including /my/
        // because we do not have reliable way to find out from where this is used
        $forcedownload = true;
        if ($parentcontext->instanceid != $USER->id) {
            if ($blockinstance->pagetypepattern !== 'user-profile') {
                // There is only one page that can be viewed by other users where users can customise blocks,
                // it is their public profile page.
                send_file_not_found();
            }
            if (!user_can_view_profile($parentcontext->instanceid)) {
                send_file_not_found();
            }
        }
    }
    // At this point there is no way to check SYSTEM context, so ignoring it.

    $fs = get_file_storage();

    $filename = array_pop($args);
    $filepath = '/';

    if (!$file = $fs->get_file($context->id, 'block_notes', 'note', 0, $filepath, $filename) or $file->is_directory()) {
        send_file_not_found();
    }

    // NOTE: it would be nice to have file revisions here, for now rely on standard file lifetime,
    //       do not lower it because the files are displayed very often.
    \core\session\manager::write_close();
    send_stored_file($file, null, 0, $forcedownload, $options);
}
