<?php // $Id: settings.php 148 2012-12-01 08:37:33Z malu $

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {
    $settings->add(
        new admin_setting_heading('block_majhub_voting/competitions', '',
            html_writer::link(
                new moodle_url('/blocks/majhub_voting/admin/competitions.php'),
                get_string('settings/competitions', 'block_majhub_voting')))
        );
}
