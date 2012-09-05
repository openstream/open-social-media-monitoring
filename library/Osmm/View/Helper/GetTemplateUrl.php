<?php

class Osmm_View_Helper_GetTemplateUrl extends Zend_View_Helper_BaseUrl
{
    public function getTemplateUrl($url = null)
    {
        $template = Zend_Registry::get('template');
        if (null !== $url) {
            $url = ltrim($url, '/\\');
        }
        return $this->getBaseUrl() . '/templates/' . $template->foldername . '/' . $url;
    }
}