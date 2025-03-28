<?php

namespace App\DTOs;

class SereServTeinListVViewDTO
{
    public $sereServTeinListVViewName;
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
    public $serviceReqId;
    public $groupBy;
    public $sereServIds;
    public $param;
    public function __construct(
        $sereServTeinListVViewName,
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
        $serviceReqId,
        $groupBy,
        $sereServIds,
        $param,
        )
    {
        $this->sereServTeinListVViewName = $sereServTeinListVViewName;
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
        $this->serviceReqId = $serviceReqId;
        $this->groupBy = $groupBy;
        $this->sereServIds = $sereServIds;
        $this->param = $param;
    }
}