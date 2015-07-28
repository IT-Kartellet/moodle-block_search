<?php
class block_search extends block_base {
    public function init() {
        $this->title = get_string('search');
    }

    public function get_content() {
        global $CFG;
        if ($this->content === null) {
            $this->content         =  new stdClass;

            $form = new \block_search\form\search_form($CFG->wwwroot . '/blocks/search/results.php');
            $this->content->text   = $form->render();
        }

        return $this->content;
    }
}
