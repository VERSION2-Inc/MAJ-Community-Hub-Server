<?php // $Id: settings.php 104 2012-11-23 04:59:04Z malu $

defined('MOODLE_INTERNAL') || die;

$ADMIN->add('root', new admin_category('majhub', new lang_string('pluginname', 'local_majhub')));
if (empty($CFG->customfrontpageinclude) || realpath($CFG->customfrontpageinclude) !== realpath(__DIR__.'/frontpage.php')) {
    // MAJ Hub requires special configuration for custom frontpage
    $ADMIN->add('majhub', new admin_externalpage('readme', 'INSTALL', new moodle_url('/local/majhub/INSTALL.txt')));
} else {
    $ADMIN->add('majhub', new admin_externalpage('metafields',
        new lang_string('settings/metafields', 'local_majhub'),
        new moodle_url('/local/majhub/admin/metafields.php')));
}
