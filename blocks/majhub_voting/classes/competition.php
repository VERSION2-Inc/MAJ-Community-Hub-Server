<?php
/**
 *  MAJ Hub Voting
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: competition.php 161 2012-12-03 07:03:22Z malu $
 */
namespace majhub\voting;

require_once __DIR__.'/../../../local/majhub/classes/courseware.php';
require_once __DIR__.'/candidate.php';

use majhub\courseware;

/**
 *  Best Courseware Voting Competition
 *  
 *  @property-read candidate[] $candidates
 */
class competition
{
    const TABLE = 'majhub_competitions';

    /** @var int */
    public $id;
    /** @var string */
    public $title;
    /** @var string */
    public $description;
    /** @var int */
    public $timestart;
    /** @var int */
    public $timeend;
    /** @var int */
    public $timecreated;
    /** @var int */
    public $timemodified;

    /** @var array */
    private $_cache;

    /**
     *  Constructor
     */
    public function __construct()
    {
        $this->_cache = array();
    }

    /**
     *  Gets a dymanic property
     *  
     *  @param string $name
     *  @return mixed
     */
    public function __get($name)
    {
        if (isset($this->_cache[$name]))
            return $this->_cache[$name];

        switch ($name) {
        case 'candidates':
            return $this->_cache[$name] = new \ArrayIterator($this->get_candidates());
        }
        throw new \InvalidArgumentException();
    }

    /**
     *  Gets competition candidates
     *  
     *  @global \moodle_database $DB
     *  @param int $limit = 0
     *  @param string $sort = 'votes_count DESC, timemodified ASC'
     *  @return candidate[]
     *  @throws \moodle_exception
     */
    public function get_candidates($limit = 0, $sort = 'votes_count DESC, timecreated ASC')
    {
        global $DB;

        $records = $DB->get_records_sql(
            'SELECT c.id, c.competitionid, c.coursewareid, c.timecreated, c.timemodified, COUNT(v.id) AS votes_count
             FROM {' . candidate::TABLE . '} c
             LEFT JOIN {' . vote::TABLE . '} v ON v.candidateid = c.id
             WHERE c.competitionid = :competitionid
             GROUP BY c.id, c.competitionid, c.coursewareid, c.timecreated, c.timemodified
             ORDER BY ' . $sort,
            array('competitionid' => $this->id), 0, $limit);
        return array_map(function ($record) { return candidate::from_record($record); }, $records);
    }

    /**
     *  Nominates a courseware
     *  
     *  @global \moodle_database $DB
     *  @param courseware $courseware
     *  @return candidate
     *  @throws \moodle_exception
     */
    public function nominate(courseware $courseware)
    {
        global $DB;

        $candidate = new candidate;
        $candidate->competitionid = $this->id;
        $candidate->coursewareid  = $courseware->id;
        $candidate->timecreated   = time();
        $candidate->timemodified  = time();
        $candidate->id = $DB->insert_record(candidate::TABLE, $candidate);
        return $candidate;
    }

    /**
     *  Gets a user vote for the competition
     *  
     *  @global \moodle_database $DB
     *  @param int $userid
     *  @return vote|null
     *  @throws \moodle_exception
     */
    public function get_vote($userid)
    {
        global $DB;

        $record = $DB->get_record_sql(
            'SELECT v.*
             FROM {' . competition::TABLE . '} c
             JOIN {' . candidate::TABLE . '} ca ON ca.competitionid = c.id
             JOIN {' . vote::TABLE . '} v ON v.candidateid = ca.id
             WHERE c.id = :competitionid AND v.userid = :userid',
            array('competitionid' => $this->id, 'userid' => $userid)
            );
        return $record ? vote::from_record($record) : null;
    }

    /**
     *  Validates
     *  
     *  @return competition
     *  @throws \moodle_exception
     */
    public function validate()
    {
        if (strlen(trim($this->title)) == 0)
            throw new \moodle_exception('error:competition:emptytitle', 'block_majhub_voting');
        if (empty($this->timestart) || empty($this->timeend) || $this->timestart >= $this->timeend)
            throw new \moodle_exception('error:competition:invalidperiod', 'block_majhub_voting');
        return $this;
    }

    /**
     *  Saves as a record
     *  
     *  @global \moodle_database $DB
     *  @return competition
     *  @throws \moodle_exception
     */
    public function save()
    {
        global $DB;

        // lazy extension installation; workaround for the database dependency
        require_once __DIR__.'/extension.php';
        \majhub\extension::install('block_majhub_voting');

        $this->validate();
        $record = new \stdClass;
        $record->id          = $this->id;
        $record->title       = trim($this->title);
        $record->description = trim($this->description);
        $record->timestart   = $this->timestart;
        $record->timeend     = $this->timeend;
        if (empty($record->id)) {
            $record->timecreated  = time();
            $record->timemodified = time();
            $this->id = $DB->insert_record(self::TABLE, $record);
        } else {
            $record->timemodified = time();
            $DB->update_record(self::TABLE, $record);
        }
        return $this;
    }

    /**
     *  Deletes the record
     *  
     *  @global \moodle_database $DB
     */
    public function delete()
    {
        global $DB;

        $DB->delete_records(self::TABLE, array('id' => $this->id));
    }

    /**
     *  Creates an instance of a competition from a record
     *  
     *  @param object $record
     *  @return competition
     */
    public static function from_record($record)
    {
        $competition = new competition;
        $competition->id           = isset($record->id) ? (int)$record->id : null;
        $competition->title        = trim($record->title);
        $competition->description  = trim($record->description);
        $competition->timestart    = (int)$record->timestart;
        $competition->timeend      = (int)$record->timeend;
        $competition->timecreated  = isset($record->timecreated)  ? (int)$record->timecreated  : null;
        $competition->timemodified = isset($record->timemodified) ? (int)$record->timemodified : null;
        return $competition;
    }

    /**
     *  Creates an instance of a competition from id
     *  
     *  @global \moodle_database $DB
     *  @param int $id
     *  @param int $strictness
     *  @return competition|null
     */
    public static function from_id($id, $strictness = IGNORE_MISSING)
    {
        global $DB;

        $record = $DB->get_record(self::TABLE, array('id' => $id), '*', $strictness);
        return $record ? self::from_record($record) : null;
    }

    /**
     *  Gets all the competitions nomination accepting
     *  
     *  @global \moodle_database $DB
     *  @return competition[]
     */
    public static function acceptings()
    {
        global $DB;

        $records = $DB->get_records_select(self::TABLE, 'timeend > ?', array(time()), 'timestart ASC');
        return array_reduce($records, function ($map, $record)
        {
            $competition = competition::from_record($record);
            $map[$competition->id] = $competition;
            return $map;
        }, array());
    }
}
