<?php
class Default_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initModuleConfig()
    {
        $iniOptions = new Zend_Config_Xml(dirname(__FILE__) . '/configs/application.xml');
        $this->getApplication()->setOptions($iniOptions->toArray());
    }
}