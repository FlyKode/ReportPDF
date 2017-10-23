<?php
require_once __DIR__ . '/../vendor/autoload.php';

//flykode_reportpdf_logo.png
use Flykode\ReportPDF\DataReport;
use Flykode\ReportPDF\DataBlock;
use Flykode\ReportPDF\HeaderBlock;

$currentPath = __DIR__.DIRECTORY_SEPARATOR;

class ListaUsuariosHeaderBlock extends HeaderBlock {
    
    public function runBlock(){

        $nom_grupo = 'Tecnologia';
        $this->report->Cell(130,5,utf8_decode('Usuários da Lista: '.$nom_grupo),1,0);

        $this->report->Cell(60,5,utf8_decode('Relatório Sem Filtro'),1,0);

        $this->report->Ln();
    }
}


$myData = [
    ['birthday' => '01/09/1987','age' => 30, 'name' => 'Allan Freitas','skill' => 'PHP'],
    ['birthday' => '02/09/1988','age' => 29, 'name' => 'Somé Name','skill' => 'Node.JS'],
    ['birthday' => '03/09/1989','age' => 28, 'name' => 'Another Name','skill' => 'Go Lang'],
    ['birthday' => '10/09/1990','age' => 27, 'name' => 'Yét Another Name','skill' => 'Lua'],
];


$r = new DataReport("P");
$r->TopMargin = 5;
$r->imgLogo = "flykode_reportpdf_logo.jpg";
$r->imgLogoPath = $currentPath;
$r->imgLogoWidth = 40;
$r->Title = utf8_decode("Relatório de Usuários");
//$r->SubTitle = "";
$r->HeaderHeight = 12;
$r->hasPageNum = true;
$r->hasDate = true;
$dbb = new DataBlock($r);

$hb = new ListaUsuariosHeaderBlock($r);

$dbb->LineHeight = 5;

$dbb->TitleBorder = 1;
$dbb->CellBorder = 1;
$dbb->ShowTitle=true;
$dbb->TotalTitle='Total de Aniversariantes do Grupo';
$dbb->addColumn('@rownum',' ',6);
$dbb->addColumn('birthday','Nascimento',18);
$dbb->addColumn('age','Idade',9);
$dbb->addColumnUTF8('name','Nome', 107);
$dbb->addColumn('skill','Habilidade', 50);

$dbb->setBlockData($myData);


$r->registerBlock($hb);
$r->registerBlock($dbb);


$namePdf = 'mybirthdaylist.pdf';

$full_path_name = $currentPath.$namePdf;

echo $full_path_name;

$r->runReport($full_path_name,'F');

//Default name doc.pdf and will return the string of pdf to the browser
//$r->runReport();