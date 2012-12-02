<?php // $Id: upgrade.php 145 2012-12-01 08:08:11Z malu $

defined('MOODLE_INTERNAL') || die;

/**
 *  MAJ Hub upgrade
 *  
 *  @global moodle_database $DB
 *  @return boolean
 */
function xmldb_local_majhub_upgrade($oldversion = 0)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2012120100) {
        $table = new xmldb_table('majhub_courseware_extensions');
        $table->add_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('pluginname', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    return true;
}
