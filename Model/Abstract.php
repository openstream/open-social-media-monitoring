<?php

class Model_Abstract
{
    public $_mainTable;
    public $_primaryKey;
    private $_data = array();

    /**
     * Return model by ID or false
     *
     * @param $id int
     */
    public function load($id)
    {
        global $prefix;

        $query = 'SELECT * FROM '.$prefix.$this->_mainTable.' WHERE '.$this->_primaryKey.' = '.$id;
        if (($res = mysql_query($query)) && mysql_num_rows($res)) {
            $this->_data = mysql_fetch_array($res);
        }
    }

    /**
     * Return data array value
     *
     * @param string $key
     * @return mixed
     */
    public function getData($key)
    {
        return $this->_data[$key];
    }

    /**
     * Shortcut to get a primary key
     *
     * @return mixed
     */
    public function getId(){
        return $this->_data[$this->_primaryKey];
    }
}