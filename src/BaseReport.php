<?php

namespace Flykode\ReportPDF;

use Flykode\Fpdf\Fpdf;

class BaseReport extends Fpdf {

    protected $blocks = array();

    public function __construct($orientation='P',$unit='mm',$format='A4')
    {
        parent::__construct($orientation='P',$unit='mm',$format='A4');

        $this->blocks = array();
    }

    public function __destruct(){}

    public function Header(){}

    public function Footer(){}

    public function preRender(){
        @$this->AliasNbPages();
        @$this->AddPage();
    }

    public function postRender($name,$dest){
        @$this->Output($name,$dest);
    }

    /**
     * Renders each DataBlock
     */
    public function renderReport(){
        foreach($this->blocks as $block){
            @$block->runBlock();
        }
    }
    
    public function runReport($name='',$dest=''){
        try{

            @$this->preRender();

            /**
             * Run rendering methods
             */
            $this->renderReport();

            $this->postRender($name, $dest);

        }catch(ReportPdfException $cpe){
            $this->showError($cpe->getMessage());
        }
    }

    public function registerBlock($block){
        $this->blocks[] = $block;
    }

    protected function showError($msg){
        //Verify if "Output Buffers" already exists
        @ob_end_clean();
        $strJavascript = "<br><input type='button' value='Back' onclick='window.history.go(-1);'/>";
        exit();
    }
}
