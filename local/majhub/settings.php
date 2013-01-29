<?php // $Id: settings.php 177 2013-01-25 12:39:58Z malu $

defined('MOODLE_INTERNAL') || die;

if (false) {
    $CFG   = new stdClass;
    $ADMIN = new admin_root(null);
}

$ADMIN->add('root', new admin_category('majhub', new lang_string('pluginname', 'local_majhub')));
if (empty($CFG->customfrontpageinclude) || realpath($CFG->customfrontpageinclude) !== realpath(__DIR__.'/frontpage.php')) {
    // MAJ Hub requires special configuration for custom frontpage
    $ADMIN->add('majhub', new admin_externalpage('readme', 'INSTALL', new moodle_url('/local/majhub/INSTALL.txt')));
} else {
    $ADMIN->add('majhub', new admin_externalpage('majhub/frontpage',
        new lang_string('settings/frontpage', 'local_majhub'),
        new moodle_url('/local/majhub/admin/frontpage.php')));
    $ADMIN->add('majhub', new admin_externalpage('majhub/metafields',
        new lang_string('settings/metafields', 'local_majhub'),
        new moodle_url('/local/majhub/admin/metafields.php')));
    $ADMIN->add('majhub', new admin_externalpage('majhub/pointsystem',
        new lang_string('settings/pointsystem', 'local_majhub'),
        new moodle_url('/local/majhub/admin/pointsystem.php')));
}
