<?php

class Default_Model_DbTable_Influencers extends Osmm_Db_Table_Abstract
{
    protected $_name = 'influencer';
    private $_loaded_influencers;

    public function getInfluencers($query_ids)
    {
        if (!$this->_loaded_influencers) {
            $select = $this->_db->select();
            $select->from(array('i' => $this->_name), array('search_author_name', 'search_author_uri', 'cnt'))
                ->where('i.query_id IN (?)', $query_ids)
                ->order('i.cnt DESC')
                ->limit(5, 0);
            $res = $select->query();
            $this->_loaded_influencers = $res->fetchAll();
        }

        return $this->_loaded_influencers;
    }
}