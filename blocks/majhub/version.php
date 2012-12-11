<?php // $Id: version.php 168 2012-12-10 10:02:21Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2012120100;
$plugin->release   = '2.3, release candidate 1';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'block_majhub';

$plugin->dependencies = array(
    'local_majhub' => 2012120100,
);
