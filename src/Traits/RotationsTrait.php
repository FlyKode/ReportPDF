<?php

namespace Flykode\ReportPDF\Traits;

/**
 * Rotations Addon
 * 
 * Extracted from: http://www.fpdf.org/en/script/script2.php
 * Original Author: Olivier
 * 
 * Adapted by: @allanfreitas
 */
trait RotationsTrait {

    public $angle = 0;
    
    public function Rotate($angle,$x=-1,$y=-1)
    {
        if($x==-1)
            $x=$this->x;

        if($y==-1)
            $y=$this->y;

        if($this->angle!=0)
            $this->_out('Q');

        $this->angle=$angle;

        if($angle!=0)
        {
            $angle*=M_PI/180;

            $c=cos($angle);

            $s=sin($angle);

            $cx=$x*$this->k;

            $cy=($this->h-$y)*$this->k;

            $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm',$c,$s,-$s,$c,$cx,$cy,-$cx,-$cy));
        }
    }

    public function RotatedText($x,$y,$txt,$angle)
    {
        //Text rotated around its origin
        $this->Rotate($angle,$x,$y);

        $this->Text($x,$y,$txt);

        $this->Rotate(0);
    }

    public function RotatedImage($file,$x,$y,$w,$h,$angle)
    {
        //Image rotated around its upper-left corner
        $this->Rotate($angle,$x,$y);

        $this->Image($file,$x,$y,$w,$h);

        $this->Rotate(0);
    }
}
