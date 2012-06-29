<?php

class Block_Campaign_Results_Head extends Block_Campaign_Results
{
    public function output()
    {
        return '<script type="text/javascript" src="'.$this->getUrl('js/jquery-1.4.2.min.js').'"></script>';
    }
}