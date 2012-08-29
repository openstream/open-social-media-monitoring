<?php

class Bootstrap extends Zend_Application_Bootstrap_Bootstrap
{
    protected function _initPlaceholders()
    {
        $this->bootstrap('view');
        $view = $this->getResource('view');

        $view->placeholder('head');
        $view->placeholder('graph');
        $view->placeholder('influencers');
        $view->placeholder('wire');
    }

    public function _initLocalSettings()
    {
        $local_config = $this->getOption('local_config');
        if (file_exists($local_config)) {
            $config = new Zend_Config_Ini($local_config);
            $this->setOptions($config->toArray());
        }
    }
}