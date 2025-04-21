<?php

namespace App\DTOs;

class MedicineListVViewDTO
{
    public $medicineListVViewName;
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
    public $param;
    public $noCache;
    public $tab;
    public $treatmentCode;
    public $patientCode;
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public function __construct(
        $medicineListVViewName,
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
        $param,
        $noCache,
        $tab,
        $treatmentCode,
        $patientCode,
        $intructionTimeFrom,
        $intructionTimeTo,
        )
    {
        $this->medicineListVViewName = $medicineListVViewName;
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
        $this->param = $param;
        $this->noCache = $noCache;
        $this->tab = $tab;
        $this->treatmentCode = $treatmentCode;
        $this->patientCode = $patientCode;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
    }
}