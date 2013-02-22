<?php // $Id: version.php 221 2013-02-22 03:00:55Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013022102;
$plugin->release   = '2.3, release 2 patch 4';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'local_majhub';
$plugin->cron      = 1;

$plugin->dependencies = array(
    'block_majhub' => 2013020700,
);
