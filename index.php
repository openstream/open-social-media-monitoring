<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

if(file_exists('settings.php')){
    include('settings.php');
}

Application::run();

function __autoload($class_name)
{
    /* backward compatibility (until `classes` dir exists) */
    if(!preg_match('/^Model_|^Block_/', $class_name)){
        $class_name = 'classes/'.$class_name;
    }
    $class_name = dirname(__FILE__).'/'.str_replace('_', '/', $class_name).'.php';
    if(file_exists($class_name)){
        require_once($class_name);
    }
}