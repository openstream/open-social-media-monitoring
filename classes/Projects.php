<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

 class Projects extends Application{

     /*
     *  Prints out the XML of search results for defined queries
     *
     *  @param string
     *  @return void
     */
     function wirexmlAction(){
         global $prefix;

         // Array indexes are 0-based, jCarousel positions are 1-based.
         $first = max(0, intval($_GET['first']) - 1);
         $last  = max($first + 1, intval($_GET['last']) - 1);

         $from = strtotime(date('Y-m-d', $_GET['from']));
         $to = strtotime(date('Y-m-d', $_GET['to']));

         $query = 'SELECT *
               FROM '.$prefix.'search
              WHERE query_id IN ('.urldecode($_GET['ids']).')
				AND search_published > '.$from.'
				AND search_published < '.($to + 24*3600).'
		   ORDER BY search_published DESC';

         $res = mysql_query($query);
         $cnt = 0;

         header('Content-Type: text/xml');
         echo '<data>'."\n".'<total>'.mysql_num_rows($res).'</total>'."\n";

         while($res && $search = mysql_fetch_object($res)){
             if($cnt >= $first && $cnt <= $last){
                 echo '<node>'."\n".'<source>'.$search->search_source.'</source>'."\n";
                 if($search->search_source == 'facebook'){
                     echo '<link>http://www.facebook.com/profile.php?id='.preg_replace('/_.*$/', '', $search->search_outer_id).'</link>'."\n".
                         '<image>images/fb.jpg</image>'."\n";
                 }else{
                     echo '<link>https://www.twitter.com/'.$search->search_author_name.'</link>'."\n".
                         '<image>'.$search->search_author_image.'</image>'."\n";
                 }
                 echo preg_replace('/\&/ism', '&amp;', '<author>'.stripslashes($search->search_author_name).'</author>'."\n".'<date>'.date('F jS, Y H:i', $search->search_published).'</date>'."\n".'<content>'.substr(strip_tags(stripslashes($search->search_content)), 0, 200).'</content>')."\n";
                 echo '</node>'."\n";
             }
             $cnt++;
         }
         echo '</data>';
     }

     /*
     *  Displays queries list screen
     *
     *  @param int
     */
     function queriesAction($campaign_id = 0){
         /** @var $campaign Model_Campaign */
         $campaign = Application::getSingleton('campaign');
         $campaign->load($campaign_id);
         /** @var $view Block_Campaign */
         $view = Application::getBlock('keyword');
         $view->processBlock('keyword/list');
         $view->renderLayout();
     }

     /*
     *  Displays edit query screen
     *
     *  @param int, mixed
     *  @return void
     */
     function editqueryAction($campaign_id, $query_id = 0){
         /** @var $campaign Model_Campaign */
         $campaign = Application::getSingleton('campaign');
         $campaign->load($campaign_id);
         /** @var $campaign Model_Keyword */
         $campaign = Application::getSingleton('keyword');
         $campaign->load($query_id);
         /** @var $view Block_Campaign */
         $view = Application::getBlock('keyword');
         $view->processBlock('keyword/edit');
         $view->renderLayout();
     }

     /*
     *  Deletes queries, searches, links and relations
     *
     *  @param array
     *  @return void
     */
     function deleteAction($project_id, $query_id = 0){
         global $prefix;

         if(!$query_id){
             // If only $project_id is passed we assuming that whole project have to be deleted
             $query = 'SELECT query_id FROM '.$prefix.'project_to_query WHERE project_id = '.(int)$project_id;
             $res = mysql_query($query);
             $query_array = array();
             while($res && $query = mysql_fetch_object($res)){
                 $query_array[] = $query->query_id;
             }
             $query = 'DELETE FROM '.$prefix.'project WHERE project_id = '.(int)$project_id;
             mysql_query($query);
         }else{
             $query_array = array((int)$query_id);
         }

         if(is_array($query_array)){
             $this->deleteSearchNodes($query_array);
             $query_array = implode(', ', $query_array);
             $query = 'DELETE FROM '.$prefix.'query WHERE query_id IN ('.$query_array.')';
             mysql_query($query);
             $query = 'DELETE FROM '.$prefix.'project_to_query WHERE query_id IN ('.$query_array.')';
             mysql_query($query);
         }

         header('Location: '.($query_id ? $this->getUrl('projects/queries/'.$project_id) : $this->getUrl('projects')));
     }

     /*
     *  Deletes searches of queries array
     *
     *  @param array
     *  @return void
     */
     function deleteSearchNodes($query_array){
         global $prefix;

         if(is_array($query_array)){
             $query_array = implode(', ', $query_array);
             $query = 'SELECT search_id FROM '.$prefix.'search WHERE query_id IN ('.$query_array.')';
             $res = mysql_query($query);
             $search_array = array();
             while($res && $search = mysql_fetch_object($res)){
                 $search_array[] = $search->search_id;
             }
             $query = 'DELETE FROM '.$prefix.'search WHERE search_id IN ('.implode(', ', $search_array).')';
             mysql_query($query);
         }
     }

     /**
     * Displays projects list screen
     */
    function defaultAction(){
        /** @var $view Block_Campaign */
        $view = Application::getBlock('campaign');
        $view->processBlock('campaign/list');
        $view->renderLayout();
    }

    /**
     * Deletes edit project screen
     *
     * @param int $id
     */
    function editAction($campaign_id = 0){
        /** @var $campaign Model_Campaign */
        $campaign = Application::getSingleton('campaign');
        $campaign->load($campaign_id);
        /** @var $view Block_Campaign */
        $view = Application::getBlock('campaign');
        $view->processBlock('campaign/edit');
        $view->renderLayout();
    }

     /*
     *  Stores project changes
     *
     *  @param none
     *  @return void
     */
     function saveAction(){
         global $prefix;

         $query = 'SELECT * FROM '.$prefix.'project WHERE project_id = '.$_POST['id'];
         $res = mysql_query($query);
         if($res && mysql_num_rows($res)){
             $query = 'UPDATE '.$prefix.'project
                  SET project_name = "'.$_POST['project_name'].'"
                WHERE project_id = "'.$_POST['id'].'"';
         }else{
             $query = 'INSERT INTO '.$prefix.'project
                  SET project_name = "'.$_POST['project_name'].'"';
         }
         mysql_query($query);
         header('Location: '.$this->getUrl('projects'));
     }

     /*
     *  Change project status to opposite
     *
     *  @param int
     *  @return void
     */
     function statusAction($project_id){
         global $prefix;

         $query = 'UPDATE '.$prefix.'project SET project_status = NOT project_status WHERE project_id = '.$project_id;
         mysql_query($query);
         header('Location: '.$this->getUrl('projects'));
     }

     /*
     *  Requests Geocode from twitter geo/search API
     *
     *  @param string, int, string
     *  @return string
     */

     private function getGeocode( $query_nearplace, $query_distance, $query_distanceunit ) {

         if( empty( $query_nearplace ) )
             return '';

         // Twitter geo/search request
         $twitter = file_get_contents( 'http://api.twitter.com/1/geo/search.json?query='.urlencode($query_nearplace) );
         $json = @json_decode($twitter, true);

         // Number of places returned
         $nrPlaces = count( $json['result']['places'] );
         $place = 0;

         // Get the index of the first place with the place_type city
         for( $i = 0; $i < $nrPlaces; $i++){
             if( $json['result']['places'][$i]['place_type'] == 'city' ){
                 $place = $i;
                 break;
             }
         }

         // Get the coordinates of the found city
         $coordinates = $json['result']['places'][$place]['bounding_box']['coordinates'][0];

         $nrCoords = count( $coordinates );
         $long = 0.0;
         $lat  = 0.0;

         // Calculate the middle of the city area
         for( $i = 0; $i < $nrCoords; $i++ ){
             $long += $coordinates[$i][0];
             $lat  += $coordinates[$i][1];
         }
         $long /= $nrCoords;
         $lat  /= $nrCoords;

         //return the geocode
         return $lat.','.$long.','.$query_distance.$query_distanceunit;
     }

     /*
     *  Stores query changes
     *
     *  @param none
     *  @return void
     */
     function savequeryAction(){
         global $prefix;

         $query_geocode = $this->getGeocode( $_POST['query_nearplace'], $_POST['query_distance'], $_POST['query_distanceunit']);

         $query = 'SELECT * FROM '.$prefix.'query WHERE query_id = '.$_POST['id'];
         $res = mysql_query($query);
         if($res && mysql_num_rows($res)){
             $this->deleteSearchNodes(array($_POST['id']));
             $query = 'UPDATE '.$prefix.'query
                 SET query_q = "'.$_POST['query_q'].'",
                     query_lang = "'.$_POST['query_lang'].'",
                     query_geocode = "'.$query_geocode.'",
					 query_nearplace = "'.$_POST['query_nearplace'].'",
					 query_distance  = "'.$_POST['query_distance'].'",
					 query_distanceunit = "'.$_POST['query_distanceunit'].'"
               WHERE query_id = "'.$_POST['id'].'"';
             mysql_query($query);
         }elseif(isset($_POST['project_id']) && (int)$_POST['project_id']){
             $query = 'INSERT INTO '.$prefix.'query
                 SET query_q = "'.$_POST['query_q'].'",
                     query_lang = "'.$_POST['query_lang'].'",
                     query_geocode = "'.$query_geocode.'",
					 query_nearplace = "'.$_POST['query_nearplace'].'",
					 query_distance  = "'.$_POST['query_distance'].'",
					 query_distanceunit = "'.$_POST['query_distanceunit'].'"';
             mysql_query($query);
             $query = 'INSERT INTO '.$prefix.'project_to_query
	                  SET project_id = '.(int)$_POST['project_id'].',
					      query_id = '.mysql_insert_id();
             mysql_query($query);
         }
         header('Location: '.$this->getUrl('projects/queries/'.$_POST['project_id']));
     }

     /**
     * Displays results page
     *
     * @param $type
     * @param $id
     */
    function resultsAction($type, $id){
        if($type == 'project') {
            /** @var $campaign Model_Campaign */
            $campaign = Application::getSingleton('campaign');
            $campaign->load($id);
        }else{
            /** @var $keyword Model_Keyword */
            $keyword = Application::getSingleton('keyword');
            $keyword->load($id);
        }

        /** @var $view Block_Campaign */
        $view = Application::getBlock('campaign/results');
        $view->processBlock('campaign/results/graph');
        $view->processBlock('campaign/results/influencers');
        $view->processBlock('campaign/results/wire');
        $view->renderLayout();
    }
 }