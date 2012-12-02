<?php // $Id: frontpage.php 142 2012-11-30 06:36:21Z malu $

defined('MOODLE_INTERNAL') || die;

if (false) {
    $CFG  = new stdClass;
    $SITE = new stdClass;
    $PAGE = new moodle_page;
    $modnames     = array();
    $modnamesused = array();
}

// suppresses all standard frontpage contents after MAJ Hub sections
$CFG->frontpageloggedin = $CFG->frontpage = '';


// oops, too late to require css... we need style attributes for each elements!
//$PAGE->requires->css('/local/majhub/frontpage.css');

$sectionnames = array('leaderboard', 'searchcriteria');
if (optional_param('search', null, PARAM_TEXT)) {
    $sectionnames[] = 'searchresults';
}
foreach ($sectionnames as $sectionname) {
    echo html_writer::start_tag('div', array('class' => 'mform'));
    echo html_writer::start_tag('fieldset', array('class' => 'clearfix'));
    echo html_writer::tag('legend', get_string($sectionname, 'local_majhub'));
    {
        require_once __DIR__.'/frontpage/'.$sectionname.'.php';
    }
    echo html_writer::end_tag('fieldset');
    echo html_writer::end_tag('div');
}

#$PAGE->requires->string_for_js('showoptionalcriteria', 'local_majhub');
#$PAGE->requires->string_for_js('hideoptionalcriteria', 'local_majhub');
#$PAGE->requires->js('/local/majhub/frontpage.js');
#$PAGE->requires->js_init_call('M.local_majhub.frontpage.init');

// Include course AJAX
if (include_course_ajax($SITE, $modnamesused)) {
    // Add the module chooser
    $renderer = $PAGE->get_renderer('core', 'course');
    echo $renderer->course_modchooser(get_module_metadata($SITE, $modnames), $SITE);
}
