<?php // $Id: version.php 148 2012-12-01 08:37:33Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2012120100;
$plugin->release   = '2.3, release candidate 1';
$plugin->requires  = 2012062503.00; // Moodle 2.3.3
$plugin->component = 'block_majhub_voting';

$plugin->dependencies = array(
    'local_majhub' => 2012120100,
);