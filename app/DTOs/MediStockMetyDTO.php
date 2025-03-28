<?php

namespace App\DTOs;

class MediStockMetyDTO
{
    public $mediStockMetyName;
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
    public $medicineTypeId;
    public $param;
    public function __construct(
        $mediStockMetyName,
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
        $medicineTypeId,
        $param,
        )
    {
        $this->mediStockMetyName = $mediStockMetyName;
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
        $this->medicineTypeId = $medicineTypeId;
        $this->param = $param;
    }
}