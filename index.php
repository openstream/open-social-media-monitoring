<?php

defined('BASE_PATH') || define('BASE_PATH', realpath(dirname(__FILE__)));
defined('APPLICATION_PATH') || define('APPLICATION_PATH', BASE_PATH );
defined('APPLICATION_ENV') || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/library'),
    APPLICATION_PATH . '/modules/admin/models' ,
    get_include_path(),
)));
set_include_path(APPLICATION_PATH . '/library' . PATH_SEPARATOR . APPLICATION_PATH . '/library/Zend');

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(APPLICATION_ENV, APPLICATION_PATH . '/configs/application.ini');
$application->bootstrap()->run();