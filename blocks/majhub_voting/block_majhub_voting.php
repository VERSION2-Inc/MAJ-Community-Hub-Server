<?php

require_once __DIR__.'/classes/candidate.php';

/**
 *  MAJ Hub Voting block
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: block_majhub_voting.php 216 2013-02-21 09:37:35Z malu $
 */
class block_majhub_voting extends block_base
{
    public function init()
    {
        $this->title   = get_string('blocktitle', __CLASS__);
        $this->version = 2012120100;
    }

    public function applicable_formats()
    {
        return array('course' => true, 'course-category' => false);
    }

    /**
     *  Gets a block content
     *  
     *  @global object $USER
     *  @glocal core_renderer $OUTPUT
     *  @return object|string
     */
    public function get_content()
    {
        global $USER, $OUTPUT;

        if ($this->content !== null)
            return $this->content;

        if (empty($this->instance) || empty($USER->id))
            return $this->content = '';

        $courseware = majhub\courseware::from_courseid($this->page->course->id);
        if (!$courseware)
            return $this->content = '';

        $isadmin = has_capability('moodle/site:config', context_system::instance());
        $isstaff = has_capability('moodle/course:update', context_course::instance($this->page->course->id));

        $candidate = majhub\voting\candidate::from_coursewareid($courseware->id);
        if (!$candidate && !$isstaff)
            return $this->content = ''; // only moderators (= editing teachers) can nominate

        if ($isstaff && optional_param('nominate', null, PARAM_TEXT)) {
            $competitionid = required_param('competition', PARAM_INT);
            majhub\voting\competition::from_id($competitionid, MUST_EXIST)->nominate($courseware);
            redirect($this->page->url);
        }

        if ($candidate && optional_param('vote', null, PARAM_TEXT)) {
            $candidate->vote($USER->id);
            redirect($this->page->url);
        }

        $html = '';

        if ($candidate) {
            // this is a candidate, shows vote button or a message of thanks for voting
            $dateformat = get_string('strftimedateshort', 'langconfig');
            $startdate = userdate($candidate->competition->timestart, $dateformat);
            $enddate = userdate($candidate->competition->timeend, $dateformat);
            $html .= html_writer::tag('div', $candidate->competition->title, array('class' => 'title'));
            $html .= html_writer::tag('div', $startdate . ' - ' . $enddate, array('class' => 'period'));
            if ($vote = $candidate->competition->get_vote($USER->id)) {
                if ($vote->candidateid == $candidate->id) {
                    // has voted for this courseware
                    $html .= html_writer::tag('div',
                        get_string('thanksforvoting', __CLASS__), array('class' => 'thanks')
                        );
                } else {
                    // has voted for another courseware
                    $url = new moodle_url('/course/view.php', array('id' => $vote->candidate->courseware->course->id));
                    $html .= html_writer::tag('div',
                        get_string('youhadvotedfor', __CLASS__) . ': ' .
                        html_writer::link($url, $vote->candidate->courseware->fullname),
                        array('class' => 'voted')
                        );
                }
            } elseif (time() < $candidate->competition->timestart) {
                $html .= html_writer::tag('div', get_string('comingsoon', __CLASS__), array('class' => 'comingsoon'));
            } elseif (time() <= $candidate->competition->timeend) {
                $html .= html_writer::start_tag('form', array('action' => $this->page->url, 'method' => 'post'));
                $html .= self::render_input('id', $this->page->url->param('id'), 'hidden');
                $html .= html_writer::tag('div',
                    self::render_input('vote', get_string('voteforthiscourseware', __CLASS__), 'submit'),
                    array('class' => 'vote')
                    );
                $html .= html_writer::end_tag('form');
            }
            if ($isstaff || time() > $candidate->competition->timeend) {
                $votes = html_writer::link(
                    new moodle_url('/blocks/majhub_voting/results.php', array('id' => $candidate->competition->id)),
                    html_writer::tag('span', $candidate->votes_count, array('class' => 'votes'))
                    );
                $html .= html_writer::tag('div',
                    get_string('numberofvotes', __CLASS__) . ': ' . $votes, array('class' => 'result')
                    );
            }
        } else {
            // not a candidate, shows accepting competitions
            $competitions = majhub\voting\competition::acceptings();
            if (empty($competitions)) {
                if ($isadmin) {
                    $settingsurl = new moodle_url('/blocks/majhub_voting/admin/competitions.php');
                    $settingsicon = $OUTPUT->pix_icon('i/settings', '');
                    $html .= html_writer::tag('div',
                        html_writer::link($settingsurl, $settingsicon . get_string('settings/competitions', __CLASS__))
                        );
                } else {
                    $html .= html_writer::tag('div', get_string('nocompetitiontonominate', __CLASS__));
                }
            } else {
                $html .= html_writer::start_tag('form', array('action' => $this->page->url, 'method' => 'post'));
                $html .= self::render_input('id', $this->page->url->param('id'), 'hidden');
                $html .= html_writer::start_tag('div');
                $html .= html_writer::start_tag('select', array('name' => 'competition'));
                foreach ($competitions as $competition) {
                    $html .= html_writer::tag('option', $competition->title, array('value' => $competition->id));
                }
                $html .= html_writer::end_tag('select');
                $html .= self::render_input('nominate', get_string('nominatethiscourseware', __CLASS__), 'submit');
                $html .= html_writer::end_tag('div');
                $html .= html_writer::end_tag('form');
            }
        }

        return $this->content = (object)array('text' => $html);
    }

    /**
     *  Renders an input element
     *  
     *  @param string $name
     *  @param string $value
     *  @return string
     */
    private static function render_input($name, $value, $type = 'text')
    {
        return html_writer::empty_tag('input', array('type' => $type, 'name' => $name, 'value' => $value));
    }
}
