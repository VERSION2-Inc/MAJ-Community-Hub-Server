<?php

require_once __DIR__.'/../../local/majhub/classes/courseware.php';
require_once __DIR__.'/../../local/majhub/classes/point.php';

/**
 *  MAJ Hub My Points block
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: block_majhub_points.php 229 2013-03-01 06:24:21Z malu $
 */
class block_majhub_points extends block_base
{
    public function init()
    {
        $this->title   = get_string('blocktitle', __CLASS__);
        $this->version = 2013030101;
    }

    public function applicable_formats()
    {
        return array('course' => true, 'course-category' => false);
    }

    public function instance_can_be_docked()
    {
        return false; // AJAX won't work with Dock
    }

    /**
     *  Gets a block content
     *  
     *  @global object $USER
     *  @global moodle_database $DB
     *  @global core_renderer $OUTPUT
     *  @return object|string
     */
    public function get_content()
    {
        global $USER, $DB, $OUTPUT;

        if ($this->content !== null)
            return $this->content;

        if (empty($this->instance) || empty($USER->id))
            return $this->content = '';

        $courseware = majhub\courseware::from_courseid($this->page->course->id);
        if (empty($courseware))
            return $this->content = '';

        $point = majhub\point::from_userid($USER->id);
        $html = '
          <table class="points">
            <tr><th rowspan="5" class="plus">' . $OUTPUT->pix_icon('plus', '', __CLASS__) . '</th>
                <th>' . get_string('registrationbonus', __CLASS__) . '</th><td>' . $point->registration . '</td></tr>
            <tr><th>' . get_string('totaluploads', __CLASS__) . '</th><td>' . $point->upload . '</td></tr>
            <tr><th>' . get_string('totalreviews', __CLASS__) . '</th><td>' . $point->review . '</td></tr>
            <tr><th>' . get_string('popularitybonuses', __CLASS__) . '</th><td>' . $point->popularity . '</td></tr>
            <tr><th>' . get_string('qualitybonuses', __CLASS__) . '</th><td>' . $point->quality . '</td></tr>
            <tr><th rowspan="1" class="minus">' . $OUTPUT->pix_icon('minus', '', __CLASS__) . '</th>
                <th>' . get_string('totaldownloads', __CLASS__) . '</th><td>' . $point->download . '</td></tr>
            <tr class="total"><th></th>
                <th>' . get_string('total', __CLASS__) . '</th><td>' . $point->total . '</td></tr>
          </table>';

        return $this->content = (object)array('text' => $html);
    }
}
