<?php

class Osmm_Controller_Plugin_Template extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown($request)
    {
        // Hardcoded for now
        $template = 'default';
        Zend_Registry::set('template', $template);

        /** @var $bootstrap Bootstrap */
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');

        if ($bootstrap->hasResource('layout')) {
            /** @var $layout Zend_Layout */
            $layout = $bootstrap->getResource('layout');
            $layout->setLayoutPath(APPLICATION_PATH . '/templates/' . $template);
        }
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $view = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')->view;
        $scriptPath = sprintf('%s/templates/%s/%s',
            APPLICATION_PATH,
            Zend_Registry::get('template'),
            $request->getModuleName()
        );
        if (file_exists($scriptPath)) {
            $view->addScriptPath($scriptPath);
        }
    }
}