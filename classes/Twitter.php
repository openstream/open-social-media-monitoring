<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 class Twitter{
  function __construct($obj) {
   global $prefix;
   
   $twitter = file_get_contents('http://search.twitter.com/search.atom?q='.urlencode($obj->query_q).($obj->query_geocode ? '&geocode='.$obj->query_geocode : '').'&rpp=100&result_type=recent&since_id='.$this->getLastTwittId($obj->query_id).($obj->query_lang ? '&lang='.$obj->query_lang : ''));
   $xml = simplexml_load_string(preg_replace('/\r/ism', '', $twitter));
   $json = @json_decode(@json_encode($xml),1);
   if(is_array($json['entry']) && isset($json['entry']['id'])){
    $temp = $json['entry'];
    $json['entry'] = Array();
    $json['entry'][] = $temp;
   }
   while(is_array($json['entry']) && list(,$entry) = each($json['entry'])){
    $query = 'INSERT INTO '.$prefix.'search 
	                  SET query_id = '.$obj->query_id.',
					      search_outer_id = "'.$entry['id'].'",
						  search_source = "twitter",
						  search_published = '.strtotime($entry['published']).',
						  search_title = "'.addslashes($entry['title']).'",
						  search_content = "'.addslashes($entry['content']).'",
						  search_author_name = "'.addslashes($entry['author']['name']).'",
						  search_author_uri = "'.$entry['author']['uri'].'"';
    mysql_query($query);
	$search_id = mysql_insert_id();
    while(is_array($entry['link']) && list(,$link) = each($entry['link'])){
     $query = 'INSERT INTO '.$prefix.'search_link 
                       SET search_id = '.$search_id.',
                           search_link_str = "'.addslashes(json_encode($link)).'"';
	 mysql_query($query);
    }
   }
  } 

  function getLastTwittId($query_id){
   global $prefix;
   
   $query = 'SELECT search_outer_id FROM '.$prefix.'search WHERE search_source = "twitter" AND query_id = '.$query_id.' ORDER BY search_published DESC LIMIT 0, 1';
   $res = mysql_query($query);
   return $res && mysql_num_rows($res) ? preg_replace('/^.*:/ism', '', mysql_result($res, 0, 0)) : 0;
  }
 }

?>