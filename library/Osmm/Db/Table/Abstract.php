<?php

class Osmm_Db_Table_Abstract extends Zend_Db_Table_Abstract
{
    private $_tables, $_prefix;
    protected $_name;

    public function __construct($config = array()){
        $this->_name = $this->getTableName($this->_name);
        parent::__construct($config);
    }

    public function getTableName($key)
    {
        if (!$this->_tables || !$this->_prefix) {
            $front = Zend_Controller_Front::getInstance();
            /** @var $bootstrap Bootstrap */
            $bootstrap = $front->getParam("bootstrap");

            $db_options = $bootstrap->getOption('resources');
            $this->_prefix = $db_options['db']['params']['prefix'];

            $this->_tables = $bootstrap->getOption('table');
        }

        return $this->_prefix . $this->_tables[$key];
    }

    public function insertOnDuplicate($data, $on_duplicate)
    {
        $sql = 'INSERT INTO ' . $this->_name . ' (`' . implode('`, `', array_keys($data)) . '`)
                     VALUES (' . substr(str_repeat('?, ', count($data)), 0, -2) . ')
           ON DUPLICATE KEY '.$on_duplicate;
        $this->_db->query($sql, array_values($data));
    }

    public function optimize()
    {
        $sql = 'OPTIMIZE TABLE ' . $this->_name . 'search';
        $this->_db->query($sql);
    }
}