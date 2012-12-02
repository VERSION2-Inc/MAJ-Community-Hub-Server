<?php // $Id: upgrade.php 148 2012-12-01 08:37:33Z malu $

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

    if ($oldversion < 2012120100) {
        require_once __DIR__.'/install.php';
        xmldb_block_majhub_voting_install();
    }

    return true;
}
