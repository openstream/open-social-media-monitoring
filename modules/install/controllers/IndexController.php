<?php
class Install_IndexController extends Zend_Controller_Action
{
    public function indexAction()
    {
        $defaultNamespace = new Zend_Session_Namespace('Default');

        /** @var $bootstrap Bootstrap */
        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->view->options = isset($defaultNamespace->options) ? $defaultNamespace->options : $bootstrap->getOptions();

        $this->view->error = $defaultNamespace->error;
        unset($defaultNamespace->error);

        $frontControllerDir = $this->getFrontController()->getControllerDirectory('settings');
        $this->view->addBasePath(realpath($frontControllerDir . '/../views'));
        $this->view->render('index/index.phtml');
    }

    public function processAction()
    {
        $error = array();
        $defaultNamespace = new Zend_Session_Namespace('Default');

        $options = array(
            'authentication' => array(
                'email'     => $this->getRequest()->getParam('admin_email'),
                'password'  => $this->getRequest()->getParam('admin_password')
            ),
            'settings' => array(
                'default_from'      => $this->getRequest()->getParam('default_from'),
                'keep_history'      => $this->getRequest()->getParam('keep_history'),
                'alchemy_api_key'   => $this->getRequest()->getParam('alchemy_api_key')
            ),
            'resources' => array('db' => array(
                'adapter'   => 'PDO_MYSQL',
                'params'    => array(
                    'host'      => $this->getRequest()->getParam('db_host'),
                    'username'  => $this->getRequest()->getParam('db_user'),
                    'password'  => $this->getRequest()->getParam('db_password'),
                    'dbname'    => $this->getRequest()->getParam('db_name'),
                    'prefix'    => $this->getRequest()->getParam('db_prefix')
                )
            ))
        );

        $validator = new Zend_Validate_EmailAddress();
        if (!$validator->isValid($options['authentication']['email'])) {
            $error['admin_email'] = 'Administrator e-mail is invalid';
        }
        if (!trim($options['authentication']['password'])) {
            $error['admin_password'] = 'Administrator password can not be blank';
        }
        if (!$validator->isValid($options['settings']['default_from'])) {
            $error['default_from'] = 'Default "from" e-mail is invalid';
        }
        if (!trim($options['resources']['db']['params']['host'])) {
            $error['db_host'] = 'Database host can not be blank';
        }
        if (!trim($options['resources']['db']['params']['username'])) {
            $error['db_user'] = 'Database user can not be blank';
        }
        if (!trim($options['resources']['db']['params']['dbname'])) {
            $error['db_name'] = 'Database name can not be blank';
        }
        if ((int)$options['settings']['keep_history'] <= 0) {
            $error['keep_history'] = 'Timeline display length have to be positive integer';
        }
        if (!$db = new Zend_Db_Adapter_Pdo_Mysql(array(
            'host'      => $options['resources']['db']['params']['host'],
            'username'  => $options['resources']['db']['params']['username'],
            'password'  => $options['resources']['db']['params']['password'],
            'dbname'    => $options['resources']['db']['params']['dbname']
        ))) {
            $error[] = 'Incorrect database connection details';
        }

        if (count($error)) {
            /**
             * Redirect back
             */
            $defaultNamespace->error = $error;
            $defaultNamespace->options = $options;
            $this->_helper->redirector('index', 'index', 'install');
        } else {
            /**
             * Execute db queries
             */
            $schemaSql = '
CREATE TABLE IF NOT EXISTS '.$options['resources']['db']['params']['prefix'].'project (
  project_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  project_name varchar(255) NOT NULL,
  project_status TINYINT(1) UNSIGNED NOT NULL DEFAULT \'1\',
  PRIMARY KEY (project_id)
);

CREATE TABLE IF NOT EXISTS '.$options['resources']['db']['params']['prefix'].'project_to_query (
  project_id int(10) unsigned NOT NULL,
  query_id int(10) unsigned NOT NULL,
  UNIQUE KEY project_id (project_id,query_id)
);

CREATE TABLE IF NOT EXISTS '.$options['resources']['db']['params']['prefix'].'query (
  query_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  query_q varchar(255) NOT NULL,
  query_lang char(2) NOT NULL,
  query_geocode varchar(255) NOT NULL,
  query_nearplace varchar(255) NOT NULL,
  query_distance int(10) NOT NULL,
  query_distanceunit varchar(30) NOT NULL,
  query_last_twitter varchar(255) NOT NULL,
  query_last_facebook varchar(255) NOT NULL,
  PRIMARY KEY (query_id)
);

CREATE TABLE IF NOT EXISTS '.$options['resources']['db']['params']['prefix'].'search (
  search_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  query_id int(10) unsigned NOT NULL,
  search_outer_id VARCHAR( 255 ) NOT NULL ,
  search_source VARCHAR( 255 ) NOT NULL ,
  search_published INT( 10 ) UNSIGNED NOT NULL ,
  search_title VARCHAR( 255 ) NOT NULL ,
  search_content TEXT NOT NULL ,
  search_author_name VARCHAR( 255 ) NOT NULL ,
  search_author_image VARCHAR( 255 ) NOT NULL ,
  PRIMARY KEY (search_id)
);

CREATE TABLE IF NOT EXISTS '.$options['resources']['db']['params']['prefix'].'search_index (
  query_id int(10) NOT NULL,
  index_date int(10) NOT NULL,
  index_source varchar(255) NOT NULL,
  index_count int(5) NOT NULL,
  UNIQUE KEY query_id (query_id,index_date,index_source)
);

CREATE TABLE  IF NOT EXISTS '.$options['resources']['db']['params']['prefix'].'search_influencers (
  query_id int(10) unsigned NOT NULL,
  search_author_name varchar(255) NOT NULL,
  search_author_uri varchar(255) NOT NULL,
  cnt int(5) unsigned NOT NULL,
  search_source varchar(255) NOT NULL,
  UNIQUE KEY query_id (query_id,search_author_name)
);';
            $db->getConnection()->exec($schemaSql);

            /**
             * Write .ini file
             */
            /** @var $bootstrap Bootstrap */
            unset($defaultNamespace->options);
            $bootstrap = $this->getInvokeArg('bootstrap');
            $options = new Zend_Config($options);
            $writer = new Zend_Config_Writer_Ini();
            $writer->setRenderWithoutSections()
                ->write($bootstrap->getOption('local_config'), $options);

            $this->_helper->redirector('index', 'index', 'default');
        }
    }
}
