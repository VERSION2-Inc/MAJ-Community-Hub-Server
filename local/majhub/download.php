<?php // $Id: download.php 91 2012-11-21 08:45:29Z malu $

require_once __DIR__.'/../../config.php';
require_once __DIR__.'/../../lib/filelib.php';
require_once __DIR__.'/classes/courseware.php';

if (false) {
    $USER = new stdClass;
    $DB   = new mysqli_native_moodle_database;
    $PAGE = new moodle_page;
}

$id = required_param('id', PARAM_INT);

$courseware = majhub\courseware::from_id($id, MUST_EXIST);

$PAGE->set_url('/local/majhub/download.php', array('id' => $id));
$PAGE->set_course($courseware->course);
$PAGE->set_cacheable(false);

require_login($courseware->course, false);

// TODO: check permissions for downloading courseware

if (!$DB->record_exists('majhub_courseware_downloads',
    array('userid' => $USER->id, 'coursewareid' => $courseware->id)))
{
    $record = new stdClass;
    $record->userid       = $USER->id;
    $record->coursewareid = $courseware->id;
    $record->timecreated  = time();
    $DB->insert_record('majhub_courseware_downloads', $record);
}

send_stored_file($courseware->file);
