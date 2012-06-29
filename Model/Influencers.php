<?php

class Model_Influencers extends Model_Abstract
{
    public function __construct(){
        $this->_mainTable = 'search_influencers';
    }

    /**
     * Return top influencers array
     *
     * @param $project_id
     * @param int $influencers_to_show
     * @return array
     */
    public function getTopInfluencers($influencers_to_show = 5){
        global $prefix;

        /** @var $campaign Model_Campaign */
        $campaign = Application::getSingleton('campaign');
        $query_ids = $campaign->getQueryIds();

        $query = 'SELECT search_author_name, search_author_uri, cnt
                    FROM '.$prefix.'search_influencers
                   WHERE query_id IN ('.$query_ids.')
                ORDER BY cnt
                   LIMIT 0, '.$influencers_to_show;
        $res = mysql_query($query);
        $_topInfluencers = array();
        while($res && $_influencer = mysql_fetch_array($res)) {
            $_topInfluencers[] = $_influencer;
        }
        return $_topInfluencers;
    }
}