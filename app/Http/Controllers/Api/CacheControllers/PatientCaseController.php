<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PatientCaseDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PatientCase\CreatePatientCaseRequest;
use App\Http\Requests\PatientCase\UpdatePatientCaseRequest;
use App\Models\HIS\PatientCase;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PatientCaseService;
use Illuminate\Http\Request;


class PatientCaseController extends BaseApiCacheController
{
    protected $patientCaseService;
    protected $patientCaseDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PatientCaseService $patientCaseService, PatientCase $patientCase)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->patientCaseService = $patientCaseService;
        $this->patientCase = $patientCase;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->patientCase);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->patientCaseDTO = new PatientCaseDTO(
            $this->patientCaseName,
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
        $this->patientCaseService->withParams($this->patientCaseDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->patientCaseName);
            } else {
                $data = $this->patientCaseService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->patientCaseName);
            } else {
                $data = $this->patientCaseService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->patientCase, $this->patientCaseName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->patientCaseName, $id);
        } else {
            $data = $this->patientCaseService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePatientCaseRequest $request)
    {
        return $this->patientCaseService->createPatientCase($request);
    }
    public function update(UpdatePatientCaseRequest $request, $id)
    {
        return $this->patientCaseService->updatePatientCase($id, $request);
    }
    public function destroy($id)
    {
        return $this->patientCaseService->deletePatientCase($id);
    }
}
