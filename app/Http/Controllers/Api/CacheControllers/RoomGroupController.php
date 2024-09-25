<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\RoomGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\RoomGroup\CreateRoomGroupRequest;
use App\Http\Requests\RoomGroup\UpdateRoomGroupRequest;
use App\Models\HIS\RoomGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\RoomGroupService;
use Illuminate\Http\Request;


class RoomGroupController extends BaseApiCacheController
{
    protected $roomGroupService;
    protected $roomGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, RoomGroupService $roomGroupService, RoomGroup $roomGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->roomGroupService = $roomGroupService;
        $this->roomGroup = $roomGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->roomGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->roomGroupDTO = new RoomGroupDTO(
            $this->roomGroupName,
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
        );
        $this->roomGroupService->withParams($this->roomGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->roomGroupName);
            } else {
                $data = $this->roomGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->roomGroupName);
            } else {
                $data = $this->roomGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->roomGroup, $this->roomGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->roomGroupName, $id);
        } else {
            $data = $this->roomGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateRoomGroupRequest $request)
    {
        return $this->roomGroupService->createRoomGroup($request);
    }
    // public function update(UpdateRoomGroupRequest $request, $id)
    // {
    //     return $this->roomGroupService->updateRoomGroup($id, $request);
    // }
    public function destroy($id)
    {
        return $this->roomGroupService->deleteRoomGroup($id);
    }
}
