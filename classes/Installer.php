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

    function defaultAction(){
        /** @var $view Block_Installer */
        $view = Application::getBlock('installer');

        if(file_exists('settings.php') && !is_writable('settings.php')){
            $message = '<p>Unfortunately settings.php file is not writable, so installer script can not run.</p>';
        }elseif(!file_exists('settings.php') && !is_writable('.')){
            $message = '<p>Unfortunately settings.php file does not exist and current directory is not writable, so installer script can not run.</p>';
        }elseif(file_exists('.htaccess') && !is_writable('.htaccess')){
            $message = '<p>Unfortunately .htaccess file is not writable, so installer script can not run.</p>';
        }elseif(!file_exists('.htaccess') && !is_writable('.')){
            $message = '<p>Unfortunately .htaccess file does not exist and current directory is not writable, so installer script can not run.</p>';
        }else{
            file_put_contents('.htaccess', "<IfModule mod_rewrite.c>\nRewriteEngine On\nRewriteBase ".$_SERVER['REQUEST_URI']."\nRewriteCond %{REQUEST_FILENAME} !-f\nRewriteCond %{REQUEST_FILENAME} !-d\nRewriteRule ^(.*)$ index.php [QSA,L]\n</IfModule>");
            $message = '<p>Do you want to run installer script?</p><div align="center"><input type="button" class="bu" value="Run" onclick="location.href = \''.$this->getUrl('installer/run').'\'" /></div>';
        }

        $view->processBlock('installer/run', array('message' => $message));
        $view->renderLayout(true);
    }
  
  function runAction(){
      /** @var $view Block_Settings */
      $view = Application::getBlock('installer');
      $view->processBlock('installer/form');
      $view->renderLayout(true);
  }

    function saveAction(){
        $rd = @mysql_connect($_POST['dbHost'], $_POST['dbUser'], $_POST['dbPassword']);
        if(@mysql_select_db($_POST['dbName'], $rd)){
            $query = '

DROP TABLE IF EXISTS '.$_POST['prefix'].'project;
CREATE TABLE IF NOT EXISTS '.$_POST['prefix'].'project (
  project_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  project_name varchar(255) NOT NULL,
  project_status TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\',
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
  query_last_twitter varchar(255) NOT NULL,
  query_last_facebook varchar(255) NOT NULL,
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
  search_author_image VARCHAR( 255 ) NOT NULL ,
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

DROP TABLE IF EXISTS '.$_POST['prefix'].'search_influencers;
CREATE TABLE '.$_POST['prefix'].'search_influencers (
  query_id int(10) unsigned NOT NULL,
  search_author_name varchar(255) NOT NULL,
  search_author_uri varchar(255) NOT NULL,
  cnt int(5) unsigned NOT NULL,
  search_source varchar(255) NOT NULL,
  UNIQUE KEY query_id (query_id,search_author_name)
);

';
            $queries = preg_split("/;+(?=([^'|^\\\']*['|\\\'][^'|^\\\']*['|\\\'])*[^'|^\\\']*[^'|^\\\']$)/", $query);
            foreach ($queries as $query){
                if (strlen(trim($query)) > 0){
                    mysql_query($query);
                }
            }
            $this->saveSettings();
        }
        header('Location: '.$this->getUrl());
    }
 }