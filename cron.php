<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 include('settings.php');
 
 $rd = @mysql_connect($dbHost, $dbUser, $dbPassword);
 mysql_select_db($dbName, $rd);

 $active_queries = array();
 $query = 'SELECT q.*
	         FROM '.$prefix.'project_to_query p2q
	   INNER JOIN '.$prefix.'project p ON p2q.project_id = p.project_id
	   INNER JOIN '.$prefix.'query q ON q.query_id = p2q.query_id
	   		WHERE p.project_status = 1';
 $res = mysql_query($query);

while($res && $obj = mysql_fetch_object($res)){
    $active_queries[] = $obj->query_id;

    /**
     *  Twitter
     */
    $last_tweet_id = 0;
    $base_url = 'http://search.twitter.com/search.json';
    $parameters = array(
        'q'             => $obj->query_q,
        'geocode'       => $obj->query_geocode,
        'rpp'           => 100,
        'result_type'   => 'recent',
        'since_id'      => $obj->query_last_twitter,
        'lang'          => $obj->query_lang
    );
    $response = _get_file_contents($base_url, $parameters, true);
    while(is_object($response) && isset($response->results) && is_array($response->results) && list(,$entry) = each($response->results)) {
        if(!$last_tweet_id){
            $last_tweet_id = $entry->id_str;
            $query = 'UPDATE '.$prefix.'query SET query_last_twitter = "'.$last_tweet_id.'"';
            mysql_query($query);
        }
        $query = 'INSERT INTO '.$prefix.'search
	                  SET query_id = '.$obj->query_id.',
					      search_outer_id = "'.$entry->id_str.'",
						  search_source = "twitter",
						  search_published = '.strtotime($entry->created_at).',
						  search_content = "'.addslashes($entry->text).'",
						  search_author_name = "'.addslashes($entry->from_user).'",
						  search_author_image = "'.$entry->profile_image_url.'"';
        mysql_query($query);

        $query = 'INSERT INTO '.$prefix.'search_influencers
    				  SET query_id = '.$entry->query_id.',
    					  search_author_name = "'.$entry->from_user.'",
    					  search_source = "twitter",
    					  cnt = 1
  ON DUPLICATE KEY UPDATE cnt = cnt + 1';
        mysql_query($query);
    }

    /**
     *  Facebook
     */
    $last_facebook_post_time = 0;
    $base_url = 'https://graph.facebook.com/search';
    $parameters = array(
        'q'     => $obj->query_q,
        'type'  => 'post',
        'limit' => 100,
        'since' => $obj->query_last_facebook
    );
    $response = _get_file_contents($base_url, $parameters, true);
    $alchemy_base_url = 'http://access.alchemyapi.com/calls/text/TextGetLanguage';
    $lang = array();
    while(is_object($response) && isset($response->data) && is_array($response->data) && list(,$entry) = each($response->data)){
        $text = $entry->message ? $entry->message : $entry->story;
        if($obj->query_lang && $alchemy_api_key){
            $parameters = array(
                'apikey'        => $alchemy_api_key,
                'outputMode'    => 'json',
                'text'          => $text
            );
            $lang = _get_file_contents($alchemy_base_url, $parameters, true, true);
        }
        if(!$obj->query_lang || !$alchemy_api_key || ($obj->query_lang && $obj->query_lang == $lang['iso-639-1'])){
            $created_time = strtotime($entry->created_time);
            if(!$last_facebook_post_time){
                $last_facebook_post_time = date('n/j/Y H:i:s', $created_time);
                $query = 'UPDATE '.$prefix.'query SET query_last_facebook = "'.$last_facebook_post_time.'"';
                mysql_query($query);
            }
            $query = 'INSERT INTO '.$prefix.'search
                   SET query_id = '.$obj->query_id.',
                       search_outer_id = "'.$entry->id.'",
                       search_source = "facebook",
                       search_published = '.$created_time.',
                       search_title = "'.addslashes($entry->name).'",
                       search_content = "'.$text.'",
                       search_author_name = "'.addslashes($entry->from->name).'"';
            mysql_query($query);

            $query = 'INSERT INTO '.$prefix.'search_influencers
                   SET query_id = '.$obj->query_id.',
                       search_author_name = "'.addslashes($entry->from->name).'",
                       search_source => "facebook",
                       cnt = 1
ON DUPLICATE KEY UPDATE cnt = cnt + 1';
            mysql_query($query);
        }
    }
}
 
 // Archiving expired entries.
 $query = 'SELECT * FROM '.$prefix.'search WHERE query_id IN ('.implode(', ', $active_queries).') AND search_published < '.(time() - $keep_history*24*3600);
 $re0 = mysql_query($query);
 if($re0 && $num_rows = mysql_num_rows($re0)){
  echo 'Archiving '.$num_rows.' entries.'."\n";
  while($a_obj = mysql_fetch_object($re0)){
   echo '.';
   $date = mktime(0, 0, 0, date('n', $a_obj->search_published), date('j', $a_obj->search_published), date('Y', $a_obj->search_published));
   $query = 'INSERT INTO '.$prefix.'search_index (query_id, index_date, index_source, index_count)
                  VALUES ('.$a_obj->query_id.', '.$date.', "'.$a_obj->search_source.'", 1)
 ON DUPLICATE KEY UPDATE index_count = index_count+1';
   mysql_query($query);
   $query = 'DELETE FROM '.$prefix.'search WHERE search_id = '.$a_obj->search_id;
   mysql_query($query);
  }
  $query = 'OPTIMIZE TABLE '.$prefix.'search';
  mysql_query($query);
  echo "\n";
 }

 if(!(int)date('G')){ // If hour == 0, sending email digest
  $m = new MIMEMail($adminEmail, $defaultFrom, 'Daily overview of your projects');
  $email_html = '<html><body>logo here<hr/><br/>Project(s) in this email:<br/><ul>';
  $projects = array();
  $query = 'SELECT * FROM '.$prefix.'project';
  $res = mysql_query($query);
  while($res && $project = mysql_fetch_object($res)){
   $email_html .= '<li><a href="#'.$project->project_id.'">'.$project->project_name.'</a></li>';
   $projects[] = $project;
  }
  $email_html .= '</ul><br/><br/>';
  foreach($projects as $project){
   $email_html .= '<a name="'.$project->project_id.'"></a>Project: '.$project->project_name.'<hr/>'.breakdown_block($project->project_id, 'twitter').breakdown_block($project->project_id, 'facebook').'<br/>';
  }
  $email_html .= '</body></html>';
  $m->mailbody(strip_tags(preg_replace('#<br\s*/?>|<hr\s*/?>|</ul>|</li>|</h\d>#i', "\n", $email_html)), $email_html);
  $m->send();
 }

 function breakdown_block($project_id, $source){
  global $prefix;
  
  $query = 'SELECT s.*
	          FROM '.$prefix.'project_to_query p2q
	    INNER JOIN '.$prefix.'project p ON p2q.project_id = p.project_id
		INNER JOIN '.$prefix.'query q ON q.query_id = p2q.query_id
		INNER JOIN '.$prefix.'search s ON s.query_id = p2q.query_id
			 WHERE p2q.project_id = '.$project_id.'
			   AND s.search_source = "'.$source.'"
			   AND s.search_published > '.(time() - 3600*24);
  $res = mysql_query($query);
  $ret = '<h2>'.ucfirst($source).' Breakdown</h2><br/>Total mentions in the past 24 hours: '.mysql_num_rows($res).'<br/><br/>'.(mysql_num_rows($res) ? 'Latest entries:' : '').'<ul>';
  $cnt = 0;
  while($res && $cnt++ < 10 && $obj = mysql_fetch_object($res)){
   $ret .= '<li>'.$obj->search_author_name.': '.substr(strip_tags($obj->search_content), 0, 200).'</li>';
  }
  $ret .= '</ul>';
  
  return $ret;
 }

function _get_file_contents($url, $parameters, $json_decode = false, $json_assoc = false)
{
    $url = _appendQueryParams($url, $parameters);
    $response = file_get_contents($url);
    if($json_decode){
        $response = @json_decode($response, $json_assoc);
    }
    return $response;
}

/**
 * Append the array of parameters to the given URL string
 *
 * @param string $url
 * @param array $params
 * @return string
 */
function _appendQueryParams($url, array $params)
{
    foreach ($params as $k => $v) {
        if(trim($v)){
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= sprintf("%s=%s", $k, urlencode(trim($v)));
        }
    }
    return $url;
}