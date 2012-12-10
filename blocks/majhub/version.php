<?php // $Id: version.php 144 2012-12-01 07:59:18Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2012120100;
$plugin->release   = '2.3, release candidate 1';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'block_majhub';

$plugin->dependencies = array(
    'local_majhub' => 2012120100,
);
