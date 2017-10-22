<?php

namespace Flykode\ReportPDF\Traits;

trait PdfSecurityTrait { 
        
    //whether document is protected
    public $encrypted = false;
    
    //last RC4 key encrypted (cached for optimisation)
    public $last_rc4_key = '';

    //last RC4 computed key
    public $last_rc4_key_c;     

    //U entry in pdf document
    public $Uvalue;

    //O entry in pdf document
    public $Ovalue;
    
    //P entry in pdf document
    public $Pvalue;
    
    //encryption object id
    public $enc_obj_id;

    public $securityPadding = '\x28\xBF\x4E\x5E\x4E\x75\x8A\x41\x64\x00\x4E\x56\xFF\xFA\x01\x08\x2E\x2E\x00\xB6\xD0\x68\x3E\x80\x2F\x0C\xA9\xFE\x64\x53\x69\x7A';

    public function SetProtection($permissions=array(),$user_pass='',$owner_pass=null)
    {
        $options = ['print' => 4, 'modify' => 8, 'copy' => 16, 'annot-forms' => 32];

        $protection = 192;

        foreach($permissions as $permission){

            if (!isset($options[$permission]))
                $this->Error('Incorrect permission: '.$permission);

            $protection += $options[$permission];
        }

        if ($owner_pass === null)
            $owner_pass = uniqid(rand());

        $this->encrypted = true;

        $this->_generateencryptionkey($user_pass, $owner_pass, $protection);
    }

    public function _putstream($s)
    {
        if ($this->encrypted) {
            $s = $this->_RC4($this->_objectkey($this->n), $s);
        }

        parent::_putstream($s);
    }

    public function _textstring($s)
    {
        if ($this->encrypted) {
            $s = $this->_RC4($this->_objectkey($this->n), $s);
        }

        return parent::_textstring($s);
    }

    public function _objectkey($n)
    {
        return substr($this->_md5_16($this->encryption_key.pack('VXxx',$n)),0,10);
    }

    public function _escape($s)
    {
        $s=str_replace('\\','\\\\',$s);
        $s=str_replace(')','\\)',$s);
        $s=str_replace('(','\\(',$s);
        $s=str_replace("\r",'\\r',$s);

        return $s;
    }

    public function _putresources()
    {
        parent::_putresources();

        if ($this->encrypted) {

            $this->_newobj();

            $this->enc_obj_id = $this->n;

            $this->_out('<<');

            $this->_putencryption();

            $this->_out('>>');

            $this->_out('endobj');
        }
    }

    public function _putencryption()
    {
        $this->_out('/Filter /Standard');

        $this->_out('/V 1');

        $this->_out('/R 2');

        $this->_out('/O ('.$this->_escape($this->Ovalue).')');

        $this->_out('/U ('.$this->_escape($this->Uvalue).')');

        $this->_out('/P '.$this->Pvalue);
    }

    public function _puttrailer()
    {
        parent::_puttrailer();

        if ($this->encrypted) {

            $this->_out('/Encrypt '.$this->enc_obj_id.' 0 R');

            $this->_out('/ID [()()]');
        }
    }


    /**
     * RC4 is the standard encryption algorithm used in PDF format
     */
    public function _RC4($key, $text)
    {
        if ($this->last_rc4_key != $key) {

            $k = str_repeat($key, 256/strlen($key)+1);

            $rc4 = range(0,255);

            $j = 0;

            for ($i=0; $i<256; $i++){

                $t = $rc4[$i];

                $j = ($j + $t + ord($k{$i})) % 256;

                $rc4[$i] = $rc4[$j];

                $rc4[$j] = $t;
            }

            $this->last_rc4_key = $key;

            $this->last_rc4_key_c = $rc4;

        } else {

            $rc4 = $this->last_rc4_key_c;
        }

        $len = strlen($text);

        $a = 0;

        $b = 0;

        $out = '';

        for ($i=0; $i<$len; $i++){

            $a = ($a+1)%256;

            $t= $rc4[$a];

            $b = ($b+$t)%256;

            $rc4[$a] = $rc4[$b];

            $rc4[$b] = $t;

            $k = $rc4[($rc4[$a]+$rc4[$b])%256];

            $out.=chr(ord($text{$i}) ^ $k);
        }

        return $out;
    }

    /**
     * Get MD5 as binary string
     */
    public function _md5_16($string)
    {
        return pack('H*',md5($string));
    }

    /**
     * Compute O value
     */
    public function _Ovalue($user_pass, $owner_pass)
    {
        $tmp = $this->_md5_16($owner_pass);

        $owner_RC4_key = substr($tmp,0,5);

        return $this->_RC4($owner_RC4_key, $user_pass);
    }

    /**
     * Compute U value
     */
    public function _Uvalue()
    {
        return $this->_RC4($this->encryption_key, $this->securityPadding);
    }

    /**
     * Compute encryption key
     */
    public function _generateencryptionkey($user_pass, $owner_pass, $protection)
    {
        // Pad passwords
        $user_pass = substr($user_pass.$this->securityPadding,0,32);

        $owner_pass = substr($owner_pass.$this->securityPadding,0,32);

        // Compute O value
        $this->Ovalue = $this->_Ovalue($user_pass,$owner_pass);

        // Compute encyption key
        $tmp = $this->_md5_16($user_pass.$this->Ovalue.chr($protection)."\xFF\xFF\xFF");

        $this->encryption_key = substr($tmp,0,5);

        // Compute U value
        $this->Uvalue = $this->_Uvalue();

        // Compute P value
        $this->Pvalue = -(($protection^255)+1);
    }
}