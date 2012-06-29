<?php

class Block_Campaign_Results_Wire extends Block_Campaign_Results
{
    public function output()
    {
        /** @var $campaign Model_Campaign */
        $campaign = Application::getSingleton('campaign');
        if(!$id = $campaign->getId()) {
            /** @var $keyword Model_Campaign */
            $keyword = Application::getSingleton('keyword');
            $id = $keyword->getId();
        }

        ?>
    <script type="text/javascript" src="<?php echo $this->getUrl('js/jquery.jcarousel.min.js') ?>"></script>
    <script type="text/javascript">

        var theModelCarousel = null;
        var active_queries = new Array;
        var exclude_series = new Array;

        function mycarousel_itemLoadCallback(carousel, state){
            theModelCarousel = carousel;

            jQuery.get(
                '<?php echo $this->getUrl('projects/wirexml') ?>',
                {
                    ids: "<?php echo urlencode(isset($campaign) ? $campaign->getQueryIds() : (int)$id) ?>",
                    first: carousel.first,
                    last: carousel.last,
                    from: extrems.min/1000,
                    to: extrems.max/1000
                },
                function(xml) {
                    carousel.size(parseInt(jQuery('total', xml).text()));
                    jQuery('node', xml).each(function(i) {
                        carousel.add(carousel.first + i, '<div class="results-container' + ($('source', this).text() == 'facebook' ? ' facebook' : '') + '"><div class="left"><a href="' + $('link', this).text() + '" target="_blank" title="' + $('author', this).text() + '" onclick="blur();"><img src="' + $('image', this).text() + '" alt="' + $('author', this).text() + '" /></a></div><div class="left msg-text"><strong>' + $('author', this).text() + '</strong><div class="date">' + $('date', this).text() + '</div>' + $('content', this).text().replace(/&amp;/, '&') + '</div><div class="clear"></div></div>');
                    });
                },
                'xml'
            );
        }

        jQuery(document).ready(function(){
            jQuery('#mycarousel').jcarousel({
                vertical: true,
                scroll: 10,
                itemLoadCallback: mycarousel_itemLoadCallback
            });
        });

    </script>

    <h3>Raw Stream</h3>
    <div id="mycarousel" class="jcarousel-skin-tango"><ul><!-- The content will be dynamically loaded in here --></ul></div>

    <?php


    }
}