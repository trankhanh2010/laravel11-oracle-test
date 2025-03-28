<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MestPatientTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MestPatientType\CreateMestPatientTypeRequest;
use App\Http\Requests\MestPatientType\UpdateMestPatientTypeRequest;
use App\Models\HIS\MestPatientType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MestPatientTypeService;
use Illuminate\Http\Request;


class MestPatientTypeController extends BaseApiCacheController
{
    protected $mestPatientTypeService;
    protected $mestPatientTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MestPatientTypeService $mestPatientTypeService, MestPatientType $mestPatientType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->mestPatientTypeService = $mestPatientTypeService;
        $this->mestPatientType = $mestPatientType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'medi_stock_code',
                'medi_stock_name',
                'patient_type_code',
                'patient_type_name',
            ];
            $columns = $this->getColumnsTable($this->mestPatientType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->mestPatientTypeDTO = new MestPatientTypeDTO(
            $this->mestPatientTypeName,
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
            $this->mediStockId,
            $this->patientTypeId,
            $this->param,
        );
        $this->mestPatientTypeService->withParams($this->mestPatientTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->mestPatientTypeName);
            } else {
                $data = $this->mestPatientTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->mestPatientTypeName);
            } else {
                $data = $this->mestPatientTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->mestPatientType, $this->mestPatientTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->mestPatientTypeName, $id);
        } else {
            $data = $this->mestPatientTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMestPatientTypeRequest $request)
    {
        return $this->mestPatientTypeService->createMestPatientType($request);
    }
}
