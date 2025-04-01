<?php

namespace App\DTOs;

class TrackingListVViewDTO
{
    public $trackingListVViewName;
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
    public $groupBy;
    public $param;
    public $noCache;
    public function __construct(
        $trackingListVViewName,
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
        $groupBy,
        $param,
        $noCache,
        )
    {
        $this->trackingListVViewName = $trackingListVViewName;
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
        $this->groupBy = $groupBy;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}