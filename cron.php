<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 include('settings.php');
 
 $rd = @mysql_connect($dbHost, $dbUser, $dbPassword);
 mysql_select_db($dbName, $rd);

 $query = 'SELECT * FROM '.$prefix.'query';
 $res = mysql_query($query);
 while($res && $obj = mysql_fetch_object($res)){
  new Twitter($obj);
  new FaceBook($obj);
 }

 function __autoload($class_name) {
   require_once(dirname(__file__).'/classes/'.$class_name.'.php');
 }
  
?>