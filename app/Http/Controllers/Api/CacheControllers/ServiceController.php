<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ServiceDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Service\CreateServiceRequest;
use App\Http\Requests\Service\UpdateServiceRequest;
use App\Models\HIS\Service;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ServiceService;
use Illuminate\Http\Request;


class ServiceController extends BaseApiCacheController
{
    protected $serviceService;
    protected $serviceDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ServiceService $serviceService, Service $service)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->serviceService = $serviceService;
        $this->service = $service;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'service_type_code',
                'service_type_name',
                'parent_service_code',
                'parent_service_name',
                'service_unit_code',
                'service_unit_name',
                'hein_service_type_code',
                'hein_service_type_name',
                'bill_patient_type_code',
                'bill_patient_type_name',
                'pttt_group_code',
                'pttt_group_name',
                'pttt_method_code',
                'pttt_method_name',
                'icd_cm_code',
                'icd_cm_name',
                'revenue_department_code',
                'revenue_department_name',
                'package_code',
                'package_name',
                'exe_service_module_name',
                'gender_code',
                'gender_name',
                'ration_group_code',
                'ration_group_name',
                'diim_type_code',
                'diim_type_name',
                'fuex_type_code',
                'fuex_type_name',
                'test_type_code',
                'test_type_name',
                'other_pay_source_code',
                'other_pay_source_name',
                'film_size_code',
                'film_size_name',
                'default_patient_type_code',
                'default_patient_type_name',
            ];
            $columns = $this->getColumnsTable($this->service);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->serviceDTO = new ServiceDTO(
            $this->serviceName,
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
            $this->serviceTypeId,
            $this->param,
            $this->noCache,
            $this->groupBy,
            $this->tab,
            $this->serviceGroupIds,
        );
        $this->serviceService->withParams($this->serviceDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->serviceName);
            } else {
                $data = $this->serviceService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->serviceName);
            } else {
                $data = $this->serviceService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->service, $this->serviceName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->serviceName, $id);
        } else {
            $data = $this->serviceService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateServiceRequest $request)
    {
        return $this->serviceService->createService($request);
    }
    public function update(UpdateServiceRequest $request, $id)
    {
        return $this->serviceService->updateService($id, $request);
    }
    public function destroy($id)
    {
        return $this->serviceService->deleteService($id);
    }
}
