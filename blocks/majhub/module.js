/**
 *  MAJ Hub block
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: module.js 160 2012-12-03 06:22:35Z malu $
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

            var $metadata = $block.one('table.metadata');
            var $firstoptional = $metadata.one('.optional');
            if ($firstoptional) {
                var src = {
                    'collapsed': $block.one('img.collapsed').get('src'),
                    'expanded' : $block.one('img.expanded').get('src')
                };
                var str = {
                    'optionalfields': M.str.local_majhub['optionalfields']
                };
                var $wrapper = Y.Node.create('<tr/>');
                var $togglebutton = Y.Node.create('<td colspan="2" class="toggle"/>')
                    .append('<img/>').append(Y.Node.create('<span/>').set('text', str['optionalfields']));
                var $optionalfields = $metadata.all('.optional');
                function showoptionalfields()
                {
                    $optionalfields.show();
                    $togglebutton.one('img').set('src', src['expanded']);
                }
                function hideoptionalfields()
                {
                    $optionalfields.hide();
                    $togglebutton.one('img').set('src', src['collapsed']);
                }
                $togglebutton.on('click', function ()
                {
                    if (this.one('img').get('src') == src['collapsed']) {
                        showoptionalfields();
                    } else {
                        hideoptionalfields();
                    }
                });
                $firstoptional.ancestor().insertBefore($wrapper.append($togglebutton), $firstoptional);
                hideoptionalfields();
            }
        }
    }
}, '2.3, release candidate 1', { requires: [ 'base', 'node', 'io', 'dom' ] });
