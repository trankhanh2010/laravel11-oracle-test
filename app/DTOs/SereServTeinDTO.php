<?php

namespace App\DTOs;

class SereServTeinDTO
{
    public $sereServTeinName;
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
    public $testIndexIds;
    public $tdlTreatmentId;
    public $param;
    public $noCache;
    public function __construct(
        $sereServTeinName,
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
        $testIndexIds,
        $tdlTreatmentId,
        $param,
        $noCache,
        )
    {
        $this->sereServTeinName = $sereServTeinName;
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
        $this->testIndexIds = $testIndexIds;
        $this->tdlTreatmentId = $tdlTreatmentId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}