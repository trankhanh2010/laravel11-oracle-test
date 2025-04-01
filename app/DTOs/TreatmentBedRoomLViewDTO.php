<?php

namespace App\DTOs;

class TreatmentBedRoomLViewDTO
{
    public $treatmentBedRoomLViewName;
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
    public $bedRoomIds;
    public $addTimeTo;
    public $addTimeFrom;
    public $isInRoom;
    public $param;
    public $noCache;
    public function __construct(
        $treatmentBedRoomLViewName,
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
        $bedRoomIds,
        $addTimeTo,
        $addTimeFrom,
        $isInRoom,
        $param,
        $noCache,
        )
    {
        $this->treatmentBedRoomLViewName = $treatmentBedRoomLViewName;
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
        $this->bedRoomIds = $bedRoomIds;
        $this->addTimeTo = $addTimeTo;
        $this->addTimeFrom = $addTimeFrom;
        $this->isInRoom = $isInRoom;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}