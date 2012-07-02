<?php

class Block_Installer_Form extends Block_Installer
{
    public function output()
    {
        $ret = '<html><head><title>Administrator Area</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><base href="../../"/><link rel="stylesheet" href="st.css"></head><body class="installer"><center>';
        /** @var $view Block_Settings_Form */
        $view = Application::getBlock('settings/form');
        $view->formAction = $this->getUrl('installer/save');
        $ret .= $view->output();
        $ret .= '</body></html>';

        return $ret;
    }
}