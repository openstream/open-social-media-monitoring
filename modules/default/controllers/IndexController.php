<?php
class IndexController extends Zend_Controller_Action
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

    public function indexAction()
    {
        $campaigns = new Default_Model_DbTable_Campaigns();
        $this->view->campaigns = $campaigns->getCampaigns();
        $this->view->additional_data = array();

        foreach ($this->view->campaigns as $campaign) {
            $this->view->additional_data[$campaign->project_id] = $campaigns->getAdditionalData($campaign->project_id);
        }
    }

    public function editAction()
    {
        if ($this->getRequest()->getParam('campaign')) {
            $model = new Default_Model_DbTable_Campaigns();
            $this->view->campaign = $model->fetchRow(array('project_id = ?' => $this->getRequest()->getParam('campaign')));
        } else {
            $this->view->campaign = new ArrayObject();
            $this->view->campaign->project_id = 0;
            $this->view->campaign->project_name = '';
        }
    }

    public function saveAction()
    {
        $data = array(
            'project_id' => $this->getRequest()->getParam('id'),
            'project_name' => $this->getRequest()->getParam('project_name')
        );
        $model = new Default_Model_DbTable_Campaigns();
        $model->save($data);

        $this->_helper->redirector('index', 'index', 'default');
    }

    public function statusAction()
    {
        if ($this->getRequest()->getParam('campaign')) {
            $model = new Default_Model_DbTable_Campaigns();
            $model->changeStatus($this->getRequest()->getParam('campaign'));
        }

        $this->_helper->redirector('index', 'index', 'default');
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('campaign')) {
            $model = new Default_Model_DbTable_Campaigns();
            $campaign = $model->fetchRow(array('project_id = ?' => $this->getRequest()->getParam('campaign')));
            $campaign->delete();
        }

        $this->_helper->redirector('index', 'index', 'default');
    }
}
