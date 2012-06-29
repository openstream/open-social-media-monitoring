<?php

class Model_Campaign extends Model_Abstract
{
    public function __construct()
    {
        $this->_mainTable = 'project';
        $this->_primaryKey = 'project_id';
    }

    /*
    *  Gets list of query IDs for campaign
    *
    *  @param int, mixed, mixed
    *  @return array
    */
    public function getQueryIds(&$results_cnt = NULL, &$query_names = NULL){
        global $prefix;

        $query = 'SELECT q.*
	                FROM '.$prefix.'project_to_query p2q
	 	      INNER JOIN '.$prefix.'query q ON p2q.query_id = q.query_id
	 	           WHERE p2q.project_id = '.(int)$this->getId();
        $res = mysql_query($query);
        while($res && $query = mysql_fetch_object($res)){
            $query_ids[] = $query->query_id;
            if(is_array($results_cnt)){
                $results_cnt[$query->query_id] = array();
            }
            if(is_array($query_names)){
                $query_names[$query->query_id] = $query->query_q.($query->query_lang ? ' ('.$query->query_lang.')' : '');
            }
        }
        return isset($query_ids) ? implode(', ', $query_ids) : '';
    }
}