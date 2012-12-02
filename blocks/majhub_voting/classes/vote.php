<?php
/**
 *  MAJ Hub Voting
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: vote.php 148 2012-12-01 08:37:33Z malu $
 */
namespace majhub\voting;

require_once __DIR__.'/candidate.php';

/**
 *  Best Courseware Voting Competition vote
 *  
 *  @property-read \stdClass $user
 *  @property-read candidate $candidate
 */
class vote
{
    const TABLE = 'majhub_votes';

    /** @var int */
    public $id;
    /** @var int */
    public $userid;
    /** @var int */
    public $candidateid;
    /** @var int */
    public $timecreated;

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
        case 'user':
            return $this->_cache[$name] = $DB->get_record('user', array('id' => $this->userid), '*', MUST_EXIST);
        case 'candidate':
            return $this->_cache[$name] = candidate::from_id($this->candidateid, MUST_EXIST);
        }
        throw new \InvalidArgumentException();
    }

    /**
     *  Creates an instance of a vote from a record
     *  
     *  @param object $record
     *  @return vote
     */
    public static function from_record($record)
    {
        $vote = new vote;
        $vote->id          = (int)$record->id;
        $vote->userid      = (int)$record->userid;
        $vote->candidateid = (int)$record->candidateid;
        $vote->timecreated = (int)$record->timecreated;
        return $vote;
    }

    /**
     *  Creates an instance of a vote from id
     *  
     *  @global \moodle_database $DB
     *  @param int $id
     *  @param int $strictness
     *  @return vote|null
     */
    public static function from_id($id, $strictness = IGNORE_MISSING)
    {
        global $DB;

        $record = $DB->get_record(self::TABLE, array('id' => $id), '*', $strictness);
        return $record ? self::from_record($record) : null;
    }
}
