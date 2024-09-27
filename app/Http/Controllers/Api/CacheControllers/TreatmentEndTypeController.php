<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TreatmentEndTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TreatmentEndType\CreateTreatmentEndTypeRequest;
use App\Http\Requests\TreatmentEndType\UpdateTreatmentEndTypeRequest;
use App\Models\HIS\TreatmentEndType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TreatmentEndTypeService;
use Illuminate\Http\Request;


class TreatmentEndTypeController extends BaseApiCacheController
{
    protected $treatmentEndTypeService;
    protected $treatmentEndTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TreatmentEndTypeService $treatmentEndTypeService, TreatmentEndType $treatmentEndType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->treatmentEndTypeService = $treatmentEndTypeService;
        $this->treatmentEndType = $treatmentEndType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->treatmentEndType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->treatmentEndTypeDTO = new TreatmentEndTypeDTO(
            $this->treatmentEndTypeName,
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
        $this->treatmentEndTypeService->withParams($this->treatmentEndTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->treatmentEndTypeName);
            } else {
                $data = $this->treatmentEndTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->treatmentEndTypeName);
            } else {
                $data = $this->treatmentEndTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->treatmentEndType, $this->treatmentEndTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->treatmentEndTypeName, $id);
        } else {
            $data = $this->treatmentEndTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateTreatmentEndTypeRequest $request)
    {
        return $this->treatmentEndTypeService->createTreatmentEndType($request);
    }
    public function update(UpdateTreatmentEndTypeRequest $request, $id)
    {
        return $this->treatmentEndTypeService->updateTreatmentEndType($id, $request);
    }
    public function destroy($id)
    {
        return $this->treatmentEndTypeService->deleteTreatmentEndType($id);
    }
}
