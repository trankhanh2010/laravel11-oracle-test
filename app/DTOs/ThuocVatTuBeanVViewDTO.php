<?php

namespace App\DTOs;

class ThuocVatTuBeanVViewDTO
{
    public $thuocVatTuBeanVViewName;
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
    public $param;
    public $noCache;
    public $mediStockIds;
    public $tab;
    public $type;
    public $intructionTime;
    public function __construct(
        $thuocVatTuBeanVViewName,
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
        $param,
        $noCache,
        $mediStockIds,
        $tab,
        $type,
        $intructionTime,
        )
    {
        $this->thuocVatTuBeanVViewName = $thuocVatTuBeanVViewName;
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
        $this->param = $param;
        $this->noCache = $noCache;
        $this->mediStockIds = $mediStockIds;
        $this->tab = $tab;
        $this->type = $type;
        $this->intructionTime = $intructionTime;
    }
}