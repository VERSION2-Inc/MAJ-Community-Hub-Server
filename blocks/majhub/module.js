/**
 *  MAJ Hub block
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: module.js 105 2012-11-23 08:02:16Z malu $
 */
YUI.add('block_majhub', function (Y)
{
    M.block_majhub = new function ()
    {
        /** @var {Node}  The MAJ Hub block container node */
        var $block = Y.Node.one('.block_majhub');

        /**
         *  Initialize
         *  
         *  @param {YUI} Y
         */
        this.init = function (Y)
        {
            if (/editmetadata=1/.exec(location.search)) {
                $block.one('.metadata input').focus();
            } else if (/editreview=1/.exec(location.search)) {
                $block.one('.reviews textarea').focus();
            }
        }
    }
}, '2.3 dev', { requires: [ 'base', 'node', 'io', 'dom' ] });
