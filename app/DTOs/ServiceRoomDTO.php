<?php

namespace App\DTOs;

class ServiceRoomDTO
{
    public $serviceRoomName;
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
    public $serviceId;
    public $roomId;
    public $param;
    public $noCache;
    public $roomIds;
    public function __construct(
        $serviceRoomName,
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
        $serviceId,
        $roomId,
        $param,
        $noCache,
        $roomIds,
        )
    {
        $this->serviceRoomName = $serviceRoomName;
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
        $this->serviceId = $serviceId;
        $this->roomId = $roomId;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->roomIds = $roomIds;
    }
}