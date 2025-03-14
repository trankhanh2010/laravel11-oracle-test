<?php

namespace App\DTOs;

class SereServClsListVViewDTO
{
    public $sereServClsListVViewName;
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
    public $treatmentId;
    public $trackingId;
    public $serviceReqId;
    public $groupBy;
    public $notInTracking;
    public $patientCode;
    public $serviceTypeCodes;
    public $serviceTypeCodesString;
    public $groupByString;
    public $tab;
    public $param;
    public $intructionTimeTo;
    public $intructionTimeFrom;
    public $reportTypeCode;

    public function __construct(
        $sereServClsListVViewName,
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
        $treatmentId,
        $trackingId,
        $serviceReqId,
        $groupBy,
        $notInTracking,
        $patientCode,
        $serviceTypeCodes,
        $serviceTypeCodesString,
        $groupByString,
        $tab,
        $param,
        $intructionTimeTo,
        $intructionTimeFrom,
        $reportTypeCode,
        )
    {
        $this->sereServClsListVViewName = $sereServClsListVViewName;
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
        $this->treatmentId = $treatmentId;
        $this->trackingId = $trackingId;
        $this->serviceReqId = $serviceReqId;
        $this->groupBy = $groupBy;
        $this->notInTracking = $notInTracking;
        $this->patientCode = $patientCode;
        $this->serviceTypeCodes = $serviceTypeCodes;
        $this->serviceTypeCodesString = $serviceTypeCodesString;
        $this->groupByString = $groupByString;
        $this->tab = $tab;
        $this->param = $param;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->reportTypeCode = $reportTypeCode;
    }
}