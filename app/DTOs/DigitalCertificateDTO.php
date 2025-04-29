<?php

namespace App\DTOs;

class DigitalCertificateDTO
{
    public $loginname;
    public $loginnames;
   
    public function __construct(
        $loginname,
        $loginnames,
        )
    {
        $this->loginname = $loginname;
        $this->loginnames = $loginnames;
    }
}