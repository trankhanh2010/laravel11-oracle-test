<?php

namespace App\DTOs;

class TreatmentFeePaymentDTO
{
    public $appCreator;
    public $appModifier;
    public $paymentMethod;
    public $paymentOption;
    public $patientCode;
    public $treatmentCode;
    public $transactionTypeCode;
    public $depositReqCode;
    public $param;
    public $noCache;
    public $currentLoginname;
    public function __construct(
        $appCreator,
        $appModifier,
        $paymentMethod,
        $paymentOption,
        $patientCode,
        $treatmentCode,
        $transactionTypeCode, 
        $depositReqCode,
        $param,
        $noCache,
        $currentLoginname,
        )
    {
        $this->appCreator = $appCreator;
        $this->appModifier = $appModifier;
        $this->paymentMethod = $paymentMethod;
        $this->paymentOption = $paymentOption;
        $this->patientCode = $patientCode;
        $this->treatmentCode = $treatmentCode;
        $this->transactionTypeCode = $transactionTypeCode;
        $this->depositReqCode = $depositReqCode;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->currentLoginname = $currentLoginname;
    }
}