<?php

namespace App\DTOs;

class OtpDTO
{
    public $patientCode;
    public $method;
    public function __construct(
        $patientCode,
        $method = '',
        )
    {
        $this->patientCode = $patientCode;
        $this->method = $method;
    }
}