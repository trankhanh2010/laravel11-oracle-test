<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PatientTypeAllowDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PatientTypeAllow\CreatePatientTypeAllowRequest;
use App\Http\Requests\PatientTypeAllow\UpdatePatientTypeAllowRequest;
use App\Models\HIS\PatientTypeAllow;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PatientTypeAllowService;
use Illuminate\Http\Request;


class PatientTypeAllowController extends BaseApiCacheController
{
    protected $patientTypeAllowService;
    protected $patientTypeAllowDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PatientTypeAllowService $patientTypeAllowService, PatientTypeAllow $patientTypeAllow)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->patientTypeAllowService = $patientTypeAllowService;
        $this->patientTypeAllow = $patientTypeAllow;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'patient_type_code',
                'patient_type_name',
                'patient_type_allow_code',
                'patient_type_allow_name'
            ];
            $columns = $this->getColumnsTable($this->patientTypeAllow);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->patientTypeAllowDTO = new PatientTypeAllowDTO(
            $this->patientTypeAllowName,
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
            $this->patientTypeId,
            $this->tab,
        );
        $this->patientTypeAllowService->withParams($this->patientTypeAllowDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->patientTypeAllowName);
            } else {
                $data = $this->patientTypeAllowService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->patientTypeAllowName);
            } else {
                $data = $this->patientTypeAllowService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->patientTypeAllow, $this->patientTypeAllowName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->patientTypeAllowName, $id);
        } else {
            $data = $this->patientTypeAllowService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePatientTypeAllowRequest $request)
    {
        return $this->patientTypeAllowService->createPatientTypeAllow($request);
    }
    public function update(UpdatePatientTypeAllowRequest $request, $id)
    {
        return $this->patientTypeAllowService->updatePatientTypeAllow($id, $request);
    }
    public function destroy($id)
    {
        return $this->patientTypeAllowService->deletePatientTypeAllow($id);
    }
}
