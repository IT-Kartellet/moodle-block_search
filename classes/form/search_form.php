<?php

namespace block_search\form;

require_once("$CFG->dirroot/$CFG->admin/tool/coursesearch/locallib.php");
require_once($CFG->libdir . '/formslib.php');


class search_form extends \moodleform {
    function definition() {
        global $PAGE;
        $mform =& $this->_form;
        $mform->addElement('text', 'search', '', array('size' => '50', 'maxlength' => '100', 'placeholder' => 'Search'));
        $mform->setType('search', PARAM_TEXT);

        $ob = new \tool_coursesearch_locallib();

        $PAGE->requires->js_init_call('M.tool_coursesearch.auto', $ob->tool_coursesearch_autosuggestparams());
        $PAGE->requires->js_init_call('M.tool_coursesearch.sort');
    }
}