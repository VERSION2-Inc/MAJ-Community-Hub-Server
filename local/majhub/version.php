<?php // $Id: version.php 214 2013-02-21 09:30:58Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013022101;
$plugin->release   = '2.3, release 2 patch 3';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'local_majhub';
$plugin->cron      = 1;

$plugin->dependencies = array(
    'block_majhub' => 2013020700,
);
