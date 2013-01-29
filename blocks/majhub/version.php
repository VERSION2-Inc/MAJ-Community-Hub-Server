<?php // $Id: version.php 197 2013-01-29 04:29:22Z malu $

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2013012901;
$plugin->release   = '2.3, release 2';
$plugin->requires  = 2012062500.00; // Moodle 2.3.0
$plugin->component = 'block_majhub';

$plugin->dependencies = array(
    'local_majhub' => 2013012901,
);
