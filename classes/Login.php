<?php 

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 class Login extends Application{
 
  function defaultAction(){
      /** @var $view Block_Login */
      $view = Application::getBlock('login');
      $view->processBlock('login/form');
      $view->renderLayout(true);
  }
 
  function processAction(){
   $_SESSION['a_email'] = $_POST['email'];
   $_SESSION['a_password'] = $_POST['password'];
   header('Location: '.$this->getUrl());
  }
 }

?>