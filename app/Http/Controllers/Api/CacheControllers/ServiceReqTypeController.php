<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceReqTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceReqType\CreateServiceReqTypeRequest;
use App\Http\Requests\ServiceReqType\UpdateServiceReqTypeRequest;
use App\Models\HIS\ServiceReqType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceReqTypeService;
use Illuminate\Http\Request;


class ServiceReqTypeController extends BaseApiCacheController
{
    protected $serviceReqTypeService;
    protected $serviceReqTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceReqTypeService $serviceReqTypeService, ServiceReqType $serviceReqType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceReqTypeService = $serviceReqTypeService;
        $this->serviceReqType = $serviceReqType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->serviceReqType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceReqTypeDTO = new ServiceReqTypeDTO(
            $this->serviceReqTypeName,
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
        $this->serviceReqTypeService->withParams($this->serviceReqTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceReqTypeName);
            } else {
                $data = $this->serviceReqTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceReqTypeName);
            } else {
                $data = $this->serviceReqTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceReqType, $this->serviceReqTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceReqTypeName, $id);
        } else {
            $data = $this->serviceReqTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }

    public function store(CreateServiceReqTypeRequest $request)
    {
        return $this->serviceReqTypeService->createServiceReqType($request);
    }
    public function update(UpdateServiceReqTypeRequest $request, $id)
    {
        return $this->serviceReqTypeService->updateServiceReqType($id, $request);
    }
    public function destroy($id)
    {
        return $this->serviceReqTypeService->deleteServiceReqType($id);
    }
}
