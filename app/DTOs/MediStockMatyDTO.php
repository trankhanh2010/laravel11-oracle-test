<?php

namespace App\DTOs;

class MediStockMatyDTO
{
    public $mediStockMatyName;
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
    public $mediStockId;
    public $materialTypeId;
    public $param;
    public $noCache;
    public function __construct(
        $mediStockMatyName,
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
        $mediStockId,
        $materilalTypeId,
        $param,
        $noCache,
        )
    {
        $this->mediStockMatyName = $mediStockMatyName;
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
        $this->mediStockId = $mediStockId;
        $this->materialTypeId = $materilalTypeId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}