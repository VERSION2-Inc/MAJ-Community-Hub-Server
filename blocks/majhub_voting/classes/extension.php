<?php
/**
 *  MAJ Hub Voting
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: extension.php 150 2012-12-01 09:43:30Z malu $
 */
namespace majhub\voting;

require_once __DIR__.'/../../../local/majhub/classes/extension.php';
require_once __DIR__.'/candidate.php';

/**
 *  MAJ Hub extension
 */
class extension extends \majhub\extension
{
    /**
     *  Gets a special information from a courseware record
     *  
     *  @global \core_renderer $OUTPUT
     *  @param object $record
     *  @return string|null
     */
    public function get_courseware_info($record)
    {
        global $OUTPUT;

        if (candidate::from_coursewareid($record->id)) {
            static $strcandidate = null;
            if ($strcandidate === null) {
                $icon = $OUTPUT->pix_icon('i/tick_green_big', '', '', array('style' => 'margin:-1px 2px -4px 0;'));
                $strcandidate = $icon . \get_string('candidate', 'block_majhub_voting');
            }
            return $strcandidate;
        }
        return null;
    }
}
