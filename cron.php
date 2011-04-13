<?php

 include('includes/settings.php');
 include('includes/functions.php');
 connect();

 $query = 'SELECT * FROM '.$prefix.'query';
 $res = mysql_query($query);
 while($res && $obj = mysql_fetch_object($res)){
  new Twitter($obj);
  new FaceBook($obj);
 }

?>