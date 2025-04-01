<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PatientTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PatientType\CreatePatientTypeRequest;
use App\Http\Requests\PatientType\UpdatePatientTypeRequest;
use App\Models\HIS\PatientType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PatientTypeService;
use Illuminate\Http\Request;


class PatientTypeController extends BaseApiCacheController
{
    protected $patientTypeService;
    protected $patientTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PatientTypeService $patientTypeService, PatientType $patientType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->patientTypeService = $patientTypeService;
        $this->patientType = $patientType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->patientType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->patientTypeDTO = new PatientTypeDTO(
            $this->patientTypeName,
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
        $this->patientTypeService->withParams($this->patientTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->patientTypeName);
            } else {
                $data = $this->patientTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->patientTypeName);
            } else {
                $data = $this->patientTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->patientType, $this->patientTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->patientTypeName, $id);
        } else {
            $data = $this->patientTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePatientTypeRequest $request)
    {
        return $this->patientTypeService->createPatientType($request);
    }
    public function update(UpdatePatientTypeRequest $request, $id)
    {
        return $this->patientTypeService->updatePatientType($id, $request);
    }
    public function destroy($id)
    {
        return $this->patientTypeService->deletePatientType($id);
    }
}
