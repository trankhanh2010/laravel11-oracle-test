<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\CashierRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\CashierRoom\CreateCashierRoomRequest;
use App\Http\Requests\CashierRoom\UpdateCashierRoomRequest;
use App\Models\HIS\CashierRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\CashierRoomService;
use Illuminate\Http\Request;


class CashierRoomController extends BaseApiCacheController
{
    protected $cashierRoomService;
    protected $cashierRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, CashierRoomService $cashierRoomService, CashierRoom $cashierRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->cashierRoomService = $cashierRoomService;
        $this->cashierRoom = $cashierRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'is_pause',
                'department_id',
                'area_id',
                'room_type_name',
                'room_type_code',
                'department_name',
                'department_code',
                'area_name',
                'area_code',
            ];
            $columns = $this->getColumnsTable($this->cashierRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->cashierRoomDTO = new CashierRoomDTO(
            $this->cashierRoomName,
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
        $this->cashierRoomService->withParams($this->cashierRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->cashierRoomName);
            } else {
                $data = $this->cashierRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->cashierRoomName);
            } else {
                $data = $this->cashierRoomService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->cashierRoom, $this->cashierRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->cashierRoomName, $id);
        } else {
            $data = $this->cashierRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateCashierRoomRequest $request)
    {
        return $this->cashierRoomService->createCashierRoom($request);
    }
    public function update(UpdateCashierRoomRequest $request, $id)
    {
        return $this->cashierRoomService->updateCashierRoom($id, $request);
    }
    public function destroy($id)
    {
        return $this->cashierRoomService->deleteCashierRoom($id);
    }
}
