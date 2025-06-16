<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MedicineTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicineType\CreateMedicineTypeRequest;
use App\Http\Requests\MedicineType\UpdateMedicineTypeRequest;
use App\Models\HIS\MedicineType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicineTypeService;
use Illuminate\Http\Request;


class MedicineTypeController extends BaseApiCacheController
{
    protected $medicineTypeService;
    protected $medicineTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicineTypeService $medicineTypeService, MedicineType $medicineType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicineTypeService = $medicineTypeService;
        $this->medicineType = $medicineType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->medicineType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicineTypeDTO = new MedicineTypeDTO(
            $this->medicineTypeName,
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
            $this->tab,
            $this->groupBy,
        );
        $this->medicineTypeService->withParams($this->medicineTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->medicineTypeName);
            } else {
                $data = $this->medicineTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->medicineTypeName);
            } else {
                $data = $this->medicineTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->medicineType, $this->medicineTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicineTypeName, $id);
        } else {
            $data = $this->medicineTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMedicineTypeRequest $request)
    {
        return $this->medicineTypeService->createMedicineType($request);
    }
    public function update(UpdateMedicineTypeRequest $request, $id)
    {
        return $this->medicineTypeService->updateMedicineType($id, $request);
    }
    public function destroy($id)
    {
        return $this->medicineTypeService->deleteMedicineType($id);
    }
}
