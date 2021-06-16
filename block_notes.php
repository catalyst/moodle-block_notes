<?php
defined('MOODLE_INTERNAL') || die;

$CFG->cachejs = false;

class block_notes extends block_base {
    public function init() {
        global $PAGE, $CFG;
        $PAGE->requires->js_call_amd('block_notes/notes', 'initNote');
    }

    public function hide_header() {
        return true;
    }

    public function get_content() {
        if ($this->content !== null) {
            return $this->content;
        }

        $core_renderer = $this->page->get_renderer('core');
        $this->content = new stdClass;
        $contextdata = array(
            'abc' => 1
        );
        $this->content->text = $core_renderer->render_from_template('block_notes/crop_tool', $contextdata);
        return $this->content;
    }
}