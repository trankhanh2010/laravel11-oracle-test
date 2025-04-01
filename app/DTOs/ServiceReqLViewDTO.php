<?php

namespace App\DTOs;

class ServiceReqLViewDTO
{
    public $serviceReqName;
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
    public $serviceReqSttIds;
    public $notInServiceReqTypeIds;
    public $tdlPatientTypeIds;
    public $executeRoomId;
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public $hasExecute;
    public $isNotKskRequriedAprovalOrIsKskApprove;
    public $param;
    public $noCache;
    public function __construct(
        $serviceReqName,
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
        $serviceReqSttIds,
        $notInServiceReqTypeIds,
        $tdlPatientTypeIds,
        $executeRoomId,
        $intructionTimeFrom,
        $intructionTimeTo,
        $hasExecute,
        $isNotKskRequriedAprovalOrIsKskApprove,
        $param,
        $noCache,
        )
    {
        $this->serviceReqName = $serviceReqName;
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
        $this->serviceReqSttIds = $serviceReqSttIds;
        $this->notInServiceReqTypeIds = $notInServiceReqTypeIds;
        $this->tdlPatientTypeIds = $tdlPatientTypeIds;
        $this->executeRoomId = $executeRoomId;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->hasExecute = $hasExecute;
        $this->isNotKskRequriedAprovalOrIsKskApprove = $isNotKskRequriedAprovalOrIsKskApprove;
        $this->param = $param;
        $this->noCache = $noCache;
    }
}