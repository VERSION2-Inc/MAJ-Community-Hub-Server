<?php // $Id: version.php 199 2013-01-29 04:53:42Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013012902;
$plugin->release   = '2.3, release 2';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'local_majhub';
$plugin->cron      = 1;

$plugin->dependencies = array(
    'block_majhub' => 2013012901,
);
