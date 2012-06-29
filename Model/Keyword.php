<?php

class Model_Keyword extends Model_Abstract
{
    public function __construct(){
        $this->_mainTable = 'query';
        $this->_primaryKey = 'query_id';
    }
}