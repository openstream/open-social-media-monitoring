<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 class Application{
  
  function run(){
   global $directory, $dbName, $dbHost, $dbUser, $dbPassword, $admPassword;
   
   // If script is running in directory (not in a web-server root), then getting the dirrectory name and stripping it from REQUEST_URI
   $directory = preg_replace('/index.php$/ism', '', $_SERVER['SCRIPT_NAME']);
   $uri = preg_replace('|'.$directory.'|ism', '', $_SERVER['REQUEST_URI']);
  
   // Parsing request string into array
   $uri = explode('/', trim($uri, '/'));
	
   // Popping the first element of the path. It is representing the class that is handling output.
   $class = $uri[0] ? ucfirst($uri[0]) : 'Projects';
   $args = array_slice($uri, 1);

   // If not able to connect to database run the installer
   if($rd = @mysql_connect($dbHost, $dbUser, $dbPassword)){
    mysql_select_db($dbName, $rd);
   }else{
    $class = 'Installer';
   }
   
   // If user is not logged then setting class name to Login
   if(($_SESSION['a_login'] != 'admin' || $_SESSION['a_password'] != $admPassword) && $class != 'Installer'){
    $class = 'Login';
   }

   // At last calling class and sending request argumants  
   new $class($args);
  }
  
  function getUrl($route = ''){
   global $directory;
  
   return (preg_match('/https/i', $_SERVER['SERVER_PROTOCOL']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$directory.trim($route, '/').($route ? '/' : '');
  }
  
 }

?>