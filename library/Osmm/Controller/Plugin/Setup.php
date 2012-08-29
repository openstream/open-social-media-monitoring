<?php

class Osmm_Controller_Plugin_Setup extends Zend_Controller_Plugin_Abstract
{
    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");
        $local_config = $bootstrap->getOption('local_config');
        if (!file_exists($local_config) && $request->getModuleName() != 'install') {
            $baseUrl = new Zend_View_Helper_BaseUrl();
            $this->getResponse()->setRedirect($baseUrl->baseUrl('/install/'));
        }
    }
}