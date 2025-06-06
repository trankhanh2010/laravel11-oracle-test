<?php

namespace App\DTOs;

class DepartmentDTO
{
    public $departmentName;
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
    public $tab;
    public $param;
    public $noCache;
    public $isClinical; 
    public $treatmentTypeId;
    public function __construct(
        $departmentName,
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
        $tab,
        $param,
        $noCache,
        $isClinical,
        $treatmentTypeId,
        )
    {
        $this->departmentName = $departmentName;
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
        $this->tab = $tab;
        $this->param = $param;
        $this->noCache = $noCache;
        $this->isClinical = $isClinical;
        $this->treatmentTypeId = $treatmentTypeId;
    }
}