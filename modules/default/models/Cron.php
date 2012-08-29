<?php

class Default_Model_Cron
{
    /**
     * Grabs Twitter and Facebook entries
     *
     * @param $bootstrap Bootstrap
     */
    public function run($bootstrap)
    {
        $active_queries = array();
        $campaign = new Default_Model_DbTable_Campaigns();
        $keyword = new Default_Model_DbTable_Keyword();
        $search = new Default_Model_DbTable_Search();
        $influencer = new Default_Model_DbTable_Influencers();
        $settings = $bootstrap->getOption('settings');

        foreach ($campaign->getCronData() as $obj) {
            $active_queries[] = $obj['query_id'];

            /**
             *  Twitter
             */
            $last_tweet_id = 0;
            $base_url = 'http://search.twitter.com/search.json';
            $parameters = array(
                'q'             => $obj['query_q'],
                'geocode'       => $obj['query_geocode'],
                'rpp'           => 100,
                'result_type'   => 'recent',
                'since_id'      => $obj['query_last_twitter'],
                'lang'          => $obj['query_lang']
            );
            $response = $this->_get_file_contents($base_url, $parameters, true);
            while(is_object($response) && isset($response->results) && is_array($response->results) && list(,$entry) = each($response->results)) {
                if(!$last_tweet_id){
                    $last_tweet_id = $entry->id_str;
                    $data = array('query_last_twitter' => $last_tweet_id);
                    $keyword->update($data, array('query_id = ?' => $obj['query_id']));
                }

                $data = array(
                    'query_id'              => $obj['query_id'],
                    'search_outer_id'       => $entry->id_str,
                    'search_source'         => 'twitter',
                    'search_published'      => strtotime($entry->created_at),
                    'search_content'        => addslashes($entry->text),
                    'search_author_name'    => addslashes($entry->from_user),
                    'search_author_image'   => $entry->profile_image_url
                );
                $search->insert($data);

                $data = array(
                    'query_id'              => $obj['query_id'],
                    'search_author_name'    => addslashes($entry->from_user),
                    'search_source'         => 'twitter',
                    'cnt'                   => 1
                );
                $influencer->insertOnDuplicate($data, 'UPDATE cnt = cnt + 1');
            }

            /**
             *  Facebook
             */
            $last_facebook_post_time = 0;
            $base_url = 'https://graph.facebook.com/search';
            $parameters = array(
                'q'     => $obj['query_q'],
                'type'  => 'post',
                'limit' => 100,
                'since' => $obj['query_last_facebook']
            );
            $response = $this->_get_file_contents($base_url, $parameters, true);
            $alchemy_base_url = 'http://access.alchemyapi.com/calls/text/TextGetLanguage';
            $lang = array();
            while (is_object($response) && isset($response->data) && is_array($response->data) && list(,$entry) = each($response->data)) {
                $text = $entry->message ? $entry->message : $entry->story;
                if($obj['query_lang'] && $settings['alchemy_api_key']){
                    $parameters = array(
                        'apikey'        => $settings['alchemy_api_key'],
                        'outputMode'    => 'json',
                        'text'          => $text
                    );
                    $lang = $this->_get_file_contents($alchemy_base_url, $parameters, true, true);
                }
                if (!$obj['query_lang'] || !$settings['alchemy_api_key'] || ($obj['query_lang'] && $obj['query_lang'] == $lang['iso-639-1'])) {
                    $created_time = strtotime($entry->created_time);
                    if (!$last_facebook_post_time) {
                        $last_facebook_post_time = date('n/j/Y H:i:s', $created_time);
                        $data = array('query_last_facebook' => $last_facebook_post_time);
                        $keyword->update($data, array('query_id = ?' => $obj['query_id']));
                    }

                    $data = array(
                        'query_id'              => $obj['query_id'],
                        'search_outer_id'       => $entry->id,
                        'search_source'         => 'facebook',
                        'search_published'      => $created_time,
                        'search_content'        => addslashes($text),
                        'search_author_name'    => addslashes($entry->from->name),
                        'search_title'          => addslashes($entry->name)
                    );
                    $search->insert($data);

                    $data = array(
                        'query_id'              => $obj['query_id'],
                        'search_author_name'    => addslashes($entry->from->name),
                        'search_source'         => 'facebook',
                        'cnt'                   => 1
                    );
                    $influencer->insertOnDuplicate($data, 'UPDATE cnt = cnt + 1');
                }
            }
        }

        // Archiving expired entries.
        $expired_entries = $search->fetchAll(array(
            'query_id IN (?)' => implode(', ', $active_queries),
            'search_published < ?' => time() - $settings['keep_history'] * 24 * 3600
        ));
        if (count($expired_entries)) {
            $search_index = new Default_Model_DbTable_SearchIndex();
            echo 'Archiving ' . count($expired_entries) . ' entries.'."\n";
            foreach ($expired_entries as $a_obj) {
                echo '.';
                $date = mktime(0, 0, 0, date('n', $a_obj['search_published']), date('j', $a_obj['search_published']), date('Y', $a_obj['search_published']));
                $data = array(
                    'query_id'      => $a_obj['query_id'],
                    'index_date'    => $date,
                    'index_source'  => $a_obj['search_source'],
                    'index_count'   => 1
                );
                $search_index->insertOnDuplicate($data, 'UPDATE index_count = index_count+1');
                $search->delete(array('search_id' => $a_obj['search_id']));
            }
        }
        $search->optimize();
        echo "\n";

        if(!(int)date('G')){ // If hour == 0, sending email digest
            $mail = new Zend_Mail();
            $mail->addTo($bootstrap->getOption('authentication.email'))
                ->setFrom($settings['default_from'])
                ->setSubject('Daily overview of your projects');

            $email_html = '<html><body>logo here<hr/><br/>Project(s) in this email:<br/><ul>';
            foreach ($campaign->getCampaigns() as $cmpgn) {
                $email_html .= '<li><a href="#' . $cmpgn['project_id'] . '">' . $cmpgn['project_name'] . '</a></li>';
            }
            $email_html .= '</ul><br/><br/>';
            foreach ($campaign->getCampaigns() as $cmpgn) {
                $email_html .= '<a name="' . $cmpgn['project_id'] . '"></a>
                    Project: ' . $cmpgn['project_name'] . '<hr/>' .
                    $this->breakdown_block($cmpgn['project_id'], 'twitter') .
                    $this->breakdown_block($cmpgn['project_id'], 'facebook') . '<br/>';
            }
            $email_html .= '</body></html>';

            $mail->setBodyHtml($email_html)
                ->setBodyText(strip_tags(preg_replace('#<br\s*/?>|<hr\s*/?>|</ul>|</li>|</h\d>#i', "\n", $email_html)))
                ->send();
        }
    }

    private function breakdown_block($campaign_id, $source)
    {
        $search = new Default_Model_DbTable_Search();
        $res = $search->getDigestData($campaign_id, $source);
        $ret = '<h2>' . ucfirst($source) . ' Breakdown</h2>
                <br/>Total mentions in the past 24 hours: ' . count($res) . '<br/><br/>' .
                (count($res) ? 'Latest entries:' : '') .
                '<ul>';
        $cnt = 0;
        while ($cnt++ < 10 && list(,$obj) = each($res)) {
            $ret .= '<li>' . $obj['search_author_name'] . ': ' . substr(strip_tags($obj['search_content']), 0, 200) . '</li>';
        }
        $ret .= '</ul>';

        return $ret;
    }

    private function _get_file_contents($url, $parameters, $json_decode = false, $json_assoc = false)
    {
        $url = $this->_appendQueryParams($url, $parameters);
        $response = file_get_contents($url);
        if($json_decode){
            $response = @json_decode($response, $json_assoc);
        }
        return $response;
    }

    /**
     * Append the array of parameters to the given URL string
     *
     * @param string $url
     * @param array $params
     * @return string
     */
    private function _appendQueryParams($url, array $params)
    {
        foreach ($params as $k => $v) {
            if(trim($v)){
                $url .= strpos($url, '?') === false ? '?' : '&';
                $url .= sprintf("%s=%s", $k, urlencode(trim($v)));
            }
        }
        return $url;
    }
}