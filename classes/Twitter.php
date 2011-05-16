<?php

 class Twitter extends SocialNetwork{
  function __construct($obj) {
   global $prefix;
   $twitter = file_get_contents('http://search.twitter.com/search.atom?q='.urlencode($obj->query_q).'&rpp=100&result_type=recent&since_id='.$this->getLastTwittId().($obj->query_lang ? '&lang='.$obj->query_lang : ''));
   $xml = simplexml_load_string($twitter);
   $json = @json_decode(@json_encode($xml),1);
   if(is_array($json['entry']) && isset($json['entry']['id'])){
    $temp = $json['entry'];
    $json['entry'] = Array();
    $json['entry'][] = $temp;
   }
   while(is_array($json['entry']) && list(,$entry) = each($json['entry'])){
    $query = 'INSERT INTO '.$prefix.'search SET query_id = '.$obj->query_id;
    mysql_query($query);
    $search_id = mysql_insert_id();
    $this->saveEntity($search_id, 'id', $entry['id']);
    $this->saveEntity($search_id, 'source', 'twitter');
    $this->saveEntity($search_id, 'published', strtotime($entry['published']));
    $this->saveEntity($search_id, 'title', $entry['title']);
    $this->saveEntity($search_id, 'content', $entry['content']);
    $this->saveEntity($search_id, 'updated', strtotime($entry['updated']));
    $this->saveEntity($search_id, 'author-name', $entry['author']['name']);
    $this->saveEntity($search_id, 'author-uri', $entry['author']['uri']);
    while(is_array($entry['link']) && list(,$link) = each($entry['link'])){
     $this->saveEntity($search_id, 'link', json_encode($link));
    }
   }
  } 

  function getLastTwittId(){
   global $prefix;
   $query = 'SELECT e2.search_entity_value
               FROM '.$prefix.'search_entity e1
         INNER JOIN '.$prefix.'search_entity e2 ON e1.search_id = e2.search_id
         INNER JOIN '.$prefix.'search_entity e3 ON e1.search_id = e3.search_id
              WHERE e1.search_entity_name = "source"
                AND e1.search_entity_value = "twitter"
                AND e2.search_entity_name = "id"
                AND e3.search_entity_name = "published"
           ORDER BY e3.search_entity_value DESC
              LIMIT 0, 1';
   $res = mysql_query($query);
   return $res && mysql_num_rows($res) ? preg_replace('/^.*:/ism', '', mysql_result($res, 0, 0)) : 0;
  }
 }

?>