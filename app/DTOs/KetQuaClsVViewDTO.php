<?php

namespace App\DTOs;

class KetQuaClsVViewDTO
{
    public $ketQuaClsVViewName;
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
    public $treatmentId;
    public $hienThiDichVuChaLoaiXN;
    public $intructionTimeFrom;
    public $intructionTimeTo;
    public $trenNguong;
    public $duoiNguong;
    public $chiSoQuanTrong;
    public function __construct(
        $ketQuaClsVViewName,
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
        $treatmentId,
        $hienThiDichVuChaLoaiXN,
        $intructionTimeFrom,
        $intructionTimeTo,
        $trenNguong,
        $duoiNguong,
        $chiSoQuanTrong,
        )
    {
        $this->ketQuaClsVViewName = $ketQuaClsVViewName;
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
        $this->treatmentId = $treatmentId;
        $this->hienThiDichVuChaLoaiXN = $hienThiDichVuChaLoaiXN;
        $this->intructionTimeFrom = $intructionTimeFrom;
        $this->intructionTimeTo = $intructionTimeTo;
        $this->trenNguong = $trenNguong;
        $this->duoiNguong = $duoiNguong;
        $this->chiSoQuanTrong = $chiSoQuanTrong;
    }
}