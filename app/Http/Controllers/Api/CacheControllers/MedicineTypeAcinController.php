<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MedicineTypeAcinDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicineTypeAcin\CreateMedicineTypeAcinRequest;
use App\Http\Requests\MedicineTypeAcin\UpdateMedicineTypeAcinRequest;
use App\Models\HIS\MedicineTypeAcin;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicineTypeAcinService;
use Illuminate\Http\Request;


class MedicineTypeAcinController extends BaseApiCacheController
{
    protected $medicineTypeAcinService;
    protected $medicineTypeAcinDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicineTypeAcinService $medicineTypeAcinService, MedicineTypeAcin $medicineTypeAcin)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicineTypeAcinService = $medicineTypeAcinService;
        $this->medicineTypeAcin = $medicineTypeAcin;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
                'medicine_type_code',
                'medicine_type_name',
                'active_ingredient_code',
                'active_ingredient_name',
            ];
            $columns = $this->getColumnsTable($this->medicineTypeAcin);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicineTypeAcinDTO = new MedicineTypeAcinDTO(
            $this->medicineTypeAcinName,
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
            $this->medicineTypeId,
            $this->activeIngredientId,
            $this->param,
        );
        $this->medicineTypeAcinService->withParams($this->medicineTypeAcinDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->medicineTypeAcinName);
            } else {
                $data = $this->medicineTypeAcinService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->medicineTypeAcinName);
            } else {
                $data = $this->medicineTypeAcinService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->medicineTypeAcin, $this->medicineTypeAcinName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicineTypeAcinName, $id);
        } else {
            $data = $this->medicineTypeAcinService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMedicineTypeAcinRequest $request)
    {
        return $this->medicineTypeAcinService->createMedicineTypeAcin($request);
    }
}
