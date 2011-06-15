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

 $query = 'SELECT * FROM '.$prefix.'query';
 $res = mysql_query($query);
 while($res && $obj = mysql_fetch_object($res)){
  new Twitter($obj);
  new FaceBook($obj);
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

 function __autoload($class_name) {
   require_once(dirname(__file__).'/classes/'.$class_name.'.php');
 }
  
?>