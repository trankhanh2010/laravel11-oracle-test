<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\BloodGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\BloodGroup\CreateBloodGroupRequest;
use App\Http\Requests\BloodGroup\UpdateBloodGroupRequest;
use App\Models\HIS\BloodGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\BloodGroupService;
use Illuminate\Http\Request;


class BloodGroupController extends BaseApiCacheController
{
    protected $bloodGroupService;
    protected $bloodGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, BloodGroupService $bloodGroupService, BloodGroup $bloodGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->bloodGroupService = $bloodGroupService;
        $this->bloodGroup = $bloodGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->bloodGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->bloodGroupDTO = new BloodGroupDTO(
            $this->bloodGroupName,
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
        $this->bloodGroupService->withParams($this->bloodGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->bloodGroupName);
            } else {
                $data = $this->bloodGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->bloodGroupName);
            } else {
                $data = $this->bloodGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->bloodGroup, $this->bloodGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->bloodGroupName, $id);
        } else {
            $data = $this->bloodGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateBloodGroupRequest $request)
    {
        return $this->bloodGroupService->createBloodGroup($request);
    }
    public function update(UpdateBloodGroupRequest $request, $id)
    {
        return $this->bloodGroupService->updateBloodGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->bloodGroupService->deleteBloodGroup($id);
    }
}
