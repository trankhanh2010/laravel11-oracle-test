<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\StorageConditionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\StorageCondition\CreateStorageConditionRequest;
use App\Http\Requests\StorageCondition\UpdateStorageConditionRequest;
use App\Models\HIS\StorageCondition;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\StorageConditionService;
use Illuminate\Http\Request;


class StorageConditionController extends BaseApiCacheController
{
    protected $storageConditionService;
    protected $storageConditionDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, StorageConditionService $storageConditionService, StorageCondition $storageCondition)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->storageConditionService = $storageConditionService;
        $this->storageCondition = $storageCondition;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->storageCondition);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->storageConditionDTO = new StorageConditionDTO(
            $this->storageConditionName,
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
        $this->storageConditionService->withParams($this->storageConditionDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->storageConditionName);
            } else {
                $data = $this->storageConditionService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->storageConditionName);
            } else {
                $data = $this->storageConditionService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->storageCondition, $this->storageConditionName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->storageConditionName, $id);
        } else {
            $data = $this->storageConditionService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateStorageConditionRequest $request)
    {
        return $this->storageConditionService->createStorageCondition($request);
    }
    public function update(UpdateStorageConditionRequest $request, $id)
    {
        return $this->storageConditionService->updateStorageCondition($id, $request);
    }
    public function destroy($id)
    {
        return $this->storageConditionService->deleteStorageCondition($id);
    }
}
