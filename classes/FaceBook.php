<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 class Facebook{
  function __construct($obj){
   global $prefix, $alchemy_api_key;
   
   $facebook = file_get_contents('https://graph.facebook.com/search?q='.urlencode($obj->query_q).'&type=post&limit=100&since='.$this->getLastPostDate($obj->query_id));
   $facebook =  json_decode($facebook);
   while(is_array($facebook->data) && list(,$entry) = each($facebook->data)){
    if($obj->query_lang && $alchemy_api_key){
     $lang = file_get_contents('http://access.alchemyapi.com/calls/text/TextGetLanguage?apikey='.$alchemy_api_key.'&outputMode=json&text='.urlencode($entry->message));
     $lang = json_decode($lang, true);
    }
    if(!$obj->query_lang || !$alchemy_api_key || ($obj->query_lang && $obj->query_lang == $lang['iso-639-1'])){
     $query = 'INSERT INTO '.$prefix.'search 
  	                   SET query_id = '.$obj->query_id.',
					       search_outer_id = "'.$entry->id.'",
						   search_source = "facebook",
						   search_published = '.strtotime($entry->created_time).',
						   search_title = "'.addslashes($entry->name).'",
						   search_content = "'.addslashes($entry->message).'",
						   search_author_name = "'.addslashes($entry->from->name).'"';
	 
     mysql_query($query);
     $query = 'INSERT INTO '.$prefix.'search_influencers
					   SET query_id = '.$obj->query_id.',
    					   search_author_name = "'.addslashes($entry->from->name).'",
    					   cnt = 1
   ON DUPLICATE KEY UPDATE cnt = cnt + 1';
	 mysql_query($query);
    }
   }
   sleep(2);
  }

  function getLastPostDate($query_id){
   global $prefix;
   
   $query = 'SELECT search_published FROM '.$prefix.'search WHERE search_source = "facebook" AND query_id = '.$query_id.' ORDER BY search_published DESC LIMIT 0, 1';
   $res = mysql_query($query);
   $date1 = $res && mysql_num_rows($res) ? mysql_result($res, 0, 0) : 0;

   $query = 'SELECT index_date + 86400 FROM '.$prefix.'search_index WHERE index_source = "facebook" AND query_id = '.$query_id.' ORDER BY index_date DESC LIMIT 0, 1';
   $res = mysql_query($query);
   $date2 = $res && mysql_num_rows($res) ? mysql_result($res, 0, 0) : 0;

   return $date1 > $date2 ? $date1 : $date2;
  }
 }

?>