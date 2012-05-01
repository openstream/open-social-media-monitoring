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

  function __construct($args){
   // Searching for a method name and calling either it or default method
   if(is_array($args) && count($args) && method_exists($this, strtolower($args[0]).'Action')){
	call_user_func_array(array($this, strtolower($args[0]).'Action'), array_slice($args, 1));
   }else{
	$this->defaultAction();
   }
  }
   
  function defaultAction(){
   global $admPassword, $adminEmail, $defaultFrom, $dbHost, $dbUser, $dbUser, $dbPassword, $dbName, $prefix, $keep_history, $alchemy_api_key;
   
    a_header('Settings');
    open_table('Server Configuration');
    echo '<table width="632" cellpadding=4 cellspacing=2><form method=post action="'.$this->getUrl('settings/save').'">
            <tr><td align=right><b>Administrator E-Mail:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=adminEmail size="50" value="'.$adminEmail.'"></td></tr>
            <tr><td align=right><b>Administator Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=admPassword size="50" value="'.$admPassword.'"></td></tr>
            <tr><td align=right><b>Default From Address:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=defaultFrom size="50" value="'.$defaultFrom.'"></td></tr>
            <tr><td align=right><b>MySQL Server:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbHost value="'.$dbHost.'" size="50"></td></tr>
            <tr><td align=right><b>MySQL Login:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbUser value="'.$dbUser.'" size="50"></td></tr>
            <tr><td align=right><b>MySQL Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=dbPassword value="'.$dbPassword.'" size="50"></td></tr>
            <tr><td align=right><b>MySQL Database:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbName value="'.$dbName.'" size="50"></td></tr>
            <tr><td align=right><b>Database Prefix:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=prefix value="'.$prefix.'" size="50"></td></tr>
            <tr><td align=right><b>Show timeline for last:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type="text" name="keep_history" value="'.$keep_history.'" size="3"/> days</td></tr>
            <tr><td align=right><b>Alchemy API Key</b> (<a href="" target="_blank">Get It Here</a>):</td><td style="padding-top:2px;padding-bottom:2px;"><input type="text" name="alchemy_api_key" value="'.$alchemy_api_key.'" size="50"/></td></tr>
            <tr><td></td><td><input type=submit value="Save Settings" class=bu></td></tr>
           </form></table>';
    close_table();
	a_footer();
  }

  function saveAction(){
   if(count($_POST) == 10){
    $fp = fopen('settings.php', 'w+');
    fputs($fp, "<?php\n\n");
    while(list($var, $val) = each($_POST))
     fputs($fp, ' $'.$var." = '".$val."';\n");
    fputs($fp, "\n\n?>");
    fclose($fp);
   }
   header('Location: '.$this->getUrl('settings'));
  }
 }

?>