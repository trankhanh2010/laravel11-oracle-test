<?php

namespace App\DTOs;

class UserAccountBookVViewDTO
{
    public $userAccountBookVViewName;
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
    public $tab;
    public $currentLoginname;
    public function __construct(
        $userAccountBookVViewName,
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
        $tab,
        $currentLoginname,
        )
    {
        $this->userAccountBookVViewName = $userAccountBookVViewName;
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
        $this->tab = $tab;
        $this->currentLoginname = $currentLoginname;
    }
}