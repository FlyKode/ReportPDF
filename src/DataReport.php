<?php

namespace Flykode\ReportPDF;

class DataReport extends BaseReport {

    public $imgLogo;

    public $imgLogoWidth = 20;
    public $imgLogoFullPath = '';

    public $Title;

    public $SubTitle;

    public $HeaderHeight;

    public $HeaderBorder;

    public $hasDate;
    public $hasDateText = 'Emissão: ';
    public $hasDateFormat = 'd/m/Y';

    public $hasPageNum;
    public $hasPageNumLastPag = 0;
    public $hasPageNumText = 'Página : ';

    public $TopMargin;

    public $HasDefaultHeader = true;

    public $DisplayPreferences = '';
      
      
    public function DisplayPreferences($preferences) {

        $this->DisplayPreferences.= $preferences;
    }

    public function _putcatalog()
    {
        parent::_putcatalog();

        if(is_int(strpos($this->DisplayPreferences,'FullScreen'))){
            $this->_out('/PageMode /FullScreen');
        }

        if($this->DisplayPreferences) {

            $this->_out('/ViewerPreferences<<');

            if(is_int(strpos($this->DisplayPreferences,'HideMenubar')))
                $this->_out('/HideMenubar true');

            if(is_int(strpos($this->DisplayPreferences,'HideToolbar')))
                $this->_out('/HideToolbar true');

            if(is_int(strpos($this->DisplayPreferences,'HideWindowUI')))
                $this->_out('/HideWindowUI true');

            if(is_int(strpos($this->DisplayPreferences,'DisplayDocTitle')))
                $this->_out('/DisplayDocTitle true');

            if(is_int(strpos($this->DisplayPreferences,'CenterWindow')))
                $this->_out('/CenterWindow true');

            if(is_int(strpos($this->DisplayPreferences,'FitWindow')))
                $this->_out('/FitWindow true');

            $this->_out('>>');
        }
    }


    function CheckPageBreak($h)
    {
        //If the height h would cause an overflow, add a new page immediately
        if($this->GetY()+$h>$this->PageBreakTrigger)
            $this->AddPage($this->CurOrientation);
    }

    public function Header(){

        $offset = $this->TopMargin;

        $this->SetY(5+$offset);

        if ($this->HasDefaultHeader == true){

            //Logo
            if (trim($this->imgLogoFullPath) == ''){
                throw new ReportPdfException('The $imgLogoFullPath variable must be a full path to the image');
            }

            $this->Image(public_path('images/'.$this->imgLogo),11,5.5+$offset,$this->imgLogoWidth);

            $this->SetFont('Arial','B',11);

            //Move to the right
            $offsetx = $this->GetX()+$this->imgLogoWidth+2;

            $this->Cell(0,$this->HeaderHeight,'',$this->HeaderBorder,0,'C');

            $this->SetY(8+$offset);

            $suboffset = 0;

            if ($this->Title!="") {

                if (strpos($this->Title,"\n")==false){

                    $this->Text($offsetx+1,10+$offset,$this->Title);
                    $suboffset += 3.5;

                } else {

                    $suboffset = 0;
                    $arrText = explode("\n",$this->Title);

                    foreach($arrText as $itext){
                        $this->Text($offsetx+1,10+$suboffset+$offset,$itext);
                        $suboffset += 3.5;
                    }
                }
            }

            $this->SetFont('Arial','B',10);

            if ($this->SubTitle!="") {

                if (strpos($this->SubTitle,"\n")==false){

                    $this->Text($offsetx+1,10+$offset+$suboffset,$this->SubTitle);

                } else {

                    $suboffset2 = 0;
                    $arrText = explode("\n",$this->SubTitle);

                    foreach($arrText as $itext){
                        $this->Text($offsetx+1,10+$offset+$suboffset+$suboffset2,$itext);
                        //$this->Text($offsetx+1,10+$suboffset+$offset,$itext);
                        $suboffset2 += 3.5;
                    }
                }
            }

            $this->SetFont('Arial','',8);

            if ($this->hasPageNum)
            {
                $_hasPageNumLastPag = '{nb}';

                if($this->hasPageNumLastPag >= 0)
                    $_hasPageNumLastPag = $this->hasPageNumLastPag;

                $pageNumCellText = $this->hasPageNumText.$this->PageNo().'/'.$_hasPageNumLastPag;

                if($this->hasPageNumLastPag >= 0 && $this->hasPageNumLastPag < $this->PageNo())
                    $pageNumCellText = '';

                $this->Cell(0,0, $pageNumCellText,0,0,'R');
            }

            if ($this->hasDate)
                $this->Cell(0,10,$this->hasDateText.date($this->hasDateFormat),0,0,'R');

            //Line break
            $this->Ln($this->HeaderHeight-3);
        }

        foreach($this->blocks as $block){

            if (is_a($block,'HeaderBlock') == true){
                $block->runBlock();
            }
        }

        $this->Ln();
        $this->SetFont('Arial','',8);
    }

    public function renderReport(){

        foreach($this->blocks as $block){
            if ((is_a($block,'HeaderBlock') != true) and (is_a($block,'FooterBlock') != true)){
                $block->runBlock();
            }
        }
    }

    public function Footer()
    {
        foreach($this->blocks as $block){
            if (is_a($block,'FooterBlock')==true){
                $block->runBlock();
            }
        }
    }
}//endof DataReport Class
