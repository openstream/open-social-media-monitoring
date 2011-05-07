<?php

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