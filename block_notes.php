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

$CFG->cachejs = false;

class block_notes extends block_base {
    public function init() {
        global $PAGE, $CFG;
        $PAGE->requires->js_call_amd('block_notes/notes', 'initNote');
        $this->title = get_string('pluginname', 'block_notes');
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
        return true;
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
        global $USER;
        if ($this->content !== null) {
            return $this->content;
        }

        $core_renderer = $this->page->get_renderer('core');
        $this->content = new stdClass;
        $contextdata = array(
            'blockinstanceid' => $this->context->instanceid,
            'userid' => $USER->id
        );
        $this->content->text = $core_renderer->render_from_template('block_notes/crop_tool', $contextdata);

        return $this->content;
    }
}