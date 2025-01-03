<?php

namespace App\DTOs;

class TreatmentFeePaymentDTO
{
    public $paymentMethod;
    public $paymentOption;
    public $patientCode;
    public $treatmentCode;
    public function __construct(
        $paymentMethod,
        $paymentOption,
        $patientCode,
        $treatmentCode, 
        )
    {
        $this->paymentMethod = $paymentMethod;
        $this->paymentOption = $paymentOption;
        $this->patientCode = $patientCode;
        $this->treatmentCode = $treatmentCode;
    }
}