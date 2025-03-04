<?php

namespace App\DTOs;

class DebateListVViewDTO
{
    public $debateListVViewName;
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
    public $treatmentId;
    public $departmentIds;
    public $debateTimeFrom;
    public $debateTimeTo;
    public function __construct(
        $debateListVViewName,
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
        $treatmentId,
        $departmentIds,
        $debateTimeFrom,
        $debateTimeTo,
        )
    {
        $this->debateListVViewName = $debateListVViewName;
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
        $this->treatmentId = $treatmentId;
        $this->departmentIds = $departmentIds;
        $this->debateTimeFrom = $debateTimeFrom;
        $this->debateTimeTo = $debateTimeTo;
    }
}