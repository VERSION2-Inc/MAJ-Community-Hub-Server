<?php // $Id: results.php 148 2012-12-01 08:37:33Z malu $

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/classes/competition.php';

if (false) {
    $OUTPUT = new core_renderer;
    $PAGE   = new moodle_page;
}

$competition = majhub\voting\competition::from_id(required_param('id', PARAM_INT), MUST_EXIST);

$PAGE->set_url('/blocks/majhub_voting/results.php', array('id' => $competition->id));
$PAGE->set_context(context_system::instance());
$PAGE->set_cacheable(false);

require_login();

$PAGE->set_pagelayout('frontpage');
$PAGE->set_pagetype('site-index');
$PAGE->requires->css('/blocks/majhub_voting/results.css');

$strpagetitle = get_string('competitionresults', 'block_majhub_voting');
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpagetitle);

echo $OUTPUT->header();
echo $OUTPUT->heading($strpagetitle);

$dateformat = get_string('strftimedaydate', 'langconfig');
echo html_writer::start_tag('fieldset', array('class' => 'competition'));
echo html_writer::tag('legend', get_string('competition', 'block_majhub_voting'));
echo html_writer::start_tag('dl');
echo html_writer::tag('dt', get_string('title', 'block_majhub_voting')),
    html_writer::tag('dd', $competition->title);
echo html_writer::tag('dt', get_string('description', 'block_majhub_voting')),
    html_writer::tag('dd', $competition->description);
echo html_writer::tag('dt', get_string('startdate', 'block_majhub_voting')),
    html_writer::tag('dd', userdate($competition->timestart, $dateformat));
echo html_writer::tag('dt', get_string('enddate', 'block_majhub_voting')),
    html_writer::tag('dd', userdate($competition->timeend, $dateformat));
echo html_writer::end_tag('dl');
echo html_writer::end_tag('fieldset');

echo html_writer::start_tag('table', array('class' => 'competition'));
echo html_writer::start_tag('tr');
echo html_writer::tag('th', get_string('rank', 'block_majhub_voting'));
echo html_writer::tag('th', get_string('candidate', 'block_majhub_voting'));
echo html_writer::tag('th', get_string('numberofvotes', 'block_majhub_voting'));
echo html_writer::end_tag('tr');
list ($rank, $tie, $previous, $oddeven) = array(0, 0, -1, 1);
foreach ($competition->candidates as $candidate) {
    if ($candidate->votes_count == $previous)
        $tie++;
    else {
        $rank += 1 + $tie;
        $tie = 0;
        $previous = $candidate->votes_count;
        $oddeven ^= 1;
    }
    echo html_writer::start_tag('tr', array('class' => $oddeven ? 'odd' : 'even'));
    echo html_writer::tag('td', $rank, array('class' => 'rank'));
    echo html_writer::tag('td',
        html_writer::tag('span', $candidate->courseware->fullname) .
        $OUTPUT->action_icon(
            new moodle_url('/course/view.php', array('id' => $candidate->courseware->courseid)),
            new pix_icon('t/preview', get_string('previewthiscourseware', 'local_majhub'))
            )
        );
    echo html_writer::tag('td', $candidate->votes_count, array('class' => 'count'));
    echo html_writer::end_tag('tr');
}
echo html_writer::end_tag('table');

echo $OUTPUT->footer();
