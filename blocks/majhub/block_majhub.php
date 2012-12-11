<?php

require_once __DIR__.'/../../local/majhub/classes/courseware.php';

/**
 *  MAJ Hub block
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: block_majhub.php 166 2012-12-05 05:54:33Z malu $
 */
class block_majhub extends block_base
{
    const MAX_REVIEWS = 3;

    public function init()
    {
        $this->title   = get_string('blocktitle', __CLASS__);
        $this->version = 2012120100;
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

        //$isadmin = has_capability('moodle/site:config', context_system::instance());
        $isowner = $courseware->userid == $USER->id;
        $editing = $isowner && optional_param('editmetadata', 0, PARAM_INT);

        if ($isowner && optional_param('updatemetadata', null, PARAM_TEXT)) {
            $demourl = optional_param('demourl', null, PARAM_TEXT);
            if (empty($demourl)) {
                $courseware->demourl = null;
            } elseif (self::is_url_accessible($demourl)) {
                $courseware->demourl = $demourl;
            }
            if (isset($_POST['metadata']) && is_array($_POST['metadata'])) {
                $values = $_POST['metadata'];
                foreach ($courseware->metadata as $metadatum) {
                    if (isset($values[$metadatum->id])) {
                        $metadatum->set_form_value($values[$metadatum->id]);
                    }
                }
            }
            $courseware->update();
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

        $dateformat = get_string('strftimedateshort', 'langconfig');
        $fixedrows = array(
            get_string('title', 'local_majhub')       => $courseware->fullname,
            get_string('contributor', 'local_majhub') => fullname($courseware->user),
            get_string('uploadedat', 'local_majhub')  => userdate($courseware->timerestored, $dateformat),
            get_string('filesize', 'local_majhub')    => display_size($courseware->filesize),
        //  get_string('version', 'local_majhub')     => $courseware->version,
            );

        if ($isowner) {
            $html .= html_writer::tag('div',
                $OUTPUT->action_icon(
                    new moodle_url($this->page->url, array('editmetadata' => 1)),
                    new pix_icon('i/edit', get_string('edit'))
                    ),
                array('class' => 'editmetadata')
                );
        }
        if ($editing) {
            $html .= html_writer::start_tag('form',
                array('action' => $this->page->url, 'method' => 'post', 'class' => 'mform')
                );
            $html .= self::render_input('id', $this->page->url->param('id'), 'hidden');
        }
        $html .= html_writer::start_tag('table', array('class' => 'metadata'));
        foreach ($fixedrows as $name => $value) {
            $html .= self::render_row($name, $value);
        }
        $html .= self::render_row(get_string('demourl', 'local_majhub'),
            $editing ? self::render_input('demourl', $courseware->demourl) : self::render_url($courseware->demourl)
            );
        foreach ($courseware->metadata as $metadatum) {
            $name = $metadatum->name;
            $attr = null;
            if ($editing && $metadatum->required) {
                $attr = array('class' => 'required');
                $name = $name . $OUTPUT->pix_icon('req', get_string('required'), '', array('class' => 'req'));
            } elseif ($metadatum->optional) {
                $attr = array('class' => 'optional');
            }
            $value = $editing ? $metadatum->render_form_element('metadata', '<br />') : $metadatum->render(', ');
            $html .= self::render_row($name, $value, $attr);
        }
        if ($editing) {
            $html .= self::render_row('', self::render_input('updatemetadata', get_string('update'), 'submit'));
        }
        $html .= self::render_row(get_string('rating', 'local_majhub'), self::render_rating($courseware->rating));
        $html .= html_writer::end_tag('table');
        if ($editing) {
            $html .= html_writer::end_tag('form');
        }

        // download link
        $html .= html_writer::tag('div',
            html_writer::link(new moodle_url('/local/majhub/download.php', array('id' => $courseware->id)),
                $OUTPUT->pix_icon('t/download', '') . get_string('download')
                ),
            array('class' => 'action download', 'title' => get_string('downloadthiscourseware', 'local_majhub'))
            );

        $html .= html_writer::empty_tag('hr');

        // reviews
        $reviews = $DB->get_records_sql(
            'SELECT r.*, u.firstname, u.lastname FROM {majhub_courseware_reviews} r JOIN {user} u ON u.id = r.userid
             WHERE r.coursewareid = :coursewareid ORDER BY r.timemodified DESC',
            array('coursewareid' => $courseware->id),
            0, optional_param('showallreviews', 0, PARAM_INT) ? 0 : self::MAX_REVIEWS);
        $reviewed = array_reduce($reviews, function ($f, $r) use ($USER) { return $f || $r->userid == $USER->id; });
        $html .= html_writer::start_tag('div', array('class' => 'reviews'));
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
        foreach ($reviews as $review) {
            $html .= html_writer::start_tag('div', array('class' => 'review'));
            $html .= html_writer::tag('div', self::render_rating($review->rating) . ' ' . fullname($review));
            $html .= html_writer::tag('div', nl2br(clean_text($review->comment)));
            $html .= html_writer::end_tag('div');
        }
        $html .= html_writer::end_tag('div');

        $this->page->requires->js_init_call('M.block_majhub.init');
        $this->page->requires->string_for_js('optionalfields', 'local_majhub');

        return $this->content = (object)array('text' => $html);
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
     *  @return string
     */
    private static function render_input($name, $value, $type = 'text')
    {
        return html_writer::empty_tag('input', array('type' => $type, 'name' => $name, 'value' => $value));
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
