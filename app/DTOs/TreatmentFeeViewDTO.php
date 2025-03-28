<?php

namespace App\DTOs;

class TreatmentFeeViewDTO
{
    public $treatmentFeeViewName;
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
    public $tdlTreatmentTypeIds;
    public $tdlPatientTypeIds;
    public $branchId;
    public $inDateFrom;
    public $inDateTo;
    public $isApproveStore;
    public $param;
    public function __construct(
        $treatmentFeeViewName,
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
        $tdlTreatmentTypeIds,
        $tdlPatientTypeIds,
        $branchId,
        $inDateFrom,
        $inDateTo,
        $isApproveStore,
        $param,
        )
    {
        $this->treatmentFeeViewName = $treatmentFeeViewName;
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
        $this->tdlTreatmentTypeIds = $tdlTreatmentTypeIds;
        $this->tdlPatientTypeIds =$tdlPatientTypeIds;
        $this->branchId = $branchId;
        $this->inDateFrom = $inDateFrom;
        $this->inDateTo = $inDateTo;
        $this->isApproveStore = $isApproveStore;
        $this->param = $param;
    }
}