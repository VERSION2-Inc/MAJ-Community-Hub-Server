<?php

require_once __DIR__.'/../../local/majhub/classes/courseware.php';
require_once __DIR__.'/../../local/majhub/classes/capability.php';
require_once __DIR__.'/../../local/majhub/classes/point.php';

/**
 *  MAJ Hub block
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: block_majhub.php 203 2013-01-30 09:18:12Z malu $
 */
class block_majhub extends block_base
{
    const MAX_REVIEWS = 3;

    public function init()
    {
        $this->title   = get_string('blocktitle', __CLASS__);
        $this->version = 2013013001;
    }

    public function applicable_formats()
    {
        return array('course' => true);
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

        $html = '';

        $isowner = $courseware->userid == $USER->id;
        $isadmin = majhub\capability::is_admin($USER);
        $ismoderator = majhub\capability::is_moderator($USER);

        $purchased = $DB->record_exists('majhub_courseware_downloads',
            array('userid' => $USER->id, 'coursewareid' => $courseware->id));
        $userpoint = majhub\point::from_userid($USER->id);

        if ($ismoderator && optional_param('givebonuspointsforquality', null, PARAM_TEXT)) {
            $bonuspointsforquality = optional_param('bonuspointsforquality', 0, PARAM_INT);
            if ($bonuspointsforquality != 0) {
                $bonuspoint = new stdClass;
                $bonuspoint->coursewareid = $courseware->id;
                $bonuspoint->userid       = $USER->id;
                $bonuspoint->reason       = 'quality';
                $bonuspoint->points       = $bonuspointsforquality;
                $bonuspoint->timecreated  = time();
                $DB->insert_record('majhub_bonus_points', $bonuspoint);
            }
            redirect($this->page->url);
        }
        if (optional_param('submitreview', null, PARAM_TEXT)) {
            $rating  = optional_param('rating', 0, PARAM_INT);
            $comment = trim(optional_param('comment', '', PARAM_TEXT));
            if ($rating <= 0) {
                redirect(new moodle_url($this->page->url, array('editreview' => 1)));
            }
            $review = $DB->get_record('majhub_courseware_reviews',
                array('userid' => $USER->id, 'coursewareid' => $courseware->id));
            if ($review) {
                $review->rating       = $rating;
                $review->comment      = $comment;
                $review->timemodified = time();
                $DB->update_record('majhub_courseware_reviews', $review);
            } else {
                $review = new stdClass;
                $review->userid       = $USER->id;
                $review->coursewareid = $courseware->id;
                $review->rating       = $rating;
                $review->comment      = $comment;
                $review->timecreated  = time();
                $review->timemodified = time();
                $DB->insert_record('majhub_courseware_reviews', $review);
            }
            redirect($this->page->url);
        }
        if ($pro = optional_param('pro', 0, PARAM_INT) or $con = optional_param('con', 0, PARAM_INT)) {
            $proscons = new stdClass;
            $proscons->userid      = $USER->id;
            $proscons->reviewid    = $pro ?: $con;
            $proscons->procon      = $pro ? 'pro' : 'con';
            $proscons->timecreated = time();
            $DB->insert_record('majhub_review_proscons', $proscons);
            redirect($this->page->url);
        }

        $userlink = $OUTPUT->action_link(
            new moodle_url('/user/profile.php', array('id' => $courseware->user->id)),
            fullname($courseware->user)
            );
        $dateformat = get_string('strftimedateshort', 'langconfig');
        $fixedrows = array(
            get_string('title', 'local_majhub')       => $courseware->fullname,
            get_string('contributor', 'local_majhub') => $userlink,
            get_string('uploadedat', 'local_majhub')  => userdate($courseware->timerestored, $dateformat),
            get_string('filesize', 'local_majhub')    => display_size($courseware->filesize),
        //  get_string('version', 'local_majhub')     => $courseware->version,
            );

        if ($isowner || $isadmin /* || $ismoderator */) {
            $html .= html_writer::tag('div',
                $OUTPUT->action_link(
                    new moodle_url('/local/majhub/edit.php', array('id' => $courseware->id)),
                    $OUTPUT->pix_icon('i/edit', get_string('edit')) . get_string('editmetadata', 'block_majhub')
                    ),
                array('class' => 'editmetadata')
                );
        }
        $html .= html_writer::start_tag('table', array('class' => 'metadata'));
        foreach ($fixedrows as $name => $value) {
            $html .= self::render_row($name, $value);
        }
        $html .= self::render_row(get_string('demourl', 'local_majhub'), self::render_url($courseware->demourl));
        foreach ($courseware->metadata as $metadatum) {
            $html .= self::render_row($metadatum->name, $metadatum->render(', '),
                $metadatum->optional ? array('class' => 'optional') : null);
        }
        $html .= html_writer::end_tag('table');

        $html .= html_writer::empty_tag('hr');

        // actions
        $downloadcost = majhub\point::get_settings()->pointsfordownloading;
        $downloadurl = new moodle_url('/local/majhub/download.php', array('id' => $courseware->id));
        $strdownload = $OUTPUT->pix_icon('t/download', '') . get_string('download');
        $attrdownload = array('title' => get_string('downloadthiscourseware', 'local_majhub'));
        if ($purchased) {
            $html .= html_writer::start_tag('div', array('class' => 'action download'));
            $html .= $OUTPUT->action_link($downloadurl, $strdownload, null, $attrdownload);
            $html .= html_writer::end_tag('div');
        } elseif ($userpoint->total >= $downloadcost) {
            $confirmpurchase = new confirm_action(get_string('confirm:purchase', 'local_majhub', $downloadcost));
            $html .= html_writer::start_tag('div', array('class' => 'action download'));
            $html .= $OUTPUT->action_link($downloadurl, $strdownload, $confirmpurchase, $attrdownload);
            $html .= html_writer::end_tag('div');
        } else {
            $html .= html_writer::tag('div', $strdownload, array('class' => 'action download grayed'));
        }
        $html .= html_writer::tag('div',
            get_string('youhavepoints', 'local_majhub', $userpoint->total),
            array('class' => $userpoint->total >= $downloadcost ? 'points' : 'points short')
            );

        $html .= html_writer::empty_tag('hr');

        // reviews
        $reviews = $courseware->get_reviews(optional_param('showallreviews', 0, PARAM_INT) ? 0 : self::MAX_REVIEWS);
        $reviewed = $courseware->is_reviewed_by($USER->id);
        $html .= html_writer::start_tag('div', array('class' => 'reviews'));
        $html .= html_writer::tag('div',
            html_writer::tag('span', get_string('overallrating', 'local_majhub')) .
            self::render_rating($courseware->avarage_rating),
            array('class' => 'overall')
            );
        if ($reviewed) {
            // should the review be modifiable?
        } elseif (optional_param('editreview', 0, PARAM_INT)) {
            $stars = array();
            foreach (range(1, 5) as $rating) {
                $stars[] = html_writer::tag('label',
                    self::render_input('rating', $rating * 2, 'radio') . $rating);
            }
            $html .= html_writer::start_tag('form', array('action' => $this->page->url, 'method' => 'post'));
            $html .= self::render_input('id', $this->page->url->param('id'), 'hidden');
            $html .= html_writer::start_tag('div', array('class' => 'review'));
            $html .= html_writer::tag('div', get_string('rating', 'local_majhub') . ': ' . implode(' ', $stars));
            $html .= html_writer::tag('div',
                html_writer::tag('textarea', '', array('name' => 'comment', 'cols' => 30, 'rows' => 8))
                );
            $html .= html_writer::end_tag('div');
            $html .= html_writer::tag('div', self::render_input('submitreview', get_string('submit'), 'submit'));
            $html .= html_writer::end_tag('form');
        } else {
            $html .= html_writer::tag('div',
                html_writer::link(
                    new moodle_url($this->page->url, array('editreview' => 1)),
                    $OUTPUT->pix_icon('t/add', '') . get_string('review', 'local_majhub')
                    ),
                array('class' => 'action review', 'title' => get_string('review', 'local_majhub'))
                );
        }
        $moderatoricon = $OUTPUT->pix_icon('f/moodle', get_string('moderator', 'local_majhub'));
        foreach ($reviews as $review) {
            $fullname = fullname($review->user);
            if (majhub\capability::is_moderator($review->user)) {
                $fullname = html_writer::tag('span', $moderatoricon . $fullname, array('class' => 'moderator'));
            }
            $html .= html_writer::start_tag('div', array('class' => 'review'));
            $html .= html_writer::tag('div',
                html_writer::tag('div', $OUTPUT->user_picture($review->user), array('class' => 'picture')) .
                html_writer::tag('div', self::render_rating($review->rating), array('class' => 'rating')) .
                html_writer::tag('div', $this->render_proscons($review->id), array('class' => 'proscons')) .
                html_writer::tag('div', $fullname, array('class' => 'fullname')),
                array('class' => 'userinfo')
                );
            $html .= html_writer::tag('div', nl2br(clean_text($review->comment)), array('class' => 'comment'));
            $html .= html_writer::end_tag('div');
        }
        if (count($reviews) < $courseware->number_of_reviews) {
            $html .= html_writer::tag('div',
                get_string('latestreviews', 'local_majhub',
                    (object)array('latest' => count($reviews), 'total' => $courseware->number_of_reviews)) .
                ' ' .
                $OUTPUT->action_link(new moodle_url($this->page->url, array('showallreviews' => 1)), '...'),
                array('class' => 'ellipsis')
                );
        }
        $html .= html_writer::end_tag('div'); // reviews

        // moderator actions
        if ($ismoderator) {
            $html .= html_writer::empty_tag('hr');
            $html .= html_writer::start_tag('form',
                array('action' => $this->page->url, 'method' => 'post', 'class' => 'mform bonuspoints')
                );
            $html .= self::render_input('id', $this->page->url->param('id'), 'hidden');
            $html .= get_string('pointsforquality', 'local_majhub') . ': ';
            $bonuspoints = $DB->count_records_sql(
                'SELECT SUM(points) FROM {majhub_bonus_points}
                 WHERE coursewareid = :coursewareid AND reason = :reason',
                array('coursewareid' => $courseware->id, 'reason' => 'quality')
                );
            if ($bonuspoints > 0) {
                $html .= html_writer::tag('span', $bonuspoints);
            } else {
                $html .= html_writer::start_tag('div');
                $html .= self::render_input('bonuspointsforquality',
                    majhub\point::get_settings()->pointsforquality, 'text', array('size' => 2, 'class' => 'points')
                    );
                $html .= self::render_input('givebonuspointsforquality', get_string('give', 'local_majhub'), 'submit');
                $html .= html_writer::end_tag('div');
            }
            $html .= html_writer::end_tag('form');
        }

        $this->page->requires->js_init_call('M.block_majhub.init');
        $this->page->requires->string_for_js('optionalfields', 'local_majhub');

        return $this->content = (object)array('text' => $html);
    }

    /**
     *  Renders pros and cons
     *  
     *  @global object $USER
     *  @global moodle_database $DB
     *  @global core_renderer $OUTPUT
     *  @param int $reviewid
     *  @return string
     */
    private function render_proscons($reviewid)
    {
        global $USER, $DB, $OUTPUT;

        $pros = $DB->count_records('majhub_review_proscons', array('reviewid' => $reviewid, 'procon' => 'pro'));
        $cons = $DB->count_records('majhub_review_proscons', array('reviewid' => $reviewid, 'procon' => 'con'));
        if ($DB->record_exists('majhub_review_proscons', array('reviewid' => $reviewid, 'userid' => $USER->id))) {
            $pro = $OUTPUT->pix_icon('s/yes', '');
            $con = $OUTPUT->pix_icon('s/no', '');
            return $pro . $pros . ' ' . $con . $cons;
        }
        $pro = $OUTPUT->action_icon(
            new moodle_url($this->page->url, array('pro' => $reviewid)),
            new pix_icon('s/yes', '')
            );
        $con = $OUTPUT->action_icon(
            new moodle_url($this->page->url, array('con' => $reviewid)),
            new pix_icon('s/no', '')
            );
        return $pro . $pros . ' ' . $con . $cons;
    }

    /**
     *  Renders a row
     *  
     *  @param string $name
     *  @param string $value
     *  @param array $attributes
     *  @return string
     */
    private static function render_row($name, $value, array $attributes = null)
    {
        return html_writer::tag('tr',
            html_writer::tag('th', $name) . html_writer::tag('td', $value),
            $attributes
            );
    }

    /**
     *  Renders an input element
     *  
     *  @param string $name
     *  @param string $value
     *  @param string $type
     *  @param array $attributes
     *  @return string
     */
    private static function render_input($name, $value, $type = 'text', array $attributes = null)
    {
        $attrs = array('type' => $type, 'name' => $name, 'value' => $value);
        if ($attributes !== null)
            $attrs += $attributes;
        return html_writer::empty_tag('input', $attrs);
    }

    /**
     *  Renders a URL link
     *  
     *  @param string $url
     *  @return string
     */
    private static function render_url($url)
    {
        return !empty($url) && ($host = @parse_url($url, PHP_URL_HOST))
             ? html_writer::link($url, $host, array('title' => $url))
             : $url;
    }

    /**
     *  Renders rating starts
     *  
     *  @global core_renderer $OUTPUT
     *  @param float $rating
     *  @return string
     */
    private static function render_rating($rating)
    {
        global $OUTPUT;
        static $stars = null;
        if ($stars === null) {
            $stars = array(
                0 => $OUTPUT->pix_icon('star-none', '', 'local_majhub'),
                1 => $OUTPUT->pix_icon('star-half', '', 'local_majhub'),
                2 => $OUTPUT->pix_icon('star-full', '', 'local_majhub'),
                );
        }
        $rating = (int)max(0, min(round($rating), 10));
        $html = html_writer::start_tag('span', array('class' => 'stars'));
        for ($i = 0; $i < 5; $i++)
            $html .= $stars[max(0, min($rating - $i * 2, 2))];
        $html .= html_writer::end_tag('span');
        return $html;
    }

    /**
     *  Checks if a URL is accessible
     *  @param string $url
     *  @return boolean
     */
    private static function is_url_accessible($url)
    {
        $response = download_file_content($url, null, null, true);
        return $response && $response->status == 200;
    }
}
