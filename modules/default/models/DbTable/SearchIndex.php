<?php

class Default_Model_DbTable_SearchIndex extends Osmm_Db_Table_Abstract
{
    protected $_name = 'search_index';
    private $_graph_data;

    public function getGraphInfo($query_ids)
    {
        if (!$this->_graph_data) {
            $select = $this->_db->select();
            $select->from(array('s' => $this->_name))
                ->where('s.query_id IN (?)', $query_ids);
            $res = $select->query();
            $this->_graph_data = $res->fetchAll();
        }

        return $this->_graph_data;
    }
}