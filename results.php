<?php
require_once('../../config.php');
require_login();

global $PAGE, $OUTPUT, $USER, $DB, $CFG;

$PAGE->set_context(context_system::instance());

$PAGE->set_url('/blocks/search/results.php');
$PAGE->set_pagelayout('search-solar');

$PAGE->requires->js(new moodle_url('/blocks/search/js/search.js'));


require("$CFG->dirroot/admin/tool/coursesearch/coursesearch_resultsui_form.php");
require_once("$CFG->dirroot/$CFG->admin/tool/coursesearch/locallib.php");
require_once($CFG->libdir. '/coursecatlib.php');

echo $OUTPUT->header();

$ob = new tool_coursesearch_locallib();
if ($ob->tool_coursesearch_pingsolr()) {
    $PAGE->requires->js_init_call('M.tool_coursesearch.auto', $ob->tool_coursesearch_autosuggestparams());
    $PAGE->requires->js_init_call('M.tool_coursesearch.sort');
    $mform = new coursesearch_resultsui_form(
        new moodle_url("/blocks/search/results.php"), null, 'post', null, array(
        "id" => "searchformui"
    ));

    $mform->display();

    $searchcriteria = array();
    if (!empty(optional_param('search', '', PARAM_TEXT))) {
        $searchcriteria['search'] = optional_param('search', '', PARAM_TEXT);
    }
    if (!empty(optional_param('type', '', PARAM_TEXT))) {
        $searchcriteria['type'] = optional_param('type', '', PARAM_TEXT);
    }
    if (!empty(optional_param('sortmenu', '', PARAM_TEXT))) {
        $searchcriteria['sortmenu'] = optional_param('sortmenu', '', PARAM_TEXT);
    }
    if (!empty(optional_param_array('searchfromtime', '', PARAM_TEXT)) && !empty(optional_param_array('searchfromtime', '', PARAM_TEXT)['enabled'])) {
        $searchcriteria['searchfromtime'] = optional_param_array('searchfromtime', '', PARAM_TEXT);
    }
    if (!empty(optional_param_array('searchtilltime', '', PARAM_TEXT)) && !empty(optional_param_array('searchtilltime', '', PARAM_TEXT)['enabled'])) {
        $searchcriteria['searchtilltime'] = optional_param_array('searchtilltime', '', PARAM_TEXT);
    }

    if (!empty($searchcriteria)) {
        $content = '';
        $displayoptions = array();
        $perpage        = optional_param('perpage', 'all', PARAM_RAW);
        if ($perpage !== 'all') {
            $displayoptions['limit']  = ((int) $perpage <= 0) ? $CFG->coursesperpage : (int) $perpage;
            $page                     = optional_param('page', 0, PARAM_INT);
            $displayoptions['offset'] = $displayoptions['limit'] * $page;
        }

        $ob = new tool_coursesearch_locallib();
        $results    = $ob->tool_coursesearch_search($displayoptions);
        $qtime      = $results->responseHeader->QTime;
        $highlight =  $results->highlighting;
        $response = $results->response;

        $totalcount = $ob->tool_coursesearch_coursecount($response);

        $content .= html_writer::start_div('search-results-header-wrapper');
        $content .= html_writer::img($OUTPUT->pix_url('Icons_Resources', 'theme_learngo'), null);

        if (!$totalcount) {
            if (!empty($searchcriteria['search'])) {
                $content .= html_writer::tag('h2', get_string('nocoursesfound', '', $searchcriteria['search']), array('class' => 'search-results-header'));
            } else {
                $content .= html_writer::tag('h2', get_string('novalidcourses'), array('class' => 'search-results-header'));
            }
        } else {
            $content .= html_writer::tag('h2', get_string('searchresults'), array('class' => 'search-results-header'));
        }

        $content .= html_writer::end_div();

        $content .= html_writer::start_div('search-results');
        $docs_rendered = 0;

        $courserenderer = $PAGE->get_renderer('core', 'course');

        $course_ids = array();

        foreach ($response->docs as $doc) {

            $item = '';

            if (!tool_coursesearch_locallib::tool_coursesearch_can_view($doc)) {
                continue;
            }

            $docs_rendered++;

            switch ($doc->type) {
                case 'course':
                    $link = new moodle_url('/course/view.php', array('id' => $doc->courseid));
                    $course_ids[$doc->courseid] = $doc;
                    break;
                /*
                case 'course_module':
                    $link = new moodle_url('/mod/' . $doc->modname . '/view.php', array('id' => $doc->modid));
                    break;

                case 'forum_post':
                    $postid = str_replace('forum_post_', '', $doc->id);
                    $link = new moodle_url('/mod/forum/discuss.php', array('d' => $doc->metadata_discussionid), 'p' . $postid);
                    break;
                */
            }
        }

        if(!empty($course_ids)){
            $sql_ids = implode(',', array_keys($course_ids));
            $result = $DB->get_records_sql("SELECT * FROM {course} WHERE id IN($sql_ids)");

            $content .= $courserenderer->courses_list($result);
        }

        $content .= html_writer::end_div('search-results');

        if (isset($results->spellcheck->suggestions->collation)) {
            $didyoumean = $results->spellcheck->suggestions->collation->collationQuery;

            echo html_writer::tag('h3', get_string('didyoumean', 'tool_coursesearch') . html_writer::link(
                    new moodle_url('results.php?search=' . rawurlencode($didyoumean)), $didyoumean) . '?');
        }

        /*
        if ($docs_rendered < $totalcount) {
            $content .= $OUTPUT->paging_bar($totalcount, $page, $perpage, new moodle_url('/blocks/search/results.php', $searchcriteria));
        }
        */
        echo $content;
    }
} else {
    echo "SolR won't talk to me :(";
}

echo $OUTPUT->footer();
