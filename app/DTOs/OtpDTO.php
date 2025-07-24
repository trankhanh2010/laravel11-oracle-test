<?php

namespace App\DTOs;

class OtpDTO
{
    public $patientCode;
    public $method;
    public $registerPhone;
    public function __construct(
        $patientCode,
        $method = '',
        $registerPhone = '',
        )
    {
        $this->patientCode = $patientCode;
        $this->method = $method;
        $this->registerPhone = $registerPhone;
    }
}