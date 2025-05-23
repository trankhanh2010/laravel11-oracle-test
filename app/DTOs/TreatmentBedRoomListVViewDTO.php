<?php

namespace App\DTOs;

class TreatmentBedRoomListVViewDTO
{
    public $treatmentBedRoomListVViewName;
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
    public $departmentCode;
    public $isInBed;
    public $bedRoomIds;
    public $treatmentTypeIds;
    public $isCoTreatDepartment;
    public $patientClassifyIds;
    public $isOut;
    public $addLoginname;
    public $addTimeFrom;
    public $addTimeTo;
    public $groupBy;
    public $executeRoomCode;
    public $executeRoomIds;
    public $treatmentCode;
    public $patientCode;
    public $isCount;
    public $param;
    public $noCache;
    public function __construct(
        $treatmentBedRoomListVViewName,
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
        $departmentCode,
        $isInBed,
        $bedRoomIds,
        $treatmentTypeIds,
        $isCoTreatDepartment,
        $patientClassifyIds,
        $isOut,
        $addLoginname,
        $addTimeFrom,
        $addTimeTo,
        $groupBy,
        $executeRoomCode,
        $executeRoomIds,
        $treatmentCode,
        $patientCode,
        $isCount,
        $param,
        $noCache,
        )
    {
        $this->treatmentBedRoomListVViewName = $treatmentBedRoomListVViewName;
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
        $this->departmentCode = $departmentCode;
        $this->isInBed = $isInBed;
        $this->bedRoomIds = $bedRoomIds;
        $this->treatmentTypeIds = $treatmentTypeIds;
        $this->isCoTreatDepartment = $isCoTreatDepartment;
        $this->patientClassifyIds = $patientClassifyIds;
        $this->isOut = $isOut;
        $this->addLoginname = $addLoginname;
        $this->addTimeFrom = $addTimeFrom;
        $this->addTimeTo = $addTimeTo;
        $this->groupBy = $groupBy;
        $this->executeRoomCode = $executeRoomCode;
        $this->executeRoomIds = $executeRoomIds;
        $this->treatmentCode = $treatmentCode;
        $this->patientCode = $patientCode;
        $this->isCount = $isCount;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}