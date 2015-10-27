<?php
require_once('../../config.php');
require_login();

global $PAGE, $OUTPUT, $USER, $DB, $CFG;
$PAGE->set_context(context_system::instance());

$PAGE->set_url('/blocks/search/results.php');
$PAGE->set_pagelayout('search-solar');

require("$CFG->dirroot/admin/tool/coursesearch/coursesearch_resultsui_form.php");
require_once("$CFG->dirroot/$CFG->admin/tool/coursesearch/locallib.php");

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
        $content .= html_writer::tag('i', null, array('class' => 'fa fa-search'));

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
        foreach ($response->docs as $doc) {
            $item = '';

            if (!tool_coursesearch_locallib::tool_coursesearch_can_view($doc)) {
                continue;
            }
            $docs_rendered++;
            switch ($doc->type) {
                case 'course_module':
                    $link = new moodle_url('/mod/' . $doc->modname . '/view.php', array('id' => $doc->modid));

                    break;
                case 'course':
                    $link = new moodle_url('/course/view.php', array('id' => $doc->courseid));

                    break;
                case 'forum_post':
                    $postid = str_replace('forum_post_', '', $doc->id);
                    $link = new moodle_url('/mod/forum/discuss.php', array('d' => $doc->metadata_discussionid), 'p' . $postid);
                    break;
            }

            $fullname = $doc->fullname;
            if (!empty($highlight->{$doc->id}->fullname)) {
                $fullname = $highlight->{$doc->id}->fullname[0];
            }

            $item .= html_writer::tag('h3', html_writer::link($link, $fullname));

            $summary = '';
            if (!empty($doc->content)) {
                if (!empty($highlight->{$doc->id}->content)) {
                    $summary = '... ' . implode(' ... ', $highlight->{$doc->id}->content);
                } else {
                    $summary = mb_strimwidth($doc->content, 0, 600, '...');
                }
            } else if (!empty($highlight->{$doc->id}->summary)) {
                $summary = '... ' . implode(' ... ', $highlight->{$doc->id}->summary);
            } else {
                $summary = $doc->summary;
            }

            $item .= html_writer::tag('p', $summary, array('class' => 'summary'));
            $item = html_writer::tag('div', $item, array('class' => 'search-result'));

            $content .= $item;
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
