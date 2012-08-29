<?php
class Login_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $this->_helper->_layout->setLayout('blank');
    }

    public function processAction()
    {
        $request = $this->getRequest();
        $defaultNamespace = new Zend_Session_Namespace('Default');
        $defaultNamespace->email = $request->getParam('email');
        $defaultNamespace->password = $request->getParam('password');

        $this->_helper->redirector('index', 'index', 'default');
    }
}
