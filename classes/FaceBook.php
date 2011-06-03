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
   global $prefix;
   
   $facebook = file_get_contents('https://graph.facebook.com/search?q='.urlencode($obj->query_q).'&type=post&limit=100&since='.$this->getLastPostDate());
   $facebook =  json_decode($facebook);
   while(is_array($facebook->data) && list(,$entry) = each($facebook->data)){
    if($obj->query_lang){
     $lang = file_get_contents('https://ajax.googleapis.com/ajax/services/language/detect?v=1.0&q='.urlencode($entry->message));
     $lang = json_decode($lang);
     $lang = $lang->responseData->language;
    }
    if(!$obj->query_lang || ($obj->query_lang && $obj->query_lang == $lang)){
     $query = 'INSERT INTO '.$prefix.'search SET query_id = '.$obj->query_id;
     $query = 'INSERT INTO '.$prefix.'search 
  	                   SET query_id = '.$obj->query_id.',
					       search_outer_id = "'.$entry->id.'",
						   search_source = "facebook",
						   search_published = '.strtotime($entry->created_time).',
						   search_title = "'.$entry->name.'",
						   search_content = "'.$entry->message.'",
						   search_author_name = "'.$entry->from->name.'"';
	 
     mysql_query($query);
    }
   }
  }

  function getLastPostDate(){
   global $prefix;
   
   $query = 'SELECT search_published FROM '.$prefix.'search WHERE search_source = "facebook" ORDER BY search_published DESC LIMIT 0, 1';   
   $res = mysql_query($query);
   return $res && mysql_num_rows($res) ? preg_replace('/^.*:/ism', '', mysql_result($res, 0, 0)) : 0;
  }
 }

?>