<?php

namespace App\DTOs;

class PdfDTO
{
    public $treatmentCode;
    public $documentIds;
    public $orderBy;
    public $orderByJoin;
    public $param;
    public function __construct(
        $treatmentCode,
        $documentIds,
        $orderBy,
        $orderByJoin,
        $param,
        )
    {
        $this->treatmentCode = $treatmentCode;
        $this->documentIds = $documentIds;
        $this->orderBy = $orderBy;
        $this->orderByJoin = $orderByJoin;
        $this->param = $param;
    }
}