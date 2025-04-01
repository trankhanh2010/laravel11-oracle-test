<?php

namespace App\DTOs;

class SereServDTO
{
    public $sereServName;
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
    public $serviceReqIds;
    public $serviceTypeId;
    public $treatmentId;
    public $param;
    public $noCache;
    public function __construct(
        $sereServName,
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
        $serviceReqIds,
        $serviceTypeId,
        $treatmentId,
        $param,
        $noCache,
        )
    {
        $this->sereServName = $sereServName;
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
        $this->serviceReqIds = $serviceReqIds;
        $this->serviceTypeId = $serviceTypeId;
        $this->treatmentId = $treatmentId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}