<?php // $Id: version.php 203 2013-01-30 09:18:12Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013013001;
$plugin->release   = '2.3, release 2 patch 2';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'block_majhub';

$plugin->dependencies = array(
    'local_majhub' => 2013013001,
);
