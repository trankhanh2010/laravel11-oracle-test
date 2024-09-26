<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceType\CreateServiceTypeRequest;
use App\Http\Requests\ServiceType\UpdateServiceTypeRequest;
use App\Models\HIS\ServiceType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceTypeService;
use Illuminate\Http\Request;


class ServiceTypeController extends BaseApiCacheController
{
    protected $serviceTypeService;
    protected $serviceTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceTypeService $serviceTypeService, ServiceType $serviceType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceTypeService = $serviceTypeService;
        $this->serviceType = $serviceType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'module_link',
                'exe_service_module_name'
            ];
            $columns = $this->getColumnsTable($this->serviceType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceTypeDTO = new ServiceTypeDTO(
            $this->serviceTypeName,
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
        $this->serviceTypeService->withParams($this->serviceTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceTypeName);
            } else {
                $data = $this->serviceTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceTypeName);
            } else {
                $data = $this->serviceTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceType, $this->serviceTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceTypeName, $id);
        } else {
            $data = $this->serviceTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
