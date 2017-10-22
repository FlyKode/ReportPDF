<?php

namespace Flykode\ReportPDF;

class FooterBlock extends BaseBlock {

    public function __construct($report){
        $this->report = $report;
    }
}