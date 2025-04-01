<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PatientClassifyDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PatientClassify\CreatePatientClassifyRequest;
use App\Http\Requests\PatientClassify\UpdatePatientClassifyRequest;
use App\Models\HIS\PatientClassify;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PatientClassifyService;
use Illuminate\Http\Request;


class PatientClassifyController extends BaseApiCacheController
{
    protected $patientClassifyService;
    protected $patientClassifyDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PatientClassifyService $patientClassifyService, PatientClassify $patientClassify)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->patientClassifyService = $patientClassifyService;
        $this->patientClassify = $patientClassify;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'other_pay_source_name',
                'other_pay_source_code',
                'patient_type_name',
                'patient_type_code'
            ];
            $columns = $this->getColumnsTable($this->patientClassify);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->patientClassifyDTO = new PatientClassifyDTO(
            $this->patientClassifyName,
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
        $this->patientClassifyService->withParams($this->patientClassifyDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->patientClassifyName);
            } else {
                $data = $this->patientClassifyService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->patientClassifyName);
            } else {
                $data = $this->patientClassifyService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->patientClassify, $this->patientClassifyName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->patientClassifyName, $id);
        } else {
            $data = $this->patientClassifyService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePatientClassifyRequest $request)
    {
        return $this->patientClassifyService->createPatientClassify($request);
    }
    public function update(UpdatePatientClassifyRequest $request, $id)
    {
        return $this->patientClassifyService->updatePatientClassify($id, $request);
    }
    public function destroy($id)
    {
        return $this->patientClassifyService->deletePatientClassify($id);
    }
}
