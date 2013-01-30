<?php // $Id: version.php 200 2013-01-30 05:17:17Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013013000;
$plugin->release   = '2.3, release 2 patch 1';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'local_majhub';
$plugin->cron      = 1;

$plugin->dependencies = array(
    'block_majhub' => 2013013000,
);
