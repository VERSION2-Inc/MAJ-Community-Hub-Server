<?php // $Id: create.php 51 2012-11-13 03:36:55Z malu $

require_once __DIR__.'/../../../config.php';

try {
    require_login(null, false, null, false, true);

    $fullname  = required_param('fullname', PARAM_TEXT);
    $shortname = required_param('shortname', PARAM_TEXT);
    $filesize  = required_param('filesize', PARAM_INT); // TODO: how do we handle large file >2GiB?

    $courseware = (object)array(
        'userid' => $USER->id,
        'fullname' => $fullname,
        'shortname' => $shortname,
        'filesize' => $filesize,
        'version' => '1.0',
        'timecreated' => time(),
        'timemodified' => time(),
        );
    $courseware->id = $DB->insert_record('majhub_coursewares', $courseware);

    $doc = new DOMDocument('1.0', 'UTF-8');
    $doc->formatOutput = true;
    $root = $doc->appendChild($doc->createElement('majhub'));
    $node = $root->appendChild($doc->createElement('courseware'));
    $node->setAttribute('id', $courseware->id);
    send_headers('application/xml; charset=UTF-8');
    echo $doc->saveXML();

} catch (Exception $ex) {
    header('HTTP/1.1 400 Bad Request');
    // TODO: respond error to client
    error_log($ex->__toString());
}
