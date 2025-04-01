<?php

namespace App\DTOs;

class ExroRoomDTO
{
    public $exroRoomName;
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
    public $roomId;
    public $executeRoomId;
    public $param;
    public $noCache;
    public function __construct(
        $exroRoomName,
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
        $roomId,
        $executeRoomId,
        $param,
        $noCache,
        )
    {
        $this->exroRoomName = $exroRoomName;
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
        $this->roomId = $roomId;
        $this->executeRoomId = $executeRoomId;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}