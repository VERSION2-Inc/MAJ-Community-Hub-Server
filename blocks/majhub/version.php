<?php // $Id: version.php 201 2013-01-30 05:17:22Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013013000;
$plugin->release   = '2.3, release 2 patch 1';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'block_majhub';

$plugin->dependencies = array(
    'local_majhub' => 2013013000,
);
