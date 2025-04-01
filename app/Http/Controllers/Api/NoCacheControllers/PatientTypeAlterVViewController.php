<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\PatientTypeAlterVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PatientTypeAlterVView\CreatePatientTypeAlterVViewRequest;
use App\Http\Requests\PatientTypeAlterVView\UpdatePatientTypeAlterVViewRequest;
use App\Models\View\PatientTypeAlterVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PatientTypeAlterVViewService;
use Illuminate\Http\Request;


class PatientTypeAlterVViewController extends BaseApiCacheController
{
    protected $patientTypeAlterVViewService;
    protected $patientTypeAlterVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PatientTypeAlterVViewService $patientTypeAlterVViewService, PatientTypeAlterVView $patientTypeAlterVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->patientTypeAlterVViewService = $patientTypeAlterVViewService;
        $this->patientTypeAlterVView = $patientTypeAlterVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->patientTypeAlterVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->patientTypeAlterVViewDTO = new PatientTypeAlterVViewDTO(
            $this->patientTypeAlterVViewName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->treatmentId,
            $this->logTimeTo,
            $this->param,
            $this->noCache,
        );
        $this->patientTypeAlterVViewService->withParams($this->patientTypeAlterVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->patientTypeAlterVViewName);
            } else {
                $data = $this->patientTypeAlterVViewService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->patientTypeAlterVViewName);
            } else {
                $data = $this->patientTypeAlterVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->patientTypeAlterVView, $this->patientTypeAlterVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->patientTypeAlterVViewName, $id);
        } else {
            $data = $this->patientTypeAlterVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
