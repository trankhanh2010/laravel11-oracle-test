<?php

namespace App\DTOs;

class BedBstyDTO
{
    public $bedBstyName;
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
    public $serviceIds;
    public $bedIds;
    public $param;
    public $noCache;
    public function __construct(
        $bedBstyName,
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
        $serviceIds,
        $bedIds,
        $param,
        $noCache,
        )
    {
        $this->bedBstyName = $bedBstyName;
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
        $this->serviceIds = $serviceIds;
        $this->bedIds = $bedIds;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}