<?php

namespace App\DTOs;

class ServicePatyDTO
{
    public $servicePatyName;
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
    public $serviceTypeIds;
    public $patientTypeIds;
    public $serviceId;
    public $packageId;
    public $effective;
    public $param;
    public function __construct(
        $servicePatyName,
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
        $serviceTypeIds,
        $patientTypeIds,
        $serviceId,
        $packageId,
        $effective,
        $param,
        )
    {
        $this->servicePatyName = $servicePatyName;
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
        $this->serviceTypeIds = $serviceTypeIds;
        $this->patientTypeIds = $patientTypeIds;
        $this->serviceId = $serviceId;
        $this->packageId = $packageId;
        $this->effective= $effective;
        $this->param = $param;
    }
}