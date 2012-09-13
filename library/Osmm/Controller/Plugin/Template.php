<?php

class Osmm_Controller_Plugin_Template extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown(Zend_Controller_Request_Abstract $request)
    {
        /** @var $bootstrap Bootstrap */
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');
        if ($bootstrap->hasResource('layout')) {
            $template = $bootstrap->getOption('template') ? $bootstrap->getOption('template') : 'default';
            Zend_Registry::set('template', $template);

            /** @var $layout Zend_Layout */
            $layout = $bootstrap->getResource('layout');
            $layout->setLayoutPath(APPLICATION_PATH . '/templates/' . $template);

            $view = $layout->getView();
            $view->menu = $this->_tables = $bootstrap->getOption('menu');

            $active_module = $request->getModuleName() == 'default' ? 'campaigns' : $request->getModuleName();
            $view->menu[$active_module]['active'] = true;
        }
    }

    public function dispatchLoopStartup(Zend_Controller_Request_Abstract $request) {
        $view = Zend_Controller_Action_HelperBroker::getExistingHelper('ViewRenderer')->view;
        $scriptPath = APPLICATION_PATH . '/templates/' . Zend_Registry::get('template') . '/' . $request->getModuleName();
        $defaultScriptPath = APPLICATION_PATH . '/templates/' . Zend_Registry::get('template') . '/default';

        if (file_exists($scriptPath)) {
            $view->addScriptPath($scriptPath);
        } elseif ($request->getModuleName() != 'default' && file_exists($defaultScriptPath)) {
            $view->addScriptPath($defaultScriptPath);
        }
    }
}