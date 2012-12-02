<?php // $Id: edit.php 125 2012-11-26 10:49:04Z malu $

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/classes/courseware.php';

if (false) {
    $OUTPUT = new core_renderer;
    $PAGE   = new moodle_page;
}

$id = required_param('id', PARAM_INT);

$courseware = majhub\courseware::from_id($id);
if (!$courseware) {
    if (isset($_SERVER['HTTP_REFERER'])) {
        redirect($_SERVER['HTTP_REFERER'] . "#missingcourseware={$id}");
    }
    print_error('error:missingcourseware', 'local_majhub', null, $id);
}
if ($courseware->courseid) {
    // the preview course ready, let's redirect to there
    redirect(new moodle_url('/course/view.php', array('id' => $courseware->course->id, 'editmetadata' => 1)));
}

// not ready yet, shows a message
$message = get_string('visitlater', 'local_majhub');

$PAGE->set_url('/local/majhub/edit.php', array('id' => $id));
$PAGE->set_context(context_system::instance());
$PAGE->set_cacheable(false);

require_login();

// uses topics format style for unready course
$PAGE->set_pagelayout('course');
$PAGE->set_pagetype('course-view-topics');

$PAGE->set_title(get_string('course') . ': ' . $courseware->unique_fullname);
$PAGE->set_heading($courseware->unique_fullname);
$PAGE->navbar->add(get_string('mycourses'));
$PAGE->navbar->add($courseware->unique_shortname);

echo $OUTPUT->header();

echo html_writer::start_tag('div', array('class' => 'course-content'));
echo html_writer::start_tag('ul', array('class' => 'topics'));
echo html_writer::start_tag('li', array('class' => 'section main clearfix'));
echo html_writer::start_tag('div', array('class' => 'content'));
echo html_writer::tag('h3', $message, array('class' => 'sectionname'));
echo html_writer::end_tag('div');
echo html_writer::end_tag('li');
echo html_writer::end_tag('ul');
echo html_writer::end_tag('div');

echo $OUTPUT->footer();
