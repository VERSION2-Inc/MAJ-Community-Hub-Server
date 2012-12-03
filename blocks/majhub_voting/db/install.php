<?php // $Id: install.php 161 2012-12-03 07:03:22Z malu $

defined('MOODLE_INTERNAL') || die;

/**
 *  MAJ Hub Voting block install
 *  
 *  @return boolean
 */
function xmldb_block_majhub_voting_install()
{
    // The extensions table does not exist if we install both extension block and local_majhub
    // '$plugin->dependencies' declaration in version.php is useless at database dependencies
    //require_once __DIR__.'/../classes/extension.php';
    //majhub\extension::install('block_majhub_voting');

    return true;
}
