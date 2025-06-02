<?php

namespace App\DTOs;

class YeuCauKhamClsPtttVViewDTO
{
    public $yeuCauKhamClsPtttVViewName;
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
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public $intructionTimeDate;
    public $intructionTimeMonth;
    public $executeRoomId;
    public $treatmentTypeIds;
    public $serviceReqCode;
    public $bedCode;
    public $trangThai;
    public $trangThaiVienPhi;
    public $trangThaiKeThuoc;
    public function __construct(
        $yeuCauKhamClsPtttVViewName,
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
        $intructionTimeFrom,
        $intructionTimeTo,
        $intructionTimeDate,
        $intructionTimeMonth,
        $executeRoomId,
        $treatmentTypeIds,
        $serviceReqCode,
        $bedCode,
        $trangThai,
        $trangThaiVienPhi,
        $trangThaiKeThuoc,
        )
    {
        $this->yeuCauKhamClsPtttVViewName = $yeuCauKhamClsPtttVViewName;
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
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->intructionTimeDate = $intructionTimeDate;
        $this->intructionTimeMonth = $intructionTimeMonth;
        $this->executeRoomId = $executeRoomId;
        $this->treatmentTypeIds = $treatmentTypeIds;
        $this->serviceReqCode = $serviceReqCode;
        $this->bedCode = $bedCode;
        $this->trangThai = $trangThai;
        $this->trangThaiVienPhi = $trangThaiVienPhi;
        $this->trangThaiKeThuoc = $trangThaiKeThuoc;
    }
}