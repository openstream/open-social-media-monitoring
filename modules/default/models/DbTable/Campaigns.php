<?php

class Default_Model_DbTable_Campaigns extends Osmm_Db_Table_Abstract
{
    protected $_name = 'campaign';
    private $_loaded_campaigns, $_query_ids, $_query_names, $_cron_data;

    public function getCampaigns() {
        if (!$this->_loaded_campaigns) {
            $this->_loaded_campaigns = $this->fetchAll();
        }
        return $this->_loaded_campaigns;
    }

    public function getAdditionalData($campaign_id)
    {
        $select = $this->_db->select();
        $select->from(array('p2q' => $this->getTableName('keyword_to_campaign')))
            ->joinLeft(array('q' => $this->getTableName('keyword')), 'q.query_id = p2q.query_id')
            ->joinLeft(array('s' => $this->getTableName('search')), 's.query_id = p2q.query_id', array('cnt' => 'COUNT(s.search_id)', 's.search_published'))
            ->where('p2q.project_id = ?', $campaign_id)
            ->group('p2q.query_id')
            ->order('s.search_published');
        $res = $select->query();
        $keywords = array();
        $cnt = $first_date = 0;
        while($res && $keyword = $res->fetchObject()){
            $keywords[] = $keyword->query_q;
            $cnt += $keyword->cnt;
            $first_date = !$first_date || $first_date > $keyword->search_published ? $keyword->search_published : $first_date;
        }

        return array(
            'keywords'  => implode(', ', $keywords),
            'weekly'    => round($cnt/ceil((time() - $first_date)/(3600*24*7))),
            'daily'     => round($cnt/ceil((time() - $first_date)/(3600*24)))
        );
    }

    public function save($data)
    {
        if (is_array($data)) {
            if ($data['project_id']) {
                $this->update($data, array('project_id = ?' => $data['project_id']));
            } else {
                $this->insert($data);
            }
        }
    }

    public function changeStatus($campaign_id)
    {
        if ((int)$campaign_id) {
            $this->update(
                array('project_status' => new Zend_Db_Expr('NOT project_status')),
                array('project_id = ?' => (int)$campaign_id)
            );
        }
    }

    public function getKeywordTitles($campaign_id)
    {
        if (!$this->_query_names) {
            $this->fetchKeywordData($campaign_id);
        }

        return $this->_query_names;
    }

    public function getKeywordIds($campaign_id)
    {
        if (!$this->_query_ids) {
            $this->fetchKeywordData($campaign_id);
        }

        return $this->_query_ids;
    }

    public function fetchKeywordData($campaign_id)
    {
        if (!$this->_query_ids || !$this->_query_names) {
            $this->_query_ids = $this->_query_names = array();
            $select = $this->_db->select();
            $select->from(array('q' => $this->getTableName('keyword')))
                ->joinInner(array('p2q' => $this->getTableName('keyword_to_campaign')), 'p2q.query_id = q.query_id')
                ->where('p2q.project_id = ?', $campaign_id);
            $res = $select->query()->fetchAll();
            foreach ($res as $query) {
                $this->_query_ids[] = $query['query_id'];
                $this->_query_names[$query['query_id']] = $query['query_q'] . ($query['query_lang'] ? ' (' . $query['query_lang'] . ')' : '');
            }
        }
    }

    public function getCronData()
    {
        if (!$this->_cron_data) {
            $select = $this->_db->select();
            $select->from(array('c' => $this->_name))
                ->joinInner(array('c2k' => $this->getTableName('keyword_to_campaign')), 'c2k.project_id = c.project_id')
                ->joinInner(array('k' => $this->getTableName('keyword')), 'k.query_id = c2k.query_id')
                ->where('c.project_status = ?', 1);
            $this->_cron_data = $select->query()->fetchAll();
        }

        return $this->_cron_data;
    }
}