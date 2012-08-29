<?php
class Default_Bootstrap extends Zend_Application_Module_Bootstrap
{
    protected function _initModuleConfig()
    {
        $iniOptions = new Zend_Config_Ini(dirname(__FILE__) . '/configs/application.ini');
        $this->getApplication()->setOptions($iniOptions->toArray());
    }
}