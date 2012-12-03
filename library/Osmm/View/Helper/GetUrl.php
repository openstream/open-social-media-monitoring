<?php

class Osmm_View_Helper_GetUrl extends Zend_View_Helper_BaseUrl
{
    public function getUrl($route)
    {
        $route = explode('/', $route);
        $route = array_filter($route);
        $request = Zend_Controller_Front::getInstance()->getRequest();
        if (isset($route[0]) && $route[0] == '*') {
            $route[0] = $request->getModuleName();
        }
        if (isset($route[1]) && $route[1] == '*') {
            $route[1] = $request->getControllerName();
        }
        if (isset($route[2]) && $route[2] == '*') {
            $route[2] = $request->getActionName();
        }
        $url = implode('/', $route) . '/';
        $url = (preg_match('/https/i', $_SERVER['SERVER_PROTOCOL']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$this->baseUrl($url);

        return $url;
    }
}