<?php

 class SocialNetwork{
  function saveEntity($search_id, $entity_name, $entity_value){
   global $prefix;
   $query = 'INSERT INTO '.$prefix.'search_entity 
                     SET search_id = '.$search_id.',
                         search_entity_name = "'.addslashes($entity_name).'",
                         search_entity_value = "'.addslashes($entity_value).'"';
   mysql_query($query);
  }
 }

?>