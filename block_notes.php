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
 * Class block_notes
 *
 * @package   block_notes
 * @author    Kateryna Degtyariova katerynadegtyariova@catalyst-au.net
 * @copyright 2021 Catalyst IT
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

//$CFG->cachejs = false;

class block_notes extends block_base {
    public function init() {
        global $PAGE, $CFG;
        $this->title = get_string('pluginname', 'block_notes');
    }

    /**
     * Gets the javascript that is required for the block to work properly
     */
    public function get_required_javascript()
    {
        parent::get_required_javascript();
        $this->page->requires->js_call_amd('block_notes/notes', 'initNote');
    }

    /**
     * Returns the block name, as present in the class name,
     * the database, the block directory, etc etc.
     *
     * @return string
     */
    function name() {
        return "notes";
    }

    public function hide_header() {
        return false;
    }

    public function has_config() {
        return true;
    }

    /**
     * Override parent: Prevent the block from being dockable.
     *
     * @return bool
     */
    public function instance_can_be_docked() {
        return false;
    }

    /**
     * Override parent: Prevent the block from hiding.
     *
     * @return bool
     */
    public function instance_can_be_hidden() {
        return false;
    }

    /**
     * Override parent: the block will not be collapsible.
     *
     * @return bool
     */
    public function instance_can_be_collapsed() {
        return true;
    }

    /**
     * Override parent: disable the border
     *
     * @return bool
     */
    public function display_with_border(): bool {
        return false;
    }

    /**
     * Override parent: Do not display with header
     *
     * @since Totara 12 (Totara only method)
     *
     * @return bool
     */
    public function display_with_header(): bool {
        return false;
    }

    /**
     * Override parent: Do not allow block hiding
     *
     * @since Totara 12 (Totara only method)
     *
     * @return bool
     */
    public function allow_block_hiding() {
        return false;
    }

    /**
     * Get default common configuration values,
     * These will be used as initial values for all the new blocks.
     *
     * @return array
     */
    private function get_default_common_config_values() {
        return [
            'title' => null,
            'override_title' => false,
            'enable_hiding' => false,
            'enable_docking' => false,
            'show_header' => false,
            'show_border' => false
        ];
    }

    public function get_content() {
        global $USER, $PAGE;
        if (!isloggedin() || isguestuser()) {
            return null;
        }

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        try {
            $coursectx = $this->context->get_course_context();
        }
        catch (\coding_exception $ex) {
            return "The Notes block cannot be used outside the course.";
        }


        $core_renderer = $this->page->get_renderer('core');
        $contextdata = array(
            'contextid' => $this->context->id,
            'blockinstanceid' => $this->context->instanceid,
            'courseid' => $coursectx->instanceid,
            'userid' => $USER->id
        );
        $this->content->text = $core_renderer->render_from_template('block_notes/crop_tool', $contextdata);

        return $this->content;
    }

    function instance_delete() {
        global $DB;
        try {
            /* TODO: handle instance deleting. Now we are not deleting the course notes when instance is removed.
            $coursectx = $this->context->get_course_context();
            \block_notes\label::delete_course_labels($coursectx->instanceid);
            $fs = get_file_storage();
            $fs->delete_area_files($this->context->id, 'block_notes');*/
        } catch(Exception $e) {
            // TODO: processing exceptions
        }

        return true;
    }

}