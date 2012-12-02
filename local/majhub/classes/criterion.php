<?php
/**
 *  MAJ Hub
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: criterion.php 104 2012-11-23 04:59:04Z malu $
 */
namespace majhub;

/**
 *  SQL criterion
 */
class criterion
{
    /** @var string */
    public $select;
    /** @var string[] */
    public $params;

    /**
     *  Joins criteria
     *  
     *  @param string      $andor     'AND' | 'OR'
     *  @param criterion[] $criteria
     *  @return criterion
     */
    public static function join($andor, array $criteria)
    {
        if (empty($criteria))
            return self::everything();
        $selects = array();
        $params  = array();
        foreach ($criteria as $criterion) {
            $selects[] = $criterion->select;
            $params = array_merge($params, $criterion->params);
        }
        $criterion = new criterion;
        $criterion->select = '(' . implode(" $andor ", $selects) . ')';
        $criterion->params = $params;
        return $criterion;
    }

    /**
     *  Creates a criterion for text input
     *  
     *  @global \moodle_database $DB
     *  @param string $fieldname
     *  @param string $formvalue
     *  @return criterion
     */
    public static function text($fieldname, $formvalue)
    {
        global $DB;

        $criterion = new criterion;
        $criterion->select = $DB->sql_like($fieldname, '?', false);
        $criterion->params = array('%' . $DB->sql_like_escape($formvalue) . '%');
        return $criterion;
    }

    /**
     *  Creates a criterion for radio buttons
     *  
     *  @global \moodle_database $DB
     *  @param string $fieldname
     *  @param string $formvalue
     *  @return criterion
     */
    public static function radio($fieldname, $formvalue)
    {
        global $DB;

        $criterion = new criterion;
        $criterion->select = $DB->sql_like($fieldname, '?', false);
        $criterion->params = array($DB->sql_like_escape($formvalue));
        return $criterion;
    }

    /**
     *  Creates a criterion for checkboxes
     *  
     *  @param string $fieldname
     *  @param string[] $formvalue
     *  @return criterion
     */
    public static function check($fieldname, array $formvalue)
    {
        $criteria = array();
        foreach ($formvalue as $option => $checked) {
            if ($checked)
                $criteria[] = self::text($fieldname, "\n" . $option . "\n");
        }
        return self::join('OR', $criteria);
    }

    /**
     *  Creates a criterion for select everything
     */
    public static function everything()
    {
        $criterion = new criterion;
        $criterion->select = '1 = 1';
        $criterion->params = array();
        return $criterion;
    }

    /**
     *  Creates a criterion for select nothing
     */
    public static function nothing()
    {
        $criterion = new criterion;
        $criterion->select = '1 = 0';
        $criterion->params = array();
        return $criterion;
    }
}
