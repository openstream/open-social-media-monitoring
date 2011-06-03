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
 session_start();

 new Application();
 
 Application::run();

 function __autoload($class_name) {
   require_once(dirname(__file__).'/classes/'.$class_name.'.php');
 }

 // Following 4 functions will go to Template class

 function a_footer(){
  include('includes/a_footer.php');
 }

 function a_header(){
  global $directory;
  include('includes/a_header.php');
 }

 function open_table($title = ''){
  echo '<table cellspacing=0 cellpadding=0 bgcolor=#E4E4DD>
     <tr><td><img src=images/a12.gif width=8 height=8></td><td background=images/a13.gif></td><td><img src=images/a15.gif width=8 height=8></td></tr>
     <tr><td></td><td style="padding:5px;">'.($title ? '<span class=he3>'.$title.'</span>' : '').($title ? '</td></tr>
     <tr><td></td><td style="border-top: #666666 solid 1px;">&nbsp;</td><td></tr><tr><td></td><td>' : '');
 }

 function close_table(){
  echo '</td><td></td></tr>
     <tr><td><img src=images/a28.gif width=8 height=8></td><td></td><td><img src=images/a29.gif width=8 height=8></td></tr>
    </table>';
 }

?>