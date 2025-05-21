<?php

namespace App\DTOs;

class BangKeVViewDTO
{
    public $bangKeVViewName;
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
    public $treatmentId;
    public $param;
    public $noCache;
    public $groupBy;
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public $amountGreaterThan0;
    public $tab;
    public $status;

    public function __construct(
        $bangKeVViewName,
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
        $treatmentId,
        $param,
        $noCache,
        $groupBy,
        $intructionTimeFrom,
        $intructionTimeTo,
        $amountGreaterThan0,
        $tab,
        $status,
        )
    {
        $this->bangKeVViewName = $bangKeVViewName;
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
        $this->treatmentId = $treatmentId;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->groupBy = $groupBy;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->amountGreaterThan0 = $amountGreaterThan0;
        $this->tab = $tab;
        $this->status = $status;
    }
}