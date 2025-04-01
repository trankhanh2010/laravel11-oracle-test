<?php

namespace App\DTOs;

class MestPatientTypeDTO
{
    public $mestPatientTypeName;
    public $keyword;
    public $isActive;
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
    public $mediStockId;
    public $patientTypeId;
    public $param;
    public $noCache;
    public function __construct(
        $mestPatientTypeName,
        $keyword, 
        $isActive, 
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
        $mediStockId,
        $patientTypeId,
        $param,
        $noCache,
        )
    {
        $this->mestPatientTypeName = $mestPatientTypeName;
        $this->keyword = $keyword;
        $this->isActive = $isActive;
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
        $this->mediStockId = $mediStockId;
        $this->patientTypeId = $patientTypeId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}