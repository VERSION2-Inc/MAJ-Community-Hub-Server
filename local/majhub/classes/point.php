<?php
/**
 *  MAJ Hub
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: point.php 208 2013-02-04 00:39:45Z malu $
 */
namespace majhub;

require_once __DIR__.'/setting.php';
require_once __DIR__.'/courseware.php';

/**
 *  Point
 *  
 *  @property-read int $total
 */
class point
{
    /** @var int */
    public $userid;

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
        case 'total':
            $uploadingcount = $DB->count_records(courseware::TABLE, array('userid' => $this->userid, 'deleted' => 0));
            $reviewingcount = $DB->count_records(review::TABLE, array('userid' => $this->userid));
            $popularitycount = $DB->count_records_sql(
                'SELECT COUNT(d.id) FROM {majhub_courseware_downloads} d
                 JOIN {' . courseware::TABLE . '} c ON c.id = d.coursewareid
                 WHERE c.userid = :userid AND d.userid <> c.userid',
                array('userid' => $this->userid)
                );
            $downloadingcount = $DB->count_records_sql(
                'SELECT COUNT(d.id) FROM {majhub_courseware_downloads} d
                 JOIN {' . courseware::TABLE . '} c ON c.id = d.coursewareid
                 WHERE d.userid = :userid AND c.userid <> d.userid',
                array('userid' => $this->userid)
                );
            $bonuspoints = $DB->count_records_sql(
                'SELECT SUM(b.points) FROM {majhub_bonus_points} b
                 JOIN {' . courseware::TABLE . '} c ON c.id = b.coursewareid
                 WHERE c.userid = :userid',
                array('userid' => $this->userid)
                );
            $settings = self::get_settings();
            $total  = $settings->pointsforregistration;
            $total += $settings->pointsforuploading * $uploadingcount;
            $total += $settings->pointsforreviewing * $reviewingcount;
            if ($popularitycount >= $settings->countforpopularity) {
                $total += $settings->pointsforpopularity;
            }
            $total += $bonuspoints;
            $total -= $settings->pointsfordownloading * $downloadingcount;
            return $this->_cache[$name] = $total;
        }
        throw new \InvalidArgumentException();
    }

    /**
     *  Creates an instance of a point from userid
     *  
     *  @param int $userid
     *  @return point
     */
    public static function from_userid($userid)
    {
        $point = new point;
        $point->userid = $userid;
        return $point;
    }

    /**
     *  Gets the point system settings
     *  
     *  @return pointsettings
     */
    public static function get_settings()
    {
        static $settings = null;
        if ($settings === null) {
            $settings = new pointsettings;
            foreach (get_object_vars($settings) as $name => $default)
                $settings->{$name} = setting::get($name, $default);
        }
        return $settings;
    }
}

/**
 *  Point system settings
 */
class pointsettings
{
    /** @var int */
    public $pointsforregistration = 10;
    /** @var int */
    public $pointsforuploading    = 20;
    /** @var int */
    public $pointsforreviewing    = 5;
    /** @var int */
    public $pointsforquality      = 20;
    /** @var int */
    public $pointsforpopularity   = 20;
    /** @var int */
    public $countforpopularity    = 10;
    /** @var int */
    public $lengthforreviewing    = 100;
    /** @var int */
    public $pointsfordownloading  = 10;
}
