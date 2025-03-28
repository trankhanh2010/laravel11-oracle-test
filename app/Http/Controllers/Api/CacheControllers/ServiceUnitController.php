<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceUnitDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ServiceUnit\CreateServiceUnitRequest;
use App\Http\Requests\ServiceUnit\UpdateServiceUnitRequest;
use App\Models\HIS\ServiceUnit;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceUnitService;
use Illuminate\Http\Request;


class ServiceUnitController extends BaseApiCacheController
{
    protected $serviceUnitService;
    protected $serviceUnitDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceUnitService $serviceUnitService, ServiceUnit $serviceUnit)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceUnitService = $serviceUnitService;
        $this->serviceUnit = $serviceUnit;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'convert_service_unit_code',
                'convert_service_unit_name'
            ];
            $columns = $this->getColumnsTable($this->serviceUnit);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceUnitDTO = new ServiceUnitDTO(
            $this->serviceUnitName,
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
        $this->serviceUnitService->withParams($this->serviceUnitDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceUnitName);
            } else {
                $data = $this->serviceUnitService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceUnitName);
            } else {
                $data = $this->serviceUnitService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->serviceUnit, $this->serviceUnitName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceUnitName, $id);
        } else {
            $data = $this->serviceUnitService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServiceUnitRequest $request)
    {
        return $this->serviceUnitService->createServiceUnit($request);
    }
    public function update(UpdateServiceUnitRequest $request, $id)
    {
        return $this->serviceUnitService->updateServiceUnit($id, $request);
    }
    public function destroy($id)
    {
        return $this->serviceUnitService->deleteServiceUnit($id);
    }
}
