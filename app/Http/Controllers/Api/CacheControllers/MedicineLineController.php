<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MedicineLineDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicineLine\CreateMedicineLineRequest;
use App\Http\Requests\MedicineLine\UpdateMedicineLineRequest;
use App\Models\HIS\MedicineLine;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicineLineService;
use Illuminate\Http\Request;


class MedicineLineController extends BaseApiCacheController
{
    protected $medicineLineService;
    protected $medicineLineDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicineLineService $medicineLineService, MedicineLine $medicineLine)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicineLineService = $medicineLineService;
        $this->medicineLine = $medicineLine;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->medicineLine);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicineLineDTO = new MedicineLineDTO(
            $this->medicineLineName,
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
        $this->medicineLineService->withParams($this->medicineLineDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->medicineLineName);
            } else {
                $data = $this->medicineLineService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->medicineLineName);
            } else {
                $data = $this->medicineLineService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->medicineLine, $this->medicineLineName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicineLineName, $id);
        } else {
            $data = $this->medicineLineService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMedicineLineRequest $request)
    {
        return $this->medicineLineService->createMedicineLine($request);
    }
    public function update(UpdateMedicineLineRequest $request, $id)
    {
        return $this->medicineLineService->updateMedicineLine($id, $request);
    }
    public function destroy($id)
    {
        return $this->medicineLineService->deleteMedicineLine($id);
    }
}
