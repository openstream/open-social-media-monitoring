<?php

class Default_View_Helper_GetUrl extends Zend_View_Helper_BaseUrl
{
    public function getUrl($route)
    {
        $route = explode('/', $route);
        $route = array_filter($route);
        if ($route[0] == '*') {
            $route[0] = Zend_Controller_Front::getInstance()->getRequest()->getModuleName();
        }
        if ($route[1] == '*') {
            $route[1] = Zend_Controller_Front::getInstance()->getRequest()->getControllerName();
        }
        if ($route[2] == '*') {
            $route[2] = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
        }
        $url = implode('/', $route) . '/';
        $url = (preg_match('/https/i', $_SERVER['SERVER_PROTOCOL']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$this->baseUrl($url);

        return $url;
    }
}