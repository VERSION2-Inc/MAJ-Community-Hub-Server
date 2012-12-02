<?php // $Id: version.php 143 2012-12-01 07:59:13Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2012120100;
$plugin->release   = '2.3, release candidate 1';
$plugin->requires  = 2012062503.00; // Moodle 2.3.3
$plugin->component = 'local_majhub';
$plugin->cron      = 1;

$plugin->dependencies = array(
    'block_majhub' => 2012120100,
);
