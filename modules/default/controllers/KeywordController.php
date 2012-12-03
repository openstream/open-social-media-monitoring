<?php

class KeywordController extends Zend_Controller_Action
{
    public function preDispatch()
    {
        $bootstrap = $this->getInvokeArg('bootstrap');
        $config = $bootstrap->getOptions();
        $defaultNamespace = new Zend_Session_Namespace('Default');
        if ($defaultNamespace->email != $config['authentication']['email'] || $defaultNamespace->password != $config['authentication']['password']) {
            $this->_helper->redirector('index', 'index', 'login');
        }
    }

    public function indexAction()
    {
        $keywords = new Default_Model_DbTable_Keyword();
        $this->view->campaign_id = $this->getRequest()->getParam('campaign');
        $this->view->keywords = $keywords->getKeywords($this->getRequest()->getParam('campaign'));
    }

    public function editAction()
    {
        $this->view->campaign_id = $this->getRequest()->getParam('campaign');

        if ($this->getRequest()->getParam('id')) {
            $model = new Default_Model_DbTable_Keyword();
            $this->view->keyword = $model->fetchRow(array('query_id = ?' => $this->getRequest()->getParam('id')));
        } else {
            $this->view->keyword = new ArrayObject();
            $this->view->keyword->query_id = '';
            $this->view->keyword->query_q = '';
            $this->view->keyword->query_nearplace = '';
            $this->view->keyword->query_distance = '';
            $this->view->keyword->query_distanceunit = '';
        }

        $this->view->country_options = '<option value="">none</option>';
        $locale = new Zend_Locale('en_US');
        $countries = $locale->getTranslationList('Territory', 'en', 2);
        asort($countries, SORT_LOCALE_STRING);
        while (list($iso, $name) = each($countries)) {
            $iso = strtolower($iso);
            $this->view->country_options .= '<option value="' . $iso . '"' . (isset($this->view->keyword['query_lang']) && $iso == $this->view->keyword['query_lang'] ? ' selected' : '').'>'.$name.'</option>';
        }
    }

    public function saveAction()
    {
        if ($this->getRequest()->getParam('query_nearplace') && $this->getRequest()->getParam('query_distance')) {
            // Twitter geo/search request
            $twitter = file_get_contents( 'http://api.twitter.com/1/geo/search.json?query='.urlencode($this->getRequest()->getParam('query_nearplace')) );
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

            $geocode = $lat . ',' . $long . ',' . $this->getRequest()->getParam('query_distance') . $this->getRequest()->getParam('query_distanceunit');
        } else {
            $geocode = '';
        }

        $data = array(
            'query_id' => $this->getRequest()->getParam('id'),
            'query_q' => $this->getRequest()->getParam('query_q'),
            'query_lang' => $this->getRequest()->getParam('query_lang'),
            'query_geocode' => $geocode,
            'query_nearplace' => $this->getRequest()->getParam('query_nearplace'),
            'query_distance' => $this->getRequest()->getParam('query_distance'),
            'query_distanceunit' => $this->getRequest()->getParam('query_distanceunit')
        );
        $model = new Default_Model_DbTable_Keyword();
        $new_id = $model->save($data);

        if ($this->getRequest()->getParam('id')) {
            /* Delete Search Nodes */
            $table = new Default_Model_DbTable_Search();
            $where = $table->getAdapter()->quoteInto('query_id = ?', $this->getRequest()->getParam('id'));
            $table->delete($where);
        } elseif ($new_id) {
            $data = array(
                'project_id'    => $this->getRequest()->getParam('campaign_id'),
                'query_id'      => $new_id
            );

            $table = new Default_Model_DbTable_KeywordToCampaign();
            $table->insert($data);
        }

        $this->_helper->redirector('index', 'keyword', 'default', array('campaign' => $this->getRequest()->getParam('campaign_id')));
    }

    public function deleteAction()
    {
        if ($this->getRequest()->getParam('id')) {
            /* Delete keyword itself */
            $table = new Default_Model_DbTable_Keyword();
            $where = $table->getAdapter()->quoteInto('query_id = ?', $this->getRequest()->getParam('id'));
            $table->delete($where);

            /* Delete keyword to campaign relation */
            $table = new Default_Model_DbTable_KeywordToCampaign();
            $where = $table->getAdapter()->quoteInto(array(
                'query_id = ?' => $this->getRequest()->getParam('id'),
                'project_id = ?' => $this->getRequest()->getParam('campaign')
            ));
            $table->delete($where);

            /* Delete search results for this query */
            $table = new Default_Model_DbTable_Search();
            $where = $table->getAdapter()->quoteInto('query_id = ?', $this->getRequest()->getParam('id'));
            $table->delete($where);
        }

        $this->_helper->redirector('index', 'keyword', 'default', array('campaign' => $this->getRequest()->getParam('campaign')));
    }
}