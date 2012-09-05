<?php

class Osmm_Controller_Plugin_Template extends Zend_Controller_Plugin_Abstract
{
    public function routeShutdown($request)
    {
        $template = new stdClass;
        $template->foldername = 'default';
        Zend_Registry::set('theme', $template);

        /** @var $bootstrap Bootstrap */
        $bootstrap = Zend_Controller_Front::getInstance()->getParam('bootstrap');

        if ($bootstrap->hasResource('view')) {
            /** @var $view Zend_View */
// Not yet
//            $view = $bootstrap->getResource('view');
//            $view->setBasePath(APPLICATION_PATH . '/views/' . $template->foldername);
        }

        if ($bootstrap->hasResource('layout')) {
            /** @var $layout Zend_Layout */
            $layout = $bootstrap->getResource('layout');
            $layout->setLayoutPath(APPLICATION_PATH . '/templates/' . $template->foldername);
        }
    }
}