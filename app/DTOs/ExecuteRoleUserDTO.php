<?php

namespace App\DTOs;

class ExecuteRoleUserDTO
{
    public $executeRoleUserName;
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
    public $loginname;
    public $executeRoleId;
    public $param;
    public $noCache;
    public function __construct(
        $executeRoleUserName,
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
        $loginname,
        $executeRoleId,
        $param,
        $noCache,
        )
    {
        $this->executeRoleUserName = $executeRoleUserName;
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
        $this->loginname = $loginname;
        $this->executeRoleId = $executeRoleId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}