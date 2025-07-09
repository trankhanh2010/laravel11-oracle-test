<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TrackingTempDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;

use App\Models\HIS\TrackingTemp;
use App\Services\Model\TrackingTempService;
use Illuminate\Http\Request;


class TrackingTempController extends BaseApiCacheController
{
    protected $trackingTempService;
    protected $trackingTempDTO;
    public function __construct(Request $request, TrackingTempService $trackingTempService, TrackingTemp $trackingTemp)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->trackingTempService = $trackingTempService;
        $this->trackingTemp = $trackingTemp;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->trackingTemp);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->trackingTempDTO = new TrackingTempDTO(
            $this->trackingTempName,
            $this->keyword,
            $this->isActive,
            $this->orderBy,
            $this->orderByJoin,
            $this->orderByString,
            $this->getAll,
            $this->start,
            $this->limit,
            $request,
            $this->appCreator,
            $this->appModifier,
            $this->time,
            $this->param,
            $this->noCache,
            $this->currentLoginname,
            $this->currentDepartmentId,
            $this->roomId,
        );
        $this->trackingTempService->withParams($this->trackingTempDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($this->tab == 'selectByLoginname') {
            $data = $this->trackingTempService->handleDataBaseGetAllDataFromDatabaseSelectByLoginname();
        } else {
            $keyword = $this->keyword;
            if (($keyword != null) && !$this->cache) {
                $data = $this->trackingTempService->handleDataBaseSearch();
            } else {
                $data = $this->trackingTempService->handleDataBaseGetAll();
            }
        }
        $paramReturn = [
            $this->getAllName => $this->getAll,
            $this->startName => $this->getAll ? null : $this->start,
            $this->limitName => $this->getAll ? null : $this->limit,
            $this->countName => $data['count'],
            $this->isActiveName => $this->isActive,
            $this->keywordName => $this->keyword,
            $this->orderByName => $this->orderByRequest
        ];
        return returnDataSuccess($paramReturn, $data['data']);
    }

    public function show($id)
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($id !== null) {
            $validationError = $this->validateAndCheckId($id, $this->trackingTemp, $this->trackingTempName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->trackingTempService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
