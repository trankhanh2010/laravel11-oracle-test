<?php

namespace App\DTOs;

class TreatmentExecuteRoomListVViewDTO
{
    public $treatmentExecuteRoomListVViewName;
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
    public $treatmentTypeIds;
    public $isCoTreatDepartment;
    public $patientClassifyIds;
    public $isOut;
    public $addLoginname;
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public $groupBy;
    public $executeRoomCode;
    public $executeRoomIds;
    public $serviceReqSttCodes;

    public function __construct(
        $treatmentExecuteRoomListVViewName,
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
        $treatmentTypeIds,
        $isCoTreatDepartment,
        $patientClassifyIds,
        $isOut,
        $addLoginname,
        $intructionTimeFrom,
        $intructionTimeTo,
        $groupBy,
        $executeRoomCode,
        $executeRoomIds,
        $serviceReqSttCodes,
        )
    {
        $this->treatmentExecuteRoomListVViewName = $treatmentExecuteRoomListVViewName;
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
        $this->treatmentTypeIds = $treatmentTypeIds;
        $this->isCoTreatDepartment = $isCoTreatDepartment;
        $this->patientClassifyIds = $patientClassifyIds;
        $this->isOut = $isOut;
        $this->addLoginname = $addLoginname;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->groupBy = $groupBy;
        $this->executeRoomCode = $executeRoomCode;
        $this->executeRoomIds = $executeRoomIds;
        $this->serviceReqSttCodes = $serviceReqSttCodes;
    }
}