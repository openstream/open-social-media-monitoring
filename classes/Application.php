<?php

 /*
 Open Social Media Monitoring
 http://www.open-social-media-monitoring.net

 Copyright (c) 2011 Openstream Internet Solutions

 This program is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License v3 (2007)
 as published by the Free Software Foundation.
 */

class Application{

    public $settings_var_names = array('admPassword', 'adminEmail', 'defaultFrom', 'dbHost', 'dbUser', 'dbPassword', 'dbName', 'prefix', 'keep_history', 'alchemy_api_key');

    /**
     * Registry collection
     *
     * @var array
     */
    static private $_registry = array();

    /*
    *  Routing
    *
    *  @param array
    *  @return void
    */
    function __construct($args = array()){
        // Searching for a method name and calling either it or default method
        if(is_array($args) && count($args) && method_exists($this, strtolower($args[0]).'Action')){
            call_user_func_array(array($this, strtolower($args[0]).'Action'), array_slice($args, 1));
        }else{
            $this->defaultAction();
        }
    }

    public function defaultAction()
    {
        /* do nothing */
    }

    public static function run(){
        global $directory, $dbName, $dbHost, $dbUser, $dbPassword, $adminEmail, $admPassword;

        session_start();

        // If script is running in directory (not in a web-server root), then getting the directory name and stripping it from REQUEST_URI
        $directory = preg_replace('/index.php$/ism', '', $_SERVER['SCRIPT_NAME']);
        $uri = preg_replace('|^'.$directory.'|ism', '', $_SERVER['REQUEST_URI']);

        // Parsing request string into array
        $uri = explode('/', trim($uri, '/'));

        // Popping the first element of the path. It is representing the class that is handling output.
        $class = $uri[0] ? ucfirst($uri[0]) : 'Projects';
        $args = array_slice($uri, 1);

        // If not able to connect to database run the installer
        if($rd = @mysql_connect($dbHost, $dbUser, $dbPassword)){
            mysql_select_db($dbName, $rd);
        }else{
            $class = 'Installer';
        }

        // If user is not logged then setting class name to Login
        if((!isset($_SESSION['a_email']) || $_SESSION['a_email'] != $adminEmail || !isset($_SESSION['a_password']) || $_SESSION['a_password'] != $admPassword) && $class != 'Installer'){
            $class = 'Login';
        }

        // At last calling class and sending request arguments
        new $class($args);
    }

    public function getUrl($route = ''){
        global $directory;
        $backslash = $route && !preg_match('/\.js$/ism', $route) ? '/' : '';

        return (preg_match('/https/i', $_SERVER['SERVER_PROTOCOL']) ? 'https://' : 'http://').$_SERVER['SERVER_NAME'].$directory.trim($route, '/').$backslash;
    }

    public function log($string){
        if(file_exists('logs/system.log') && is_writable('logs/system.log')){
            file_put_contents('logs/system.log', $string);
        }
    }

    public function saveSettings(){
        $fp = fopen('settings.php', 'w+');
        fputs($fp, "<?php\n\n");
        while(list($var, $val) = each($_POST)){
            if(in_array($var, $this->settings_var_names)){
                fputs($fp, ' $'.$var." = '".$val."';\n");
            }
        }
        fputs($fp, "\n\n?>");
        fclose($fp);
    }

    public static function getSingleton($modelClass='', array $arguments=array())
    {
        $registryKey = '_singleton/'.$modelClass;
        if (!self::registry($registryKey)) {
            self::register($registryKey, self::getModel($modelClass, $arguments));
        }
        return self::registry($registryKey);
    }

    public static function getModel($modelName)
    {
        $modelName = 'Model_'.self::getClassName($modelName);
        return new $modelName();
    }

    public static function getBlock($blockName)
    {
        $blockName = 'Block_'.self::getClassName($blockName);
        return new $blockName();
    }

    private function getClassName($className)
    {
        $className = explode('/', $className);
        for($i=0; $i<count($className); $i++) {
            $className[$i] = ucfirst($className[$i]);
        }
        return implode('_', $className);
    }

    /**
     * Register a new variable
     *
     * @param string $key
     * @param mixed $value
     */
    public static function register($key, $value)
    {
        if (!isset(self::$_registry[$key])) {
            self::$_registry[$key] = $value;
        }
    }

    /**
     * Unregister a variable from register by key
     *
     * @param string $key
     */
    public static function unregister($key)
    {
        if (isset(self::$_registry[$key])) {
            if (is_object(self::$_registry[$key]) && (method_exists(self::$_registry[$key], '__destruct'))) {
                self::$_registry[$key]->__destruct();
            }
            unset(self::$_registry[$key]);
        }
    }

    /**
     * Retrieve a value from registry by a key
     *
     * @param string $key
     * @return mixed
     */
    public static function registry($key)
    {
        if (isset(self::$_registry[$key])) {
            return self::$_registry[$key];
        }
        return null;
    }
}