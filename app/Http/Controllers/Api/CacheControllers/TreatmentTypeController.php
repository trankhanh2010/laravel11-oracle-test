<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TreatmentTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentType\CreateTreatmentTypeRequest;
use App\Http\Requests\TreatmentType\UpdateTreatmentTypeRequest;
use App\Models\HIS\TreatmentType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentTypeService;
use Illuminate\Http\Request;


class TreatmentTypeController extends BaseApiCacheController
{
    protected $treatmentTypeService;
    protected $treatmentTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentTypeService $treatmentTypeService, TreatmentType $treatmentType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentTypeService = $treatmentTypeService;
        $this->treatmentType = $treatmentType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentTypeDTO = new TreatmentTypeDTO(
            $this->treatmentTypeName,
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
        $this->treatmentTypeService->withParams($this->treatmentTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->treatmentTypeName);
            } else {
                $data = $this->treatmentTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->treatmentTypeName);
            } else {
                $data = $this->treatmentTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentType, $this->treatmentTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentTypeName, $id);
        } else {
            $data = $this->treatmentTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateTreatmentTypeRequest $request)
    {
        return $this->treatmentTypeService->createTreatmentType($request);
    }
    public function update(UpdateTreatmentTypeRequest $request, $id)
    {
        return $this->treatmentTypeService->updateTreatmentType($id, $request);
    }
    public function destroy($id)
    {
        return $this->treatmentTypeService->deleteTreatmentType($id);
    }
}
