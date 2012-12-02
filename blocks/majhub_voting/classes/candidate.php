<?php
/**
 *  MAJ Hub Voting
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: candidate.php 148 2012-12-01 08:37:33Z malu $
 */
namespace majhub\voting;

require_once __DIR__.'/../../../local/majhub/classes/courseware.php';
require_once __DIR__.'/competition.php';
require_once __DIR__.'/vote.php';

use majhub\courseware;

/**
 *  Best Courseware Voting Competition candidate
 *  
 *  @property-read competition $competition
 *  @property-read courseware $courseware
 *  @property-read int $votes_count
 *  @property-read vote[] $votes
 */
class candidate
{
    const TABLE = 'majhub_candidates';

    /** @var int */
    public $id;
    /** @var int */
    public $competitionid;
    /** @var int */
    public $coursewareid;
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
     *  @global \moodle_database $DB
     *  @param string $name
     *  @return mixed
     */
    public function __get($name)
    {
        global $DB;

        if (isset($this->_cache[$name]))
            return $this->_cache[$name];

        switch ($name) {
        case 'competition':
            return $this->_cache[$name] = competition::from_id($this->competitionid, MUST_EXIST);
        case 'courseware':
            return $this->_cache[$name] = courseware::from_id($this->coursewareid, MUST_EXIST);
        case 'votes_count':
            return $this->_cache[$name] = $DB->get_field(vote::TABLE, 'COUNT(*)', array('candidateid' => $this->id));
        case 'votes':
            return $this->_cache[$name] = new \ArrayIterator(
                array_map(
                    function ($record) { return vote::from_record($record); },
                    $DB->get_records(vote::TABLE, array('candidateid' => $this->id), 'timecreated ASC')
                    )
                );
        }
        throw new \InvalidArgumentException();
    }

    /**
     *  Votes for the candidate
     *  
     *  @param int $userid
     */
    public function vote($userid)
    {
        global $DB;

        if ($this->competition->timestart <= time() && time() <= $this->competition->timeend) {
            // TODO: transaction
            if (!$this->competition->get_vote($userid)) {
                $vote = new vote;
                $vote->userid      = $userid;
                $vote->candidateid = $this->id;
                $vote->timecreated = time();
                $DB->insert_record(vote::TABLE, $vote);
            }
        }
    }

    /**
     *  Creates an instance of a candidate from a record
     *  
     *  @param object $record
     *  @return candidate
     */
    public static function from_record($record)
    {
        $candidate = new candidate;
        $candidate->id            = (int)$record->id;
        $candidate->competitionid = (int)$record->competitionid;
        $candidate->coursewareid  = (int)$record->coursewareid;
        $candidate->timecreated   = (int)$record->timecreated;
        $candidate->timemodified  = (int)$record->timemodified;
        return $candidate;
    }

    /**
     *  Creates an instance of a candidate from id
     *  
     *  @global \moodle_database $DB
     *  @param int $id
     *  @param int $strictness
     *  @return candidate|null
     */
    public static function from_id($id, $strictness = IGNORE_MISSING)
    {
        global $DB;

        $record = $DB->get_record(self::TABLE, array('id' => $id), '*', $strictness);
        return $record ? self::from_record($record) : null;
    }

    /**
     *  Creates an instance of a candidate from coursewareid
     *  
     *  @global \moodle_database $DB
     *  @param int $coursewareid
     *  @param int $strictness
     *  @return candidate|null
     */
    public static function from_coursewareid($coursewareid, $strictness = IGNORE_MISSING)
    {
        global $DB;

        $record = $DB->get_record(self::TABLE, array('coursewareid' => $coursewareid), '*', $strictness);
        return $record ? self::from_record($record) : null;
    }
}
