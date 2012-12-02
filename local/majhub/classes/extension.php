<?php
/**
 *  MAJ Hub
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: extension.php 149 2012-12-01 09:43:18Z malu $
 */
namespace majhub;

require_once __DIR__.'/courseware.php';

/**
 *  MAJ Hub extension
 */
class extension
{
    /**
     *  Gets a special information from a courseware record
     *  
     *  @param object $record
     *  @return string|null
     */
    public function get_courseware_info($record)
    {
        return null;
    }

    /**
     *  Gets all extensions
     *  
     *  @global \moodle_database $DB
     *  @return extension[]
     */
    public static function all()
    {
        global $DB;

        static $extensions = null;
        if ($extensions === null) {
            $extensions = array();
            foreach ($DB->get_records('majhub_courseware_extensions') as $record) {
                if (preg_match('/^(block)_majhub_(\w+)$/', $record->pluginname, $m)) {
                    list (, $type, $name) = $m;
                    switch ($type) {
                    case 'block':
                        require_once __DIR__."/../../../blocks/majhub_{$name}/classes/extension.php";
                        $reflector = new \ReflectionClass("\\majhub\\{$name}\\extension");
                        $extensions[] = $reflector->newInstance();
                        break;
                    }
                }
            }
        }
        return $extensions;
    }
}
