<?php

namespace App\DTOs;

class DonVViewDTO
{
    public $donVViewName;
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
    public $param;
    public $noCache;
    public $tab;
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public $patientId;
    public $groupBy;
    public $intructionDate;
    public $sessionCodes;
    public $serviceReqId;
    public $serviceReqCode;
    public function __construct(
        $donVViewName,
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
        $param,
        $noCache,
        $tab,
        $intructionTimeFrom,
        $intructionTimeTo,
        $patientId,
        $groupBy,
        $intructionDate,
        $sessionCodes,
        $serviceReqId,
        $serviceReqCode,
        )
    {
        $this->donVViewName = $donVViewName;
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
        $this->param = $param;
        $this->noCache = $noCache;
        $this->tab = $tab;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->patientId = $patientId;
        $this->groupBy = $groupBy;
        $this->intructionDate = $intructionDate;
        $this->sessionCodes = $sessionCodes;
        $this->serviceReqId = $serviceReqId;
        $this->serviceReqCode = $serviceReqCode;
    }
}