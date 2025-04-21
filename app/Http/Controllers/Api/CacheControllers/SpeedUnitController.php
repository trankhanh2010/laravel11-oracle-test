<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\SpeedUnitDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\SpeedUnit\CreateSpeedUnitRequest;
use App\Http\Requests\SpeedUnit\UpdateSpeedUnitRequest;
use App\Models\HIS\SpeedUnit;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\SpeedUnitService;
use Illuminate\Http\Request;


class SpeedUnitController extends BaseApiCacheController
{
    protected $speedUnitService;
    protected $speedUnitDTO;
    public function __construct(Request $request, SpeedUnitService $speedUnitService, SpeedUnit $speedUnit)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->speedUnitService = $speedUnitService;
        $this->speedUnit = $speedUnit;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->speedUnit);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->speedUnitDTO = new SpeedUnitDTO(
            $this->speedUnitName,
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
        );
        $this->speedUnitService->withParams($this->speedUnitDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->speedUnitService->handleDataBaseGetAll();
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


}
