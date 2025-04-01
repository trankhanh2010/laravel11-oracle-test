<?php

namespace App\DTOs;

class TreatmentListVViewDTO
{
    public $treatmentListVViewName;
    public $keyword;
    public $isActive;
    public $isDelete;
    public $orderBy;
    public $orderByJoin;
    public $orderByString;
    public $getAll;
    public $start;
    public $limit;
    public $request;
    public $appCreator;
    public $appModifier;
    public $time;
     public $groupBy;
    public $patientCode;
    public $param;
    public $treatmentTypeCode;
    public $inTimeFrom;
    public $inTimeTo;
    public $noCache;

    public function __construct(
        $treatmentListVViewName,
        $keyword, 
        $isActive, 
        $isDelete,
        $orderBy, 
        $orderByJoin, 
        $orderByString, 
        $getAll, 
        $start, 
        $limit,
        $request,
        $appCreator,
        $appModifier,
        $time,
        $groupBy,
        $patientCode,
        $param,
        $treatmentTypeCode,
        $inTimeFrom,
        $inTimeTo,
        $noCache,
        )
    {
        $this->treatmentListVViewName = $treatmentListVViewName;
        $this->keyword = $keyword;
        $this->isActive = $isActive;
        $this->isDelete = $isDelete;
        $this->orderBy = $orderBy;
        $this->orderByJoin = $orderByJoin;
        $this->orderByString = $orderByString;
        $this->getAll = $getAll;
        $this->start = $start;
        $this->limit = $limit;
        $this->request = $request;
        $this->appCreator = $appCreator;
        $this->appModifier = $appModifier;
        $this->time = $time;
        $this->groupBy = $groupBy;
        $this->patientCode = $patientCode;
        $this->param = $param;
        $this->treatmentTypeCode = $treatmentTypeCode;
        $this->inTimeFrom = $inTimeFrom;
        $this->inTimeTo = $inTimeTo;
        $this->noCache = $noCache;
    }
}