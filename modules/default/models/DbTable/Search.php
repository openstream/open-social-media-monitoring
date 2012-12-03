<?php

class Default_Model_DbTable_Search extends Osmm_Db_Table_Abstract
{
    protected $_name = 'search';
    private $_graph_data, $_raw_stream, $_digest_data;

    public function getGraphInfo($query_ids)
    {
        if (!$this->_graph_data) {
            $select = $this->_db->select();
            $select->from(array('s' => $this->_name), array('search_source', 'search_published', 'query_id'))
                ->where('s.query_id IN (?)', explode(', ', $query_ids))
                ->group('s.search_id');
            $res = $select->query();
            $this->_graph_data = $res->fetchAll();
        }

        return $this->_graph_data;
    }

    public function getRawStream($query_ids, $from, $to)
    {
        if (!$this->_raw_stream) {
            $select = $this->_db->select();
            $select->from(array('s' => $this->_name))
                ->where('s.query_id IN (?)', $query_ids)
                ->where('s.search_published > ?', $from)
                ->where('s.search_published < ?', $to)
                ->order('s.search_published DESC');
            $res = $select->query();
            $this->_raw_stream = $res->fetchAll();
        }

        return $this->_raw_stream;
    }

    public function getDigestData($campaign_id, $source)
    {
        if (!$this->_digest_data) {
            $select = $this->_db->select();
            $select->from(array('k2c' => $this->_name))
                ->joinInner(array('c' => $this->getTableName('campaign')), 'c.project_id = k2c.project_id')
                ->joinInner(array('k' => $this->getTableName('keyword')), 'k.query_id = k2c.query_id')
                ->joinInner(array('s' => $this->getTableName('search')), 's.query_id = k2c.query_id')
                ->where(array(
                    'c.project_status = ?'      => 1,
                    'k2c.project_id = ?'        => $campaign_id,
                    's.search_source = "?"'     => $source,
                    's.search_published > ?'    => time() - 3600*24
                    ));
            $this->_digest_data = $select->query()->fetchAll();
        }

        return $this->_digest_data;
    }
}