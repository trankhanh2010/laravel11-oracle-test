<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ReceptionRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ReceptionRoom\CreateReceptionRoomRequest;
use App\Http\Requests\ReceptionRoom\UpdateReceptionRoomRequest;
use App\Models\HIS\ReceptionRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ReceptionRoomService;
use Illuminate\Http\Request;


class ReceptionRoomController extends BaseApiCacheController
{
    protected $receptionRoomService;
    protected $receptionRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ReceptionRoomService $receptionRoomService, ReceptionRoom $receptionRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->receptionRoomService = $receptionRoomService;
        $this->receptionRoom = $receptionRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'department_id',
                'department_name',
                'department_code',
                'area_name',
                'area_code',
                'cashier_room_name',
                'cashier_room_code',
            ];
            $columns = $this->getColumnsTable($this->receptionRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->receptionRoomDTO = new ReceptionRoomDTO(
            $this->receptionRoomName,
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
        );
        $this->receptionRoomService->withParams($this->receptionRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->receptionRoomName);
            } else {
                $data = $this->receptionRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->receptionRoomName);
            } else {
                $data = $this->receptionRoomService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->receptionRoom, $this->receptionRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->receptionRoomName, $id);
        } else {
            $data = $this->receptionRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateReceptionRoomRequest $request)
    {
        return $this->receptionRoomService->createReceptionRoom($request);
    }
    public function update(UpdateReceptionRoomRequest $request, $id)
    {
        return $this->receptionRoomService->updateReceptionRoom($id, $request);
    }
    public function destroy($id)
    {
        return $this->receptionRoomService->deleteReceptionRoom($id);
    }
}
