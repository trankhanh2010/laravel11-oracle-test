<?php

namespace App\DTOs;

class TestServiceReqListVViewDTO
{
    public $testServiceReqListVViewName;
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
    public $fromTime;
    public $toTime;
    public $executeDepartmentCode;
    public $isNoExcute;
    public $isSpecimen;
    public function __construct(
        $testServiceReqListVViewName,
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
        $fromTime,
        $toTime,
        $executeDepartmentCode,
        $isNoExcute,
        $isSpecimen,
        )
    {
        $this->testServiceReqListVViewName = $testServiceReqListVViewName;
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
        $this->fromTime = $fromTime;
        $this->toTime = $toTime;
        $this->executeDepartmentCode = $executeDepartmentCode;
        $this->isNoExcute = $isNoExcute;
        $this->isSpecimen = $isSpecimen;
    }
}