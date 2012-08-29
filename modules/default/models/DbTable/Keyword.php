<?php

class Default_Model_DbTable_Keyword extends Osmm_Db_Table_Abstract
{
    protected $_name = 'keyword';
    private $_loaded_keywords;

    public function getKeywords($campaign_id)
    {
        if (!$this->_loaded_keywords) {
            $select = $this->_db->select();
            $select->from(array('q' => $this->_name))
                ->joinInner(array('p2q' => $this->getTableName('keyword_to_campaign')), 'q.query_id = p2q.query_id')
                ->where('p2q.project_id = ?', (int)$campaign_id);
            $res = $select->query();
            $this->_loaded_keywords = $res->fetchAll();
        }

        return $this->_loaded_keywords;
    }

    public function save($data)
    {
        if (is_array($data)) {
            if ($data['query_id']) {
                $this->update($data, array('query_id = ?' => $data['query_id']));
            } else {
                return $this->insert($data);
            }
        }
        return 0;
    }
}