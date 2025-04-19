<?php

namespace App\DTOs;

class DigitalCertificateDTO
{
    public $loginname;
   
    public function __construct(
        $loginname,
        )
    {
        $this->loginname = $loginname;
    }
}