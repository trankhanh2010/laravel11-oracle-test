<?php

namespace App\DTOs;

class RoomDTO
{
    public $roomName;
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
    public $departmentId;
    public $roomTypeId;
    public $param;
    public $noCache;
    public function __construct(
        $roomName,
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
        $departmentId,
        $roomTypeId,
        $param,
        $noCache,
        )
    {
        $this->roomName = $roomName;
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
        $this->departmentId = $departmentId;
        $this->roomTypeId = $roomTypeId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}