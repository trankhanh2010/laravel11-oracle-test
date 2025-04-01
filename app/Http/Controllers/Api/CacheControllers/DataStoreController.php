<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DataStoreDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DataStore\CreateDataStoreRequest;
use App\Http\Requests\DataStore\UpdateDataStoreRequest;
use App\Models\HIS\DataStore;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DataStoreService;
use Illuminate\Http\Request;


class DataStoreController extends BaseApiCacheController
{
    protected $dataStoreService;
    protected $dataStoreDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DataStoreService $dataStoreService, DataStore $dataStore)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->dataStoreService = $dataStoreService;
        $this->dataStore = $dataStore;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'parent_data_store_code',
                'parent_data_store_name',
                'stored_department_code',
                'stored_department_name',
                'department_code',
                'department_name',
            ];
            $columns = $this->getColumnsTable($this->dataStore);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->dataStoreDTO = new DataStoreDTO(
            $this->dataStoreName,
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
        $this->dataStoreService->withParams($this->dataStoreDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->dataStoreName);
            } else {
                $data = $this->dataStoreService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->dataStoreName);
            } else {
                $data = $this->dataStoreService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->dataStore, $this->dataStoreName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->dataStoreName, $id);
        } else {
            $data = $this->dataStoreService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateDataStoreRequest $request)
    {
        return $this->dataStoreService->createDataStore($request);
    }
    public function update(UpdateDataStoreRequest $request, $id)
    {
        return $this->dataStoreService->updateDataStore($id, $request);
    }
    public function destroy($id)
    {
        return $this->dataStoreService->deleteDataStore($id);
    }
}
