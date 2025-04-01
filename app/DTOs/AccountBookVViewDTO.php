<?php

namespace App\DTOs;

class AccountBookVViewDTO
{
    public $accountBookVViewName;
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
    public $isForDeposit;
    public $isForRepay;
    public $isForBill;   
    // public $isOutOfBill;
    // public $forDeposit;
    // public $loginname;
    // public $cashierroomId;
    public $param;
    public $noCache;
    public function __construct(
        $accountBookVViewName,
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
        $isForDeposit,
        $isForRepay,
        $isForBill,
        // $isOutOfBill,
        // $forDeposit,
        // $loginname,
        // $cashierroomId,
        $param,
        $noCache,
        )
    {
        $this->accountBookVViewName = $accountBookVViewName;
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
        $this->isForDeposit = $isForDeposit;
        $this->isForRepay = $isForRepay;
        $this->isForBill = $isForBill;
        // $this->isOutOfBill = $isOutOfBill;
        // $this->forDeposit = $forDeposit;
        // $this->loginname = $loginname;
        // $this->cashierroomId = $cashierroomId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}