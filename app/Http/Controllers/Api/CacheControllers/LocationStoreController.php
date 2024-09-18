<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\LocationStoreDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\LocationTreatment\CreateLocationTreatmentRequest;
use App\Http\Requests\LocationTreatment\UpdateLocationTreatmentRequest;
use App\Models\HIS\LocationStore;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\LocationStoreService;
use Illuminate\Http\Request;


class LocationStoreController extends BaseApiCacheController
{
    protected $locationStoreService;
    protected $locationStoreDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, LocationStoreService $locationStoreService, LocationStore $locationStore)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->locationStoreService = $locationStoreService;
        $this->locationStore = $locationStore;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'data_store_code',
                'data_store_name'
            ];
            $columns = $this->getColumnsTable($this->locationStore);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->locationStoreDTO = new LocationStoreDTO(
            $this->locationStoreName,
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
        $this->locationStoreService->withParams($this->locationStoreDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->locationStoreName);
            } else {
                $data = $this->locationStoreService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->locationStoreName);
            } else {
                $data = $this->locationStoreService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->locationStore, $this->locationStoreName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->locationStoreName, $id);
        } else {
            $data = $this->locationStoreService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateLocationTreatmentRequest $request)
    {
        return $this->locationStoreService->createLocationStore($request);
    }
    public function update(UpdateLocationTreatmentRequest $request, $id)
    {
        return $this->locationStoreService->updateLocationStore($id, $request);
    }
    public function destroy($id)
    {
        return $this->locationStoreService->deleteLocationStore($id);
    }
}
