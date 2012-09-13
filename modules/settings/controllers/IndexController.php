<?php
class Settings_IndexController extends Zend_Controller_Action
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
        $defaultNamespace = new Zend_Session_Namespace('Default');

        /** @var $bootstrap Bootstrap */
        $bootstrap = $this->getInvokeArg('bootstrap');
        $this->view->options = isset($defaultNamespace->options) ? $defaultNamespace->options : $bootstrap->getOptions();
        $this->view->error = $defaultNamespace->error;
        unset($defaultNamespace->error);
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
            )),
            'template' => $this->getRequest()->getParam('template')
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
             * Write .ini file
             */
            unset($defaultNamespace->options);
            /** @var $bootstrap Bootstrap */
            $bootstrap = $this->getInvokeArg('bootstrap');
            $options = new Zend_Config($options);
            $writer = new Zend_Config_Writer_Xml();
            $writer->write($bootstrap->getOption('local_config'), $options);

            $options = $bootstrap->getOptions();
            $options = new Zend_Config($options);
            $writer->write('application.xml', $options);

            $this->_helper->redirector('index', 'index', 'default');
        }
    }
}
