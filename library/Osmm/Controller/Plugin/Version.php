<?php

class Osmm_Controller_Plugin_Version extends Zend_Controller_Plugin_Abstract
{
    public function preDispatch(Zend_Controller_Request_Abstract $request)
    {
        $front = Zend_Controller_Front::getInstance();
        $bootstrap = $front->getParam("bootstrap");

        $layout = Zend_Layout::getMvcInstance();
        $view = $layout->getView();

        $view->version = $bootstrap->getOption('version');
    }
}