<?php // $Id: upgrade.php 161 2012-12-03 07:03:22Z malu $

defined('MOODLE_INTERNAL') || die;

/**
 *  MAJ Hub Voting block upgrade
 *  
 *  @global moodle_database $DB
 *  @return boolean
 */
function xmldb_block_majhub_voting_upgrade($oldversion = 0)
{
    global $DB;

    $dbman = $DB->get_manager();

//  if ($oldversion < 2012120100) {
//  }

    return true;
}
