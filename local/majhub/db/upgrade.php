<?php // $Id: upgrade.php 214 2013-02-21 09:30:58Z malu $

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

    if ($oldversion < 2013012400) {
        $table = new xmldb_table('majhub_settings');
        $table->add_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
        $table->add_field('value', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2013012700) {
        $table = new xmldb_table('majhub_settings');
        $key = new xmldb_key('name', XMLDB_KEY_UNIQUE, array('name'));
        $dbman->add_key($table, $key);
    }

    if ($oldversion < 2013012801) {
        $table = new xmldb_table('majhub_bonus_points');
        $table->add_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('coursewareid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('reason', XMLDB_TYPE_CHAR, 255, null, XMLDB_NOTNULL, null, null);
        $table->add_field('points', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2013012802) {
        $table = new xmldb_table('majhub_review_proscons');
        $table->add_field('id', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('reviewid', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_field('procon', XMLDB_TYPE_CHAR, 4, null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2013012902) {
        require_once __DIR__.'/../classes/setting.php';
        if (majhub\setting::get('coursewaresperpageoptions') === null)
            majhub\setting::set('coursewaresperpageoptions', '5, 10, 50, 100');
        if (majhub\setting::get('coursewaresperpagedefault') === null)
            majhub\setting::set('coursewaresperpagedefault', '10');
    }

    if ($oldversion < 2013020102) {
        require_once __DIR__.'/../classes/setting.php';
        if (majhub\setting::get('lengthforreviewing') === null)
            majhub\setting::set('lengthforreviewing', 100);
    }

    if ($oldversion < 2013022101) {
        $table = new xmldb_table('majhub_coursewares');
        $field = new xmldb_field('timestarted', XMLDB_TYPE_INTEGER, 10, XMLDB_UNSIGNED, null, null, null, 'timerestored');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}
