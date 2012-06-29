<?php

class Block_Campaign_Results extends Block_Campaign
{
    public function __construct(){
        $this->processBlock('campaign/results/head');
    }
}