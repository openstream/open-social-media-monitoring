<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

class Settings extends Application{

    function defaultAction(){
        /** @var $view Block_Settings */
        $view = Application::getBlock('settings');
        $view->processBlock('settings/form');
        $view->renderLayout();
    }

    function saveAction(){
        $this->saveSettings();
        header('Location: '.$this->getUrl('settings'));
    }
}