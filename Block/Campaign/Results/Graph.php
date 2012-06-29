<?php

class Block_Campaign_Results_Graph extends Block_Campaign_Results
{
    /*
     *  Printing results page graph
     */
    public function output()
    {
        global $prefix;

        /** @var $campaign Model_Campaign */
        $campaign = Application::getSingleton('campaign');
        if($id = $campaign->getId()) {
            $title = 'Project: '.$campaign->getData('project_name');
            $subtitle = '';
        }else{
            /** @var $keyword Model_Campaign */
            $keyword = Application::getSingleton('keyword');
            $id = $keyword->getId();
            $title = 'Daily Twitter vs. Facebook';
            $subtitle = 'Keyword: '.$keyword->getData('query_q').' Language: '.$keyword->getData('query_lang');
        }

        // Printing out graph JS code
        ?>

    <script type="text/javascript" src="<?php echo $this->getUrl('js/highstock.js') ?>"></script>
    <script type="text/javascript" src="<?php echo $this->getUrl('js/modules/exporting.js') ?>"></script>
    <script type="text/javascript">

        var chart;
        jQuery(document).ready(function() {
            var options = {
                chart: { renderTo: 'container', marginTop: 57, events: {
                    redraw: function(){
                        extrems = this.xAxis[0].getExtremes();
                        theModelCarousel.reset();
                    },
                    load: function(){
                        extrems = this.xAxis[0].getExtremes();
                    }
                } },
                title: { text: '<?php echo $title ?>' },
                subtitle: { text: '<?php echo $subtitle ?>' },
                rangeSelector: { selected: 0 },
                yAxis: [{ // left y axis
                    title: { text: null },
                    labels: { align: 'left', x: 3, y: 16, formatter: function() { return Highcharts.numberFormat(this.value, 0); } },
                    showFirstLabel: false
                }, { // right y axis
                    linkedTo: 0,
                    gridLineWidth: 0,
                    opposite: true,
                    title: { text: null },
                    labels: { align: 'right', x: -3, y: 16, formatter: function() { return Highcharts.numberFormat(this.value, 0); } },
                    showFirstLabel: false
                }],
                legend: { align: 'left', verticalAlign: 'top', y: 20, floating: true, borderWidth: 0 },
                tooltip: { shared: true, crosshairs: true },
                plotOptions: { series: { marker: { lineWidth: 1 } } }
            };

            <?php

            // Preparing list of query IDs along with other information
            if(isset($campaign)){
                $results_cnt = $query_names = array();
                $query_ids = $campaign->getQueryIds($results_cnt, $query_names);
            }else{
                $query_ids = (int)$id;
                $results_cnt = array('facebook' => array(), 'twitter' => array());
            }

            // Declaring empty obkect for each series
            echo 'options.series = ['.str_repeat('{},', count($results_cnt)).'];'."\n";

            // Fetching "published" info for each entire of the query in given date range
            $query = 'SELECT search_source `source`, search_published `date`, query_id
     	        FROM '.$prefix.'search
       	       WHERE query_id IN ('.$query_ids.')
          	GROUP BY search_id';
            $res = mysql_query($query);

            $min_date = 0;
            $max_date = 0;
            // Counting results by day and storing in $results_cnt array
            while($obj = mysql_fetch_object($res)){
                $darr = getdate($obj->date);
                $date = mktime(0, 0, 0, $darr['mon'], $darr['mday'], $darr['year']);
                $results_cnt[isset($keyword) ? $obj->source : $obj->query_id][$date]++;
                $min_date = $min_date && $min_date < $date ? $min_date : $date;
                $max_date = $max_date && $max_date > $date ? $max_date : $date;
            }

            $query = 'SELECT * FROM '.$prefix.'search_index WHERE query_id IN ('.$query_ids.')';
            $res = mysql_query($query);

            while($obj = mysql_fetch_object($res)){
                $results_cnt[isset($keyword) ? $obj->index_source : $obj->query_id][$obj->index_date] += $obj->index_count;
                $min_date = isset($min_date) && $min_date < $obj->index_date ? $min_date : $obj->index_date;
                $max_date = isset($max_date) && $max_date > $obj->index_date ? $max_date : $obj->index_date;
            }

            // Printing out JS code that fill each series data array
            $series_cnt = 0;
            while(list($key, $val) = each($results_cnt)){
                echo 'options.series['.$series_cnt.'].data = [';
                for($date = $min_date; $date <= $max_date; $date += 86400){
                    echo '['.($date*1000).', '.(int)$val[$date].']'.($date+86400 > $max_date ? '];'."\n" : ',');
                }
                $name = isset($query_names) ? $query_names[$key] : $key;
                echo 'options.series['.$series_cnt++.'].name = "'.$name.'";'."\n";
                echo 'active_queries["'.$name.'"] = "'.$key.'";'."\n";
            }

            ?>
            Highcharts.setOptions({global:{useUTC:false}});
            chart = new Highcharts.StockChart(options);

            <?php if(!isset($_COOKIE['msg_legend_hide'])) : ?>
                jQuery('#container').prepend('<div id="message-container"><table class="popup" cellspacing="0" cellpadding="0"><tr><td id="topleft" class="corner"></td><td class="top"></td><td id="topright" class="corner"></td></tr><tr><td class="bg-left"></td><td class="popup-contents">Click on legend labels<br />to change graph view.<p class="hide-note">Click on this cloud to hide.</p></td><td class="bg-right"><img width="30" height="29" alt="popup tail" src="images/bubble-tail.png"/></td></tr><tr><td class="corner" id="bottomleft"></td><td class="bottom"></td><td id="bottomright" class="corner"></td></tr></tbody></table></div>');
                setTimeout(function () { jQuery('.popup').animate({ opacity:'toggle', left:'-=10px' }, 250, 'swing'); }, 3000);
                jQuery('.popup').live('click', function() {
                    jQuery(this).animate({ opacity:'toggle', left:'+=10px' }, 250, 'swing');
                    document.cookie = 'msg_legend_hide=1; expires=Thu, 1 Dec <?php echo date('Y') + 1 ?> 20:00:00 UTC; path=/'
                });
                <?php endif; ?>

        });

    </script>
    <div id="container" style="width:650px; height:400px; margin:0 auto"></div>
    <?php

    }
}