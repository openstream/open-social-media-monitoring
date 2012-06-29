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
   global $directory;
  
   echo '<html><head><title>Administrator Area</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><base href="'.$directory.'" /><link rel=stylesheet href=st.css></head><body style="background-color:#FFFFFF;"><br><br><br><br><br><br><br><br><center>';
 open_table('Administrator Logon');

 echo '<table><form method="post" action="'.$this->getUrl('login/process').'">
        <tr><td>E-Mail:</td><td><input name="email" type="text"></td></tr>
        <tr><td>Password:</td><td><input name="password" type="password"></td></tr>
        <tr><td></td><td><input type="submit" value="Login"></td></tr>
       </form></table>

     <script>
      onload = function(){
       document.forms[0].login.focus();
      }
     </script>
     <style>
      td{font-family: Verdana, Tahoma, Arial; font-size: 10px;}
      input, select{font-size: 10px;}
      .he{background-image: url(../img/bg01.gif); font-weight: bold; color: #005B90;}
     </style>
 ';
   close_table();
   echo '</body></html>'; 
  }
 
  function processAction(){
   $_SESSION['a_email'] = $_POST['email'];
   $_SESSION['a_password'] = $_POST['password'];
   header('Location: '.$this->getUrl());
  }
 }

?>