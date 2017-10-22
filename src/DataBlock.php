<?php

namespace Flykode\ReportPDF;

use Flykode\ReportPDF\Helpers;

class DataBlock extends BaseBlock {

    public $Columns;
    
    public $CellBorder;

    public $BreakLine=false;

    public $OddLines=false;

    public $TitleBorder;

    public $DetailBlocks;

    public $ParentRow;

    public $Agregateds;

    public $Ident;

    public $LineHeight=0;

    public $TotalTitle;

    public $ShowTitle=true;

    public $FillColor = array(1 => 255,255,255);

    protected $arrFormats;


    public function __construct($report){
        
        parent::__construct($report);

        $this->Columns = array();
        $this->DetailBlocks = array();
        $this->ParentRow = array();
        $this->Agregateds = array();
        $this->Formats[Helpers::CENTERTEXT_COLUMN] = 'C';
        $this->Formats[Helpers::RIGHTTEXT_COLUMN] = 'R';
        $this->Formats[Helpers::TEXT_COLUMN] = 'L';
        $this->Formats[Helpers::FLOAT_COLUMN] = 'R';
        $this->Formats[Helpers::NUMBER_COLUMN] = 'R';
        $this->Formats[Helpers::INTEGER_COLUMN] = 'R';
        $this->Formats[Helpers::CURRENCY_COLUMN] = 'R';
        $this->Formats[Helpers::DATE_COLUMN] = 'C';
        $this->Formats[Helpers::R_TEXT_COLUMN] = 'R';
        $this->Formats[Helpers::ACCUMULATE_FUNCTION] = 'R';
    }


    /**
     * funcao adicionada em 02/10/2015 para o sistema novo
     *
     * @param $field_name
     * @param string $column_title
     * @param int $column_size
     */
    public function addColumnUTF8($field_name,$column_title='',$column_size=10){
        $this->Columns[$field_name] = [
            'title' => utf8_decode($column_title),
            'size' => $column_size,
            'type' => Helpers::TEXT_COLUMN,
            'agregator' => '',
            'repeat' => true,
            'callfunction' => 'utf8_decode',
            'accmulateValue' => 0,
        ];
    }

    public function addColumn($field_name,$column_title='',$column_size=10,$column_type=Helpers::TEXT_COLUMN,
        $column_agregator='',$repeat=true,$callfuction=null)
    {
        $this->Columns[$field_name] = array(
            'title' => utf8_decode($column_title),
            'size' => $column_size,
            'type' => $column_type,
            'agregator' => $column_agregator,
            'repeat' => $repeat,
            'callfunction' => $callfuction,
            'accmulateValue' => 0,
        );
    }

    public function addAccumulateColumn($field_name,$column_title='',$column_size=10,$accmulateValue=0,$column_agregator=''){
        $this->Columns[$field_name] = array(
            'title' => utf8_decode($column_title),
            'size' => $column_size,
            'type' => Helpers::ACCUMULATE_FUNCTION,
            'agregator' => $column_agregator,
            'repeat' => true,
            'callfunction' => null,
            'accmulateValue' => $accmulateValue,
        );
    }

    public function printTitles(){

        $arrFields = array_keys($this->Columns);

        if ($this->Ident!=null)
            $this->report->Cell($this->Ident,5,'',0,0);

        $this->report->SetFont(null,'B');

        foreach ($arrFields as $field){
            $this->report->Cell($this->Columns[$field]['size'],5,
                $this->Columns[$field]['title'],
                $this->TitleBorder,0,
                $this->Formats[$this->Columns[$field]['type']]
            );
        }

        $this->report->SetFont(null,null);
        $this->report->Ln();
    }

    public function printAgregateds(){
        
        if (sizeof($this->Agregateds)>0){

            if ($this->Ident!=null)
                $this->report->Cell($this->Ident,5,'',0,0);

            $offset = 0;

            $arrFields = array_keys($this->Columns);

            foreach ($arrFields as $field){
                @$offset += (($this->Agregateds[$field]!=null) ? intval($this->Columns[$field]['size']) : 0);
            }

            $this->report->SetFont(null,'B');

            $pageWidth = ($this->report->CurOrientation=='P'?190:277);

            $this->report->Cell(($pageWidth-$offset-$this->Ident),5,$this->TotalTitle,$this->TitleBorder,0,'R');
            //$this->report->Cell((intval($this->report->w)-20)-$offset-$this->Ident,5,$this->TotalTitle,$this->TitleBorder,0,'R');

            $this->report->SetFont(null,null);
            //$this->report->Ln();
            $arrFields = array_keys($this->Columns);

            $this->report->SetFont(null,'B');

            foreach ($arrFields as $field){
                if (isset($this->Agregateds[$field])) {
                    $this->report->Cell($this->Columns[$field]['size'],5,
                        (($this->Agregateds[$field]!=null) ? Helpers::formatByType($this->Columns[$field]['type'],$this->Agregateds[$field]) : ''),
                        (($this->Agregateds[$field]!=null) ? $this->TitleBorder : 0),0,
                        $this->Formats[$this->Columns[$field]['type']]
                    );
                }
            }

            $this->report->SetFont(null,null);
            $this->report->Ln();
        }
    }

    public function setParentRow($row){
        $this->ParentRow = $row;
    }


    /**
     *
     */
    public function runBlock(){

        if ($this->ShowTitle==true)
            $this->printTitles();

        $this->Agregateds = array();
        $blockres = $this->getBlockData();

        $arrFields = array_keys($this->Columns);
        $tmp_counter = 0;
        $tmp_y = 0;
        $tmp_y2 = 0;
        $arrMemory = array();

        foreach($blockres as $arrRow)
        {
            /**
             * Efetuar melhorias aqui pois esse CASTING the stdClass to Array consome mais processamento e memoria
             * deixando o script mais lento
             */
            $arrRow = (array) $arrRow;

            if  ((($this->report->y>=270) and ($this->report->CurOrientation=="P"))
                    or (($this->report->y>=187) and ($this->report->CurOrientation=="L"))){
                if ($this->ShowTitle==true)
                    $this->printTitles();
            }

            if ($this->OddLines==true){
                if (($tmp_counter % 2)==0){
                    $this->report->SetFillColor(180,180,180);
                } else {
                    $this->report->SetFillColor(255,255,255);
                }
            } else {
                //$this->report->FillColor='0 g';
                $this->report->SetFillColor(intval($this->FillColor[1]),intval($this->FillColor[2]),intval($this->FillColor[3]));
                $this->report->ColorFlag=true;
            }

            if ($this->Ident!=null)
                $this->report->Cell($this->Ident,5,'',0,0);

            $tmp_counter++;

            foreach ($arrFields as $field){

                if (($this->Columns[$field]['repeat']==false)
                    and (isset($arrMemory[$field])) and ($arrRow[$field] == $arrMemory[$field])){
                    $value = '';
                } else {
                    if ($this->Columns[$field]['callfunction']!=null){

                        if (function_exists($this->Columns[$field]['callfunction'])==true){
                            $strFuncName = $this->Columns[$field]['callfunction'];
                            @$value = Helpers::formatByType($this->Columns[$field]['type'],$strFuncName($arrRow[$field]));
                        } else {
                            //@$value = Helpers::formatByType($this->Columns[$field]['type'],utf8_decode($arrRow[$field]));
                            @$value = Helpers::formatByType($this->Columns[$field]['type'],$arrRow[$field]);
                        }
                    } else {
                        //@$value = Helpers::formatByType($this->Columns[$field]['type'],utf8_decode($arrRow[$field]));
                        @$value = Helpers::formatByType($this->Columns[$field]['type'],$arrRow[$field]);
                    }
                }

                //@$value = Helpers::formatByType($this->Columns[$field]['type'],$arrRow[$field]);
                if ($field=="@rownum"){
                    $this->report->Cell($this->Columns[$field]['size'],5,$tmp_counter,
                        $this->CellBorder,0,$this->Formats[$this->Columns[$field]['type']],1
                    );
                } else {
                    if ($this->Columns[$field]['type'] == Helpers::ACCUMULATE_FUNCTION)
                    {
                        $this->Columns[$field]['accmulateValue'] = $this->Columns[$field]['accmulateValue'] + ($arrRow[$field]);

                        $this->report->Cell($this->Columns[$field]['size'],5,
                            Helpers::formatByType(Helpers::CURRENCY_COLUMN,
                            $this->Columns[$field]['accmulateValue']),
                            $this->CellBorder,0,$this->Formats[$this->Columns[$field]['type']],1
                        );
                    }else{
                        $this->report->Cell($this->Columns[$field]['size'],5,$value,
                            $this->CellBorder,0,$this->Formats[$this->Columns[$field]['type']],1
                        );
                    }

                }

                if ($this->Columns[$field]['agregator'] == Helpers::SUM_FUNCTION){

                    //@$this->Agregateds[$field]+= $arrRow[$field];
                    @$this->Agregateds[$field]+= $arrRow[$field];

                    if ($this->Agregateds[$field]==0){
                        $this->Agregateds[$field] = "0";
                    }
                }

                if ($this->Columns[$field]['agregator'] == Helpers::COUNT_FUNCTION){
                    @$this->Agregateds[$field]++;
                }

                if ($this->Columns[$field]['agregator'] == Helpers::AVG_FUNCTION){
                    $this->Agregateds[$field] = ($this->Agregateds[$field] + floatval($arrRow[$field]))/2;
                }

                if ($this->Columns[$field]['agregator'] == Helpers::MAX_FUNCTION){
                    $this->Agregateds[$field] = ($this->Agregateds[$field]>$arrRow[$field]?$this->Agregateds[$field]:$arrRow[$field]);
                }

                if ($this->Columns[$field]['agregator'] == Helpers::MIN_FUNCTION){

                    $this->Agregateds[$field] = (($this->Agregateds[$field] == null) ? $arrRow[$field] : $this->Agregateds[$field]);
                    $this->Agregateds[$field] = (($arrRow[$field]>$this->Agregateds[$field]) ? $this->Agregateds[$field] : $arrRow[$field]);
                }

                if($this->Columns[$field]['agregator'] == Helpers::VALUE_FUNCTION){

                    if(($arrRow[$field]!=null) || ($arrRow[$field]!='')){
                        $this->Agregateds[$field] += 1;
                    }

                    if($this->Agregateds[$field] == null){
                        $this->Agregateds[$field] = '0';
                    }

                    if($this->Columns[$field]['isempty']==true){
                        $this->Agregateds[$field] = ' ';
                    }
                }
            }

            //Verify this old if that was breaking some reports
            //if ($this->Columns[$field]['multiline']==false){
            $this->report->Ln($this->LineHeight);
            //}

            foreach($this->DetailBlocks as $dblock){

                if (get_class($dblock)=="Flykode\ReportPDF\DataBlock"){
                    $dblock->setParentRow($arrRow);
                    //$this->report->SetX($this->report->GetX()+10);
                    //$dblock->printTitles();
                    $dblock->runBlock();
                }
            }
            $arrMemory = $arrRow;
        }
        
        $this->printAgregateds();

        if ($this->BreakLine==true){
            $this->report->Cell(0,0,'',1,0,'C');
            $this->report->Ln(0.5);
        }
    }

    public function registerDetailBlock($dblock){
        $this->DetailBlocks[] = $dblock;
    }
}