<?php

namespace App\DTOs;

class ServiceReqPaymentDTO
{
    public $patientCode;
    public $treatmentCode;
    public function __construct(
        $patientCode,
        $treatmentCode, 
        )
    {
        $this->patientCode = $patientCode;
        $this->treatmentCode = $treatmentCode;
    }
}