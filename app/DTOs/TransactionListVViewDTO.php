<?php

namespace App\DTOs;

class TransactionListVViewDTO
{
    public $transactionListVViewName;
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
    public $createFromTime;
    public $createToTime;
    public $transactionTypeIds;
    public $lastId;
    public $cursorPaginate;
    public $treatmentCode;
    public $transactionCode;
    public $param;
    public $noCache;
    public $transReqCode;
    public $accountBookCode;
    public $isCancel;
    public $billTypeId;
    public function __construct(
        $transactionListVViewName,
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
        $createFromTime,
        $createToTime,
        $transactionTypeIds,
        $lastId,
        $cursorPaginate,
        $treatmentCode,
        $transactionCode,
        $param,
        $noCache,
        $transReqCode,
        $accountBookCode,
        $isCancel,
        $billTypeId,
        )
    {
        $this->transactionListVViewName = $transactionListVViewName;
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
        $this->createFromTime = $createFromTime;
        $this->createToTime = $createToTime;
        $this->transactionTypeIds = $transactionTypeIds;
        $this->lastId = $lastId;
        $this->cursorPaginate = $cursorPaginate;
        $this->treatmentCode = $treatmentCode;
        $this->transactionCode = $transactionCode;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->transReqCode = $transReqCode;
        $this->accountBookCode = $accountBookCode; 
        $this->isCancel = $isCancel;
        $this->billTypeId = $billTypeId;
    }
}