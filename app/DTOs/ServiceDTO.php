<?php

namespace App\DTOs;

class ServiceDTO
{
    public $serviceName;
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
    public $serviceTypeId;
    public $param;
    public $noCache;
    public $groupBy;
    public $tab;
    public $serviceGroupIds;
    public $serviceReqId;
    public $serviceReqIds;
    public $serviceTypeCode;
    public $executeRoomId;

    public function __construct(
        $serviceName,
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
        $serviceTypeId,
        $param,
        $noCache,
        $groupBy,
        $tab,
        $serviceGroupIds,
        $serviceReqId,
        $serviceReqIds,
        $serviceTypeCode,
        $executeRoomId,
        )
    {
        $this->serviceName = $serviceName;
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
        $this->serviceTypeId = $serviceTypeId;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->groupBy = $groupBy;
        $this->tab = $tab;
        $this->serviceGroupIds = $serviceGroupIds;
        $this->serviceReqId = $serviceReqId;
        $this->serviceReqIds = $serviceReqIds;
        $this->serviceTypeCode = $serviceTypeCode;
        $this->executeRoomId = $executeRoomId;
    }
}