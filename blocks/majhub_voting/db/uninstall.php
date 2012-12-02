<?php // $Id: uninstall.php 148 2012-12-01 08:37:33Z malu $

defined('MOODLE_INTERNAL') || die;

/**
 *  MAJ Hub Voting block uninstall
 *  
 *  @global moodle_database $DB
 *  @return boolean
 */
function xmldb_block_majhub_voting_uninstall()
{
    global $DB;

    $DB->delete_records('majhub_courseware_extensions', array('pluginname' => 'block_majhub_voting'));

    return true;
}
