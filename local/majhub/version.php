<?php // $Id: version.php 202 2013-01-30 09:18:03Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013013001;
$plugin->release   = '2.3, release 2 patch 2';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'local_majhub';
$plugin->cron      = 1;

$plugin->dependencies = array(
    'block_majhub' => 2013013001,
);
