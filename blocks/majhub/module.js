/**
 *  MAJ Hub block
 *  
 *  @author  VERSION2, Inc. (http://ver2.jp)
 *  @version $Id: module.js 166 2012-12-05 05:54:33Z malu $
 */
YUI.add('block_majhub', function (Y)
{
    M.block_majhub = new function ()
    {
        /** @var {Node}  The MAJ Hub block container node */
        var $block = Y.Node.one('.block_majhub');

        /** @var {Node}  The metadata container node */
        var $metadata = $block.one('table.metadata');

        /**
         *  Initializes required fields
         */
        function init_required_fields()
        {
            var $fields = $metadata.all('.required');
            var $update = $metadata.one('input[name="updatemetadata"]');
            if ($fields && $update) {
                $update.on('click', function (e)
                {
                    $fields.each(function ($field)
                    {
                        var $input = $field.one('input[type="text"],input:checked');
                        if (!$input || $input.get('value').length == 0) {
                            $field.setStyle('border', '1px dashed red');
                            e.preventDefault();
                        } else {
                            $field.setStyle('border', 'none');
                        }
                    });
                });
            }
        }

        /**
         *  Initializes optional fields
         */
        function init_optional_fields()
        {
            var $first = $metadata.one('.optional');
            if (!$first)
                return;

            var pix = {
                'collapsed': M.util.image_url('t/collapsed'),
                'expanded' : M.util.image_url('t/expanded')
            };
            var $fields = $metadata.all('.optional');
            var $button = Y.Node.create('<tr class="toggle"/>').append(
                Y.Node.create('<td colspan="2"/>')
                    .append('<img/>')
                    .append(Y.Node.create('<span/>').set('text', M.str.local_majhub['optionalfields']))
                );
            function show()
            {
                $fields.show();
                $button.one('img').set('src', pix['expanded']);
            }
            function hide()
            {
                $fields.hide();
                $button.one('img').set('src', pix['collapsed']);
            }
            $button.on('click', function ()
            {
                if (this.one('img').get('src') == pix['collapsed'])
                    show();
                else
                    hide();
            });
            $first.ancestor().insertBefore($button, $first);
            hide();
        }

        /**
         *  Initializes the MAJ Hub block
         *  
         *  @param {YUI} Y
         */
        this.init = function (Y)
        {
            init_required_fields();
            init_optional_fields();

            if (/editmetadata=1/.exec(location.search)) {
                $block.one('.metadata input').focus();
            } else if (/editreview=1/.exec(location.search)) {
                $block.one('.reviews textarea').focus();
            }
        }
    }
}, '2.3, release candidate 1', { requires: [ 'base', 'node', 'io', 'dom' ] });
