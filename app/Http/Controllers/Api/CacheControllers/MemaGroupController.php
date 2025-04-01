<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MemaGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MemaGroup\CreateMemaGroupRequest;
use App\Http\Requests\MemaGroup\UpdateMemaGroupRequest;
use App\Models\HIS\MemaGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MemaGroupService;
use Illuminate\Http\Request;


class MemaGroupController extends BaseApiCacheController
{
    protected $memaGroupService;
    protected $memaGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MemaGroupService $memaGroupService, MemaGroup $memaGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->memaGroupService = $memaGroupService;
        $this->memaGroup = $memaGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->memaGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->memaGroupDTO = new MemaGroupDTO(
            $this->memaGroupName,
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
        $this->memaGroupService->withParams($this->memaGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->memaGroupName);
            } else {
                $data = $this->memaGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->memaGroupName);
            } else {
                $data = $this->memaGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->memaGroup, $this->memaGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->memaGroupName, $id);
        } else {
            $data = $this->memaGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMemaGroupRequest $request)
    {
        return $this->memaGroupService->createMemaGroup($request);
    }
    public function update(UpdateMemaGroupRequest $request, $id)
    {
        return $this->memaGroupService->updateMemaGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->memaGroupService->deleteMemaGroup($id);
    }
}
