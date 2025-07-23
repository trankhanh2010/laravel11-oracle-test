<?php

namespace App\DTOs;

class OtpDTO
{
    public $patientCode;
    public function __construct(
        $patientCode,
        )
    {
        $this->patientCode = $patientCode;
    }
}