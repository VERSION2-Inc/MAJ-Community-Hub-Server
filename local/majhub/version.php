<?php // $Id: version.php 172 2012-12-11 08:58:26Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2012121100;
$plugin->release   = '2.3, release candidate 2';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'local_majhub';
$plugin->cron      = 1;

$plugin->dependencies = array(
    'block_majhub' => 2012120100,
);
