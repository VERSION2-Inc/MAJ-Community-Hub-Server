<?php // $Id: install.php 148 2012-12-01 08:37:33Z malu $

defined('MOODLE_INTERNAL') || die;

/**
 *  MAJ Hub Voting block install
 *  
 *  @global moodle_database $DB
 *  @return boolean
 */
function xmldb_block_majhub_voting_install()
{
    global $DB;

    $extension = new stdClass;
    $extension->pluginname  = 'block_majhub_voting';
    $extension->timecreated = time();
    $DB->insert_record('majhub_courseware_extensions', $extension);

    return true;
}
