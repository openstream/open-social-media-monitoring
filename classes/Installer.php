<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 class Installer extends Application{

  function __construct($args){
   // Searching for a method name and calling either it or default method
   if(is_array($args) && count($args) && method_exists($this, strtolower($args[0]).'Action')){
	call_user_func_array(array($this, strtolower($args[0]).'Action'), array_slice($args, 1));
   }else{
	$this->defaultAction();
   }
  }
   
  function defaultAction(){
   echo '<html><head><title>Administrator Area</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><link rel=stylesheet href=st.css></head><body class="installer"><center>';
   open_table('Error');
   echo '<p>Not able to connect to database.</p>';
	if(file_exists('settings.php') && !is_writable('settings.php')){
	 echo '<p>Unfortunately settings.php file is not writable, so installer script can not run.</p>';
	}elseif(!file_exists('settings.php') && !is_writable('.')){
	 echo '<p>Unfortunately settings.php file does not exist and current directory is not writable, so installer script can not run.</p>';
	}elseif(file_exists('.htaccess') && !is_writable('.htaccess')){
	 echo '<p>Unfortunately .htaccess file is not writable, so installer script can not run.</p>';
	}elseif(!file_exists('.htaccess') && !is_writable('.')){
	 echo '<p>Unfortunately .htaccess file does not exist and current directory is not writable, so installer script can not run.</p>';
	}else{
     file_put_contents('.htaccess', "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase ".$_SERVER['REQUEST_URI']."\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [QSA,L]\n</IfModule>");
	 echo '<p>Do you want to run installer script?</p><div align="center"><input type="button" class="bu" value="Run" onclick="location.href = \''.$this->getUrl('installer/run').'\'" /></div>';
	}    
    close_table();
	echo '</body></html>';  
  }
  
  function runAction(){   
   echo '<html><head><title>Administrator Area</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><base href="../../"/><link rel="stylesheet" href="st.css"></head><body class="installer"><center>';
   open_table('Installer');
   echo '<table width=355 cellpadding=4 cellspacing=2><form method=post action="'.$this->getUrl('installer/save').'">
            <tr><td align=right><b>Administrator E-Mail:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=adminEmail size=30 value="'.$_SESSION['adminEmail'].'"></td></tr>
            <tr><td align=right><b>Administator Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=admPassword size=30 value="'.$_SESSION['admPassword'].'"></td></tr>
            <tr><td align=right><b>Default From Address:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input name=defaultFrom size=30 value="'.$_SESSION['defaultFrom'].'"></td></tr>
            <tr><td align=right><b>MySQL Server:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbHost value="'.$_SESSION['dbHost'].'" size=30></td></tr>
            <tr><td align=right><b>MySQL Login:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbUser value="'.$_SESSION['dbUser'].'" size=30></td></tr>
            <tr><td align=right><b>MySQL Password:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=password name=dbPassword value="'.$_SESSION['dbPassword'].'" size=30></td></tr>
            <tr><td align=right><b>MySQL Database:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=dbName value="'.$_SESSION['dbName'].'" size=30></td></tr>
            <tr><td align=right><b>Database Prefix:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type=text name=prefix value="'.$_SESSION['prefix'].'" size=30></td></tr>
            <tr><td align=right><b>Show timeline for last:</b></td><td style="padding-top:2px;padding-bottom:2px;"><input type="text" name="keep_history" value="'.($_SESSION['keep_history'] ? $_SESSION['keep_history'] : 3).'" size="3"/> days</td></tr>
            <tr><td></td><td><input type=submit value="Save Settings" class=bu></td></tr>
         </form></table>';
   close_table();
   echo '</body></html>';
  }

  function saveAction(){   
   if(count($_POST) == 9){
    $rd = @mysql_connect($_POST['dbHost'], $_POST['dbUser'], $_POST['dbPassword']);
    if(@mysql_select_db($_POST['dbName'], $rd)){
     $query = '

DROP TABLE IF EXISTS '.$_POST['prefix'].'project;
CREATE TABLE IF NOT EXISTS '.$_POST['prefix'].'project (
  project_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  project_name varchar(255) NOT NULL,
  PRIMARY KEY (project_id)
);

DROP TABLE IF EXISTS '.$_POST['prefix'].'project_to_query;
CREATE TABLE IF NOT EXISTS '.$_POST['prefix'].'project_to_query (
  project_id int(10) unsigned NOT NULL,
  query_id int(10) unsigned NOT NULL,
  UNIQUE KEY project_id (project_id,query_id)
);

DROP TABLE IF EXISTS '.$_POST['prefix'].'query;
CREATE TABLE IF NOT EXISTS '.$_POST['prefix'].'query (
  query_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  query_q varchar(255) NOT NULL,
  query_lang char(2) NOT NULL,
  query_geocode varchar(255) NOT NULL,
  query_nearplace varchar(255) NOT NULL,
  query_distance int(10) NOT NULL,
  query_distanceunit varchar(30) NOT NULL,
  PRIMARY KEY (query_id)
);

DROP TABLE IF EXISTS '.$_POST['prefix'].'search;
CREATE TABLE IF NOT EXISTS '.$_POST['prefix'].'search (
  search_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  query_id int(10) unsigned NOT NULL,
  search_outer_id VARCHAR( 255 ) NOT NULL ,
  search_source VARCHAR( 255 ) NOT NULL ,
  search_published INT( 10 ) UNSIGNED NOT NULL ,
  search_title VARCHAR( 255 ) NOT NULL ,
  search_content TEXT NOT NULL ,
  search_author_name VARCHAR( 255 ) NOT NULL ,
  search_author_uri VARCHAR( 255 ) NOT NULL ,  
  PRIMARY KEY (search_id)
);

DROP TABLE IF EXISTS '.$_POST['prefix'].'search_index;
CREATE TABLE '.$_POST['prefix'].'search_index (
  query_id int(10) NOT NULL,
  index_date int(10) NOT NULL,
  index_source varchar(255) NOT NULL,
  index_count int(5) NOT NULL,
  UNIQUE KEY query_id (query_id,index_date,index_source)
);

DROP TABLE IF EXISTS '.$_POST['prefix'].'search_influencers_index;
CREATE TABLE '.$_POST['prefix'].'search_influencers_index (
  query_id int(10) unsigned NOT NULL,
  index_date int(10) unsigned NOT NULL,
  search_source varchar(255) NOT NULL,
  search_author_name varchar(255) NOT NULL,
  search_author_uri varchar(255) NOT NULL,
  index_count int(5) unsigned NOT NULL,
  UNIQUE KEY query_id (query_id,search_source,search_author_name,index_date)
);

DROP TABLE IF EXISTS '.$_POST['prefix'].'search_link;
CREATE TABLE IF NOT EXISTS '.$_POST['prefix'].'search_link (
  search_link_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  search_id int(10) unsigned NOT NULL,
  search_link_str text NOT NULL,
  PRIMARY KEY (search_link_id)
);

';
     $queries = preg_split("/;+(?=([^'|^\\\']*['|\\\'][^'|^\\\']*['|\\\'])*[^'|^\\\']*[^'|^\\\']$)/", $query);	 
     foreach ($queries as $query){ 
      if (strlen(trim($query)) > 0){
	   mysql_query($query); 
	  }
     }
     $fp = fopen('settings.php', 'w+');
     fputs($fp, "<?php\n\n");
     while(list($var, $val) = each($_POST)){
      fputs($fp, ' $'.$var." = '".$val."';\n");
	 }
     fputs($fp, "\n\n?>");
     fclose($fp);
	 
	 header('Location: '.$this->getUrl());
    }else{
     while(list($var, $val) = each($_POST)){
      $_SESSION[$var] = $val;
	 }
     echo '<html><head><title>Administrator Area</title><meta http-equiv="Content-Type" content="text/html; charset=utf-8"><base href="../../"/><link rel="stylesheet" href="st.css"></head><body class="installer"><center>';
     open_table('Error');	
	 echo '<p>Not able to connect to database.</p><div align="center"><input type="button" class="bu" value="Try Again?" onclick="location.href = \''.$this->getUrl('installer/run').'\'" /></div>';
	 close_table();
	 echo '</body></html>';
    }
   }   
  }
  
 }

?>