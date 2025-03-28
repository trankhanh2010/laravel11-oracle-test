<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\RoomTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\RoomType\CreateRoomTypeRequest;
use App\Http\Requests\RoomType\UpdateRoomTypeRequest;
use App\Models\HIS\RoomType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\RoomTypeService;
use Illuminate\Http\Request;


class RoomTypeController extends BaseApiCacheController
{
    protected $roomTypeService;
    protected $roomTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, RoomTypeService $roomTypeService, RoomType $roomType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->roomTypeService = $roomTypeService;
        $this->roomType = $roomType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->roomType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->roomTypeDTO = new RoomTypeDTO(
            $this->roomTypeName,
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
        $this->roomTypeService->withParams($this->roomTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->roomTypeName);
            } else {
                $data = $this->roomTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->roomTypeName);
            } else {
                $data = $this->roomTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->roomType, $this->roomTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->roomTypeName, $id);
        } else {
            $data = $this->roomTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateRoomTypeRequest $request)
    {
        return $this->roomTypeService->createRoomType($request);
    }
    public function update(UpdateRoomTypeRequest $request, $id)
    {
        return $this->roomTypeService->updateRoomType($id, $request);
    }
    public function destroy($id)
    {
        return $this->roomTypeService->deleteRoomType($id);
    }
}
