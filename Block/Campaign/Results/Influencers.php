<?php

class Block_Campaign_Results_Influencers extends Block_Campaign_Results
{
    public function output()
    {
        /** @var $influencers Model_Influencers */
        $influencers = Application::getModel('influencers');
        $topInfluencers = $influencers->getTopInfluencers();
        $ret = '';
        if(count($topInfluencers)){
            $ret .= '<h3>Top Influencers</h3>';
            foreach($topInfluencers as $influencer){
                $ret .= '<div class="left user"><a href="'.$influencer['search_author_uri'].'" target="_blank">'.$influencer['search_author_name'].'</a></div>
                         <div class="right">'.$influencer['cnt'].' mention'.($influencer['cnt'] > 1 ? 's' : '').'</div>
                         <div class="clear"></div>';
            }
        }
        return $ret;
    }
}