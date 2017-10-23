<?php

namespace Flykode\ReportPDF;

class BaseBlock {

    protected $report;

    protected $blockData = array();

    public function __construct($report){
        $this->report = $report;
    }

    public function setBlockData(array $blockData)
    {
        $this->blockData = $blockData;
    }

    public function getBlockData()
    {
        return $this->blockData;
    }

    public function __destruct(){}

    public function runBlock(){
        //echo 'running Mode';
    }
}