<?php

namespace App\DTOs;

class ServiceReqListVViewDTO
{
    public $serviceReqLisyVViewName;
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
    public $groupBy;
    public $trackingId;
    public $treatmentId;
    public $param;
    public $noCache;
    public $treatmentCode;
    public $tab;
    public $patientId;
    public $serviceReqIds;
    public $patientCode;
    public $serviceReqCode;
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public $executeRoomId;
    public $serviceReqTypeIds;
    public $serviceReqSttIds;
    public $type;
    public $currentLoginname;
    public $roomId;
    public $storeCode;
    public function __construct(
        $serviceReqLisyVViewName,
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
        $groupBy,
        $trackingId,
        $treatmentId,
        $param,
        $noCache,
        $treatmentCode,
        $tab,
        $patientId,
        $serviceReqIds,
        $patientCode,
        $serviceReqCode,
        $intructionTimeFrom,
        $intructionTimeTo,
        $executeRoomId,
        $serviceReqTypeIds,
        $serviceReqSttIds,
        $type,
        $currentLoginname,
        $roomId,
        $storeCode,
        )
    {
        $this->serviceReqLisyVViewName = $serviceReqLisyVViewName;
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
        $this->groupBy = $groupBy;
        $this->trackingId = $trackingId;
        $this->treatmentId = $treatmentId;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->treatmentCode = $treatmentCode;
        $this->tab = $tab;
        $this->patientId  = $patientId;
        $this->serviceReqIds = $serviceReqIds;
        $this->patientCode = $patientCode;
        $this->serviceReqCode = $serviceReqCode;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->executeRoomId = $executeRoomId;
        $this->serviceReqTypeIds = $serviceReqTypeIds;
        $this->serviceReqSttIds = $serviceReqSttIds;
        $this->type = $type;
        $this->currentLoginname = $currentLoginname;
        $this->roomId = $roomId;
        $this->storeCode = $storeCode;
    }
}