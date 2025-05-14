<?php

namespace App\DTOs;

class TransactionTUDetailVViewDTO
{
    public $transactionTUDetailVViewName;
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
    public $depositId;
    public $depositCode;
    public $param;
    public $noCache;
    public $groupBy;
    public function __construct(
        $transactionTUDetailVViewName,
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
        $depositId,
        $depositCode,
        $param,
        $noCache,
        $groupBy,
        )
    {
        $this->transactionTUDetailVViewName = $transactionTUDetailVViewName;
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
        $this->depositId = $depositId;
        $this->depositCode = $depositCode;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->groupBy = $groupBy;
    }
}