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

        $this->content         =  new stdClass;
        $this->content->text   = '<div class="note-pop-on" id="note_wait_pop_message">Please wait</div><div class="note-button-on" onclick="require([\'block_notes/notes\'], function (M) { M.makeScreenshot();});">
                                        <span class="flex-icon ft-fw ft tfont-var-files_copy" title="Copy content to Notes"></span></div>';

        return $this->content;
    }
}