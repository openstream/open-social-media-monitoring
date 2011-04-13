<?php

 class Facebook extends SocialNetwork{
  function __construct($obj){
   global $prefix;
   $facebook = file_get_contents('https://graph.facebook.com/search?q='.$obj->query_q.'&type=post&limit=100&since='.$this->getLastPostDate());
   $facebook =  json_decode($facebook);
   while(is_array($facebook->data) && list(,$entry) = each($facebook->data)){
    if($obj->query_lang){
     $lang = file_get_contents('https://ajax.googleapis.com/ajax/services/language/detect?v=1.0&q='.urlencode($entry->message));
     $lang = json_decode($lang);
     $lang = $lang->responseData->language;
    }
    if(!$obj->query_lang || ($obj->query_lang && $obj->query_lang == $lang)){
     $query = 'INSERT INTO '.$prefix.'search SET query_id = '.$obj->query_id;
     mysql_query($query);
     $search_id = mysql_insert_id();
     $this->saveEntity($search_id, 'id', $entry->id);   
     $this->saveEntity($search_id, 'source', 'facebook');
     $this->saveEntity($search_id, 'published', strtotime($entry->created_time));
     $this->saveEntity($search_id, 'title', $entry->name);
     $this->saveEntity($search_id, 'content', $entry->message);
     $this->saveEntity($search_id, 'updated', strtotime($entry->updated_time));
     $this->saveEntity($search_id, 'author-name', $entry->from->name);
    }
   }
  }

  function getLastPostDate(){
   global $prefix;
   $query = 'SELECT e2.search_entity_value
               FROM '.$prefix.'search_entity e1
         INNER JOIN '.$prefix.'search_entity e2 ON e1.search_id = e2.search_id
              WHERE e1.search_entity_name = "source"
                AND e1.search_entity_value = "facebook"
                AND e2.search_entity_name = "published"
           ORDER BY e2.search_entity_value DESC
              LIMIT 0, 1';
   $res = mysql_query($query);
   return $res && mysql_num_rows($res) ? preg_replace('/^.*:/ism', '', mysql_result($res, 0, 0)) : 0;
  }
 }

?>