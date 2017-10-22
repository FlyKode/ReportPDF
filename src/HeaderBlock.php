<?php

namespace Flykode\ReportPDF;

class HeaderBlock extends BaseBlock {

    public function __construct($report){
        $this->report = $report;
    }
}