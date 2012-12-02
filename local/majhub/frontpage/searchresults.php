<?php // $Id: searchresults.php 149 2012-12-01 09:43:18Z malu $

defined('MOODLE_INTERNAL') || die;

require_once __DIR__.'/../classes/criterion.php';
require_once __DIR__.'/../classes/metafield.php';
require_once __DIR__.'/../classes/extension.php';

use majhub\criterion;
use majhub\metafield;
use majhub\extension;

if (false) {
    $DB     = new mysqli_native_moodle_database;
    $OUTPUT = new core_renderer;
}

echo html_writer::start_tag('div', array('class' => 'course-content path-course-view'));

if (optional_param('search', null, PARAM_TEXT)) {
    $keywords    = preg_split('/\s+/', optional_param('keywords', '', PARAM_TEXT), -1, PREG_SPLIT_NO_EMPTY);
    $title       = optional_param('title', '', PARAM_TEXT);
    $contributor = optional_param('contributor', '', PARAM_TEXT);
    $metadata    = isset($_REQUEST['metadata']) && is_array($_REQUEST['metadata']) ? $_REQUEST['metadata'] : array();
    $sortby      = optional_param('sortby', 'newest', PARAM_TEXT);
    $offset      = max(0, optional_param('offset', 0, PARAM_INT));
    $limit       = min(optional_param('limit', 10, PARAM_INT), 100); // TODO

    $metafields = metafield::all();

    $criteria = array();
    foreach ($keywords as $keyword) {
        $keywordcriteria = array(
            criterion::text('c.fullname', $keyword), criterion::text('c.shortname', $keyword),
            criterion::text('u.firstname', $keyword), criterion::text('u.lastname', $keyword),
            );
        foreach ($metafields as $metafield) {
            $fieldname = "m{$metafield->id}.value";
            $keywordcriteria[] = criterion::text($fieldname, $keyword);
        }
        $criteria[] = criterion::join('OR', $keywordcriteria);
    }
    if (strlen($title) != 0) {
        $criteria[] = criterion::join('OR',
            array(criterion::text('c.fullname', $title), criterion::text('c.shortname', $title))
            );
    }
    if (strlen($contributor) != 0) {
        // considers "firstname lastname" or "lastname firstname" given
        $namecriteria = array();
        foreach (preg_split('/\s+/', $contributor, -1, PREG_SPLIT_NO_EMPTY) as $name) {
            $namecriteria[] = criterion::join('OR',
                array(criterion::text('u.firstname', $name), criterion::text('u.lastname', $name))
                );
        }
        $criteria[] = criterion::join('AND', $namecriteria);
    }
    foreach ($metafields as $metafield) {
        if (isset($metadata[$metafield->id])) {
            $fieldname = "m{$metafield->id}.value";
            switch ($metafield->type) {
            case metafield::TYPE_TEXT:
                if (strlen($value = trim($metadata[$metafield->id])) != 0) {
                    $criteria[] = criterion::text($fieldname, $value);
                }
                break;
            case metafield::TYPE_RADIO:
                if (strlen($metadata[$metafield->id]) != 0) {
                    $criteria[] = criterion::radio($fieldname, $metadata[$metafield->id]);
                }
                break;
            case metafield::TYPE_CHECK:
                $criteria[] = criterion::check($fieldname, $metadata[$metafield->id]);
                break;
            }
        }
    }
    $criterion = criterion::join('AND', $criteria);

    $sql = 'SELECT c.id, c.fullname, c.courseid, c.demourl, c.timeuploaded, u.firstname, u.lastname,
                   (SELECT AVG(r.rating) FROM {majhub_courseware_reviews} r WHERE r.coursewareid = c.id) AS rating,
                   (SELECT COUNT(*) FROM {majhub_courseware_reviews} r WHERE r.coursewareid = c.id) AS num_reviews
            FROM {majhub_coursewares} c
            JOIN {user} u ON u.id = c.userid';
    foreach ($metafields as $metafield) {
        $sql .= " LEFT JOIN {majhub_courseware_metadata} m{$metafield->id}
                         ON m{$metafield->id}.coursewareid = c.id AND m{$metafield->id}.metafieldid = $metafield->id";
    }
    $sql .= ' WHERE c.courseid IS NOT NULL AND ' . $criterion->select;
    $sql .= ' GROUP BY c.id, c.fullname, c.courseid, c.demourl, c.timeuploaded, u.firstname, u.lastname';
    switch ($sortby) {
    case 'newest': $sql .= ' ORDER BY timeuploaded DESC'; break;
    case 'oldest': $sql .= ' ORDER BY timeuploaded ASC'; break;
    case 'title' : $sql .= ' ORDER BY fullname ASC'; break;
    case 'rating': $sql .= ' ORDER BY rating DESC, num_reviews DESC'; break;
    case 'contributor':
        $fullnamefields = fullname((object)array('firstname' => 'firstname', 'lastname' => 'lastname'));
        if (preg_match_all('/firstname|lastname/', $fullnamefields, $matches, PREG_PATTERN_ORDER)) {
            $sql .= ' ORDER BY ' . implode(', ', $matches[0]);
        }
        break;
    }
    $coursewares = $DB->get_records_sql($sql, $criterion->params, $offset, $limit);
    $coursewares_count = $DB->count_records_sql("SELECT COUNT(*) FROM ($sql) _tmp", $criterion->params);

    $strpreview  = get_string('preview', 'local_majhub');
    $strdownload = get_string('download', 'local_majhub');
    $strdemosite = get_string('demosite', 'local_majhub');
    $strpreviewtip  = get_string('previewthiscourseware', 'local_majhub');
    $strdownloadtip = get_string('downloadthiscourseware', 'local_majhub');
    $strdemositetip = get_string('visitauthorsdemosite', 'local_majhub');

    $previewicon   = $OUTPUT->pix_icon('t/preview', '', '', array('style' => 'margin:0 2px 0 0;'));
    $downloadicon  = $OUTPUT->pix_icon('t/download', '', '', array('style' => 'margin:-1px 2px -4px 0;'));
    $visitdemoicon = $OUTPUT->pix_icon('i/moodle_host', '', '', array('style' => 'margin:-1px 2px -4px 0;'));

    $buttonstyle = 'float:left; margin:0.5em 0.5em 0.5em 0; width:8em; height:24px; line-height:24px;'
                 . ' text-align:center; border:1px solid silver; border-radius:5px; box-shadow:1px 1px 2px silver;';
    $infostyle = 'float:left; margin:0.5em; width:8em; height:24px; line-height:24px; text-align:center;';

    echo html_writer::start_tag('ul', array('class' => 'topics'));
    if (empty($coursewares)) {
        echo html_writer::start_tag('li', array('class' => 'section main clearfix'));
        {
            echo html_writer::tag('div', get_string('noresult', 'local_majhub'), array('class' => 'content'));
        }
        echo html_writer::end_tag('li');
    } else foreach ($coursewares as $courseware) {
        $previewurl = new moodle_url('/course/view.php', array('id' => $courseware->courseid));
        $downloadurl = new moodle_url('/local/majhub/download.php', array('id' => $courseware->id));
        echo html_writer::start_tag('li', array('class' => 'section main clearfix'));
        {
            // special informations and action buttons
            echo html_writer::start_tag('div', array('style' => 'float:right; white-space:nowrap;'));
            foreach (extension::all() as $extension) {
                if ($info = $extension->get_courseware_info($courseware)) {
                    echo html_writer::tag('div', $info, array('style' => $infostyle));
                }
            }
            echo html_writer::tag('div',
                html_writer::link($previewurl, $previewicon . $strpreview, array('title' => $strpreviewtip)),
                array('style' => $buttonstyle)
                );
            echo html_writer::tag('div',
                html_writer::link($downloadurl, $downloadicon . $strdownload, array('title' => $strdownloadtip)),
                array('style' => $buttonstyle)
                );
            if (empty($courseware->demourl)) {
                echo html_writer::tag('div', $strdemosite, array('style' => $buttonstyle . ' color:silver;'));
            } else {
                echo html_writer::tag('div',
                    html_writer::link($courseware->demourl, $visitdemoicon . $strdemosite, array('title' => $strdemositetip)),
                    array('style' => $buttonstyle)
                    );
            }
            echo html_writer::end_tag('div');

            // courseware infomation
            echo html_writer::start_tag('div', array('class' => 'content', 'style' => 'margin:0 20px;'));
            echo html_writer::tag('h4', $courseware->fullname, array('class' => 'sectionname'));
            echo html_writer::start_tag('div', array('style' => 'margin:2px 0 0 10px; color:gray;'));
            echo html_writer::tag('span', get_string('contributor', 'local_majhub') . ': ' . fullname($courseware));
            // TODO: show more details
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');
        }
        echo html_writer::end_tag('li');
    }
    echo html_writer::end_tag('ul');

    if ($offset + count($coursewares) < $coursewares_count) {
        // TODO: pager
    }
}

echo html_writer::end_tag('div');
