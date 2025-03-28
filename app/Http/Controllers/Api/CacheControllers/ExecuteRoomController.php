<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExecuteRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExecuteRoom\CreateExecuteRoomRequest;
use App\Http\Requests\ExecuteRoom\UpdateExecuteRoomRequest;
use App\Models\HIS\ExecuteRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ExecuteRoomService;
use Illuminate\Http\Request;


class ExecuteRoomController extends BaseApiCacheController
{
    protected $executeRoomService;
    protected $executeRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ExecuteRoomService $executeRoomService, ExecuteRoom $executeRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->executeRoomService = $executeRoomService;
        $this->executeRoom = $executeRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'department_code',
                'department_name',
                'area_code',
                'area_name',
                'room_group_code',
                'room_group_name',
                'room_type_code',
                'room_type_name',
                'speciality_code',
                'speciality_name',
                'default_cashier_room_code',
                'default_cashier_room_name',
                'patient_type_code',
                'patient_type_name',
                'default_service_name',
                'default_service_code',
                'deposit_account_book_code',
                'deposit_account_book_name',
                'bill_account_book_code',
                'bill_account_book_name',
            ];
            $columns = $this->getColumnsTable($this->executeRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->executeRoomDTO = new ExecuteRoomDTO(
            $this->executeRoomName,
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
        $this->executeRoomService->withParams($this->executeRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->executeRoomName);
            } else {
                $data = $this->executeRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->executeRoomName);
            } else {
                $data = $this->executeRoomService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->executeRoom, $this->executeRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->executeRoomName, $id);
        } else {
            $data = $this->executeRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateExecuteRoomRequest $request)
    {
        return $this->executeRoomService->createExecuteRoom($request);
    }
    public function update(UpdateExecuteRoomRequest $request, $id)
    {
        return $this->executeRoomService->updateExecuteRoom($id, $request);
    }
    public function destroy($id)
    {
        return $this->executeRoomService->deleteExecuteRoom($id);
    }
}
