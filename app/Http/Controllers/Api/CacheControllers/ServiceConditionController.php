<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceConditionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceCondition\CreateServiceConditionRequest;
use App\Http\Requests\ServiceCondition\UpdateServiceConditionRequest;
use App\Models\HIS\ServiceCondition;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceConditionService;
use Illuminate\Http\Request;


class ServiceConditionController extends BaseApiCacheController
{
    protected $serviceConditionService;
    protected $serviceConditionDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceConditionService $serviceConditionService, ServiceCondition $serviceCondition)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceConditionService = $serviceConditionService;
        $this->serviceCondition = $serviceCondition;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->serviceCondition);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceConditionDTO = new ServiceConditionDTO(
            $this->serviceConditionName,
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
        );
        $this->serviceConditionService->withParams($this->serviceConditionDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceConditionName);
            } else {
                $data = $this->serviceConditionService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceConditionName);
            } else {
                $data = $this->serviceConditionService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceCondition, $this->serviceConditionName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceConditionName, $id);
        } else {
            $data = $this->serviceConditionService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServiceConditionRequest $request)
    {
        return $this->serviceConditionService->createServiceCondition($request);
    }
    public function update(UpdateServiceConditionRequest $request, $id)
    {
        return $this->serviceConditionService->updateServiceCondition($id, $request);
    }
    public function destroy($id)
    {
        return $this->serviceConditionService->deleteServiceCondition($id);
    }
}
