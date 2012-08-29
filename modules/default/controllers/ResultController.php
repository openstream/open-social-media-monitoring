<?php

class ResultController extends Zend_Controller_Action
{
    public function preDispatch()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        $config = $bootstrap->getOptions();
        $defaultNamespace = new Zend_Session_Namespace('Default');
        if ($defaultNamespace->email != $config['authentication']['email'] || $defaultNamespace->password != $config['authentication']['password']) {
            $this->_helper->redirector('index', 'index', 'login');
        }
    }

    public function keywordAction()
    {
        if ($this->getRequest()->getParam('id')) {
            $model = new Default_Model_DbTable_Keyword();
            $keyword = $model->fetchRow(array('query_id = ?' => $this->getRequest()->getParam('id')));

            $graph_data = $this->getGraphData('keyword', $keyword->query_id);

            $this->view->title = 'Daily Twitter vs. Facebook';
            $this->view->subtitle = 'Keyword: ' . $keyword->query_q . ' Language: ' . $keyword->query_lang;
            $this->view->results_cnt = $graph_data['cnt'];
            $this->view->min_date = $graph_data['min'];
            $this->view->max_date = $graph_data['max'];

            $model = new Default_Model_DbTable_Influencers();
            $this->view->influencers = $model->getInfluencers($keyword->query_id);

            $this->view->render('result/head.phtml');
            $this->view->render('result/graph.phtml');
            $this->view->render('result/influencers.phtml');

            $this->view->query_ids = $this->getRequest()->getParam('id');
            $this->view->render('result/wire.phtml');
        } else {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
    }

    public function campaignAction()
    {
        if ($this->getRequest()->getParam('id')) {
            $model = new Default_Model_DbTable_Campaigns();
            $campaign = $model->fetchRow(array('project_id = ?' => $this->getRequest()->getParam('id')));

            $keyword_ids = $model->getKeywordIds($campaign['project_id']);
            $keyword_ids = implode(', ', $keyword_ids);
            $this->view->keyword_titles = $model->getKeywordTitles($campaign['project_id']);

            $graph_data = $this->getGraphData('campaign', $keyword_ids);

            $this->view->title = 'Project: ' . $campaign['project_name'];
            $this->view->subtitle = '';
            $this->view->results_cnt = $graph_data['cnt'];
            $this->view->min_date = $graph_data['min'];
            $this->view->max_date = $graph_data['max'];

            $model = new Default_Model_DbTable_Influencers();
            $this->view->influencers = $model->getInfluencers($keyword_ids);

            $this->view->render('result/head.phtml');
            $this->view->render('result/graph.phtml');
            $this->view->render('result/influencers.phtml');

            $this->view->query_ids = $keyword_ids;
            $this->view->render('result/wire.phtml');
        } else {
            throw new Zend_Controller_Action_Exception('This page does not exist', 404);
        }
    }

    public function wirexmlAction()
    {
        $this->_helper->_layout->setLayout('empty');

        // Array indexes are 0-based, jCarousel positions are 1-based.
        $this->view->first = max(0, intval($this->getRequest()->getParam('first')) - 1);
        $this->view->last  = max($this->view->first + 1, intval($this->getRequest()->getParam('last')) - 1);

        $from = strtotime(date('Y-m-d', $this->getRequest()->getParam('from')));
        $to = strtotime(date('Y-m-d', $this->getRequest()->getParam('to')));

        $model = new Default_Model_DbTable_Search();
        $this->view->stream = $model->getRawStream($this->getRequest()->getParam('ids'), $from, $to);
    }

    private function getGraphData($type, $query_ids)
    {
        $results = array(
            'cnt' => $type == 'keyword' ? array('facebook' => array(), 'twitter' => array()) : array(),
            'min' => 0,
            'max' => 0
        );

        $search = new Default_Model_DbTable_Search();
        foreach ($search->getGraphInfo($query_ids) as $obj) {
            $darr = getdate($obj['search_published']);
            $date = mktime(0, 0, 0, $darr['mon'], $darr['mday'], $darr['year']);
            $results['cnt'][$type == 'keyword' ? $obj['search_source'] : $obj['query_id']][$date]++;
            $results['min'] = $results['min'] && $results['min'] < $date ? $results['min'] : $date;
            $results['max'] = $results['max'] && $results['max'] > $date ? $results['max'] : $date;
        }

        $search_index = new Default_Model_DbTable_SearchIndex();
        foreach ($search_index->getGraphInfo($query_ids) as $obj) {
            $results['cnt'][$type == 'keyword' ? $obj['index_source'] : $obj['query_id']][$obj['index_date']] += $obj['index_count'];
            $results['min'] = $results['min'] && $results['min'] < $obj['index_date'] ? $results['min'] : $obj['index_date'];
            $results['max'] = $results['max'] && $results['max'] > $obj['index_date'] ? $results['max'] : $obj['index_date'];
        }

        return $results;
    }
}