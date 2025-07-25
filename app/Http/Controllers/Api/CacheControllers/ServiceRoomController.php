<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceRoomDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceRoom\CreateServiceRoomRequest;
use App\Http\Requests\ServiceRoom\UpdateServiceRoomRequest;
use App\Models\HIS\ServiceRoom;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceRoomService;
use Illuminate\Http\Request;


class ServiceRoomController extends BaseApiCacheController
{
    protected $serviceRoomService;
    protected $serviceRoomDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceRoomService $serviceRoomService, ServiceRoom $serviceRoom)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceRoomService = $serviceRoomService;
        $this->serviceRoom = $serviceRoom;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'service_name',
                'service_code',
                'service_type_name',
                'service_type_code',
                'room_type_name',
                'room_type_code',
                'department_name',
                'department_code',
                'room_code',
                'room_name',
            ];
            $columns = $this->getColumnsTable($this->serviceRoom);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceRoomDTO = new ServiceRoomDTO(
            $this->serviceRoomName,
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
            $this->serviceId,
            $this->roomId,
            $this->param,
            $this->noCache,
            $this->roomIds,
        );
        $this->serviceRoomService->withParams($this->serviceRoomDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceRoomName);
            } else {
                $data = $this->serviceRoomService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceRoomName);
            } else {
                $data = $this->serviceRoomService->handleDataBaseGetAll();
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
    public function guest()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->serviceRoomService->handleDataBaseGetAllGuest(); // không lấy mấy cái ngoài giờ, lọc có servicePaty có day_from = 2 và day_to = 7
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
            $validationError = $this->validateAndCheckId($id, $this->serviceRoom, $this->serviceRoomName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceRoomName, $id);
        } else {
            $data = $this->serviceRoomService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServiceRoomRequest $request)
    {
        return $this->serviceRoomService->createServiceRoom($request);
    }
}
