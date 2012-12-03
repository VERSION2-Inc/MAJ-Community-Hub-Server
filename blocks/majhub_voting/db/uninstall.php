<?php // $Id: uninstall.php 161 2012-12-03 07:03:22Z malu $

defined('MOODLE_INTERNAL') || die;

/**
 *  MAJ Hub Voting block uninstall
 *  
 *  @return boolean
 */
function xmldb_block_majhub_voting_uninstall()
{
    require_once __DIR__.'/../classes/extension.php';
    majhub\extension::uninstall('block_majhub_voting');

    return true;
}
