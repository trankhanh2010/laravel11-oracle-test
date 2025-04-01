<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MedicineUseFormDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicineUseForm\CreateMedicineUseFormRequest;
use App\Http\Requests\MedicineUseForm\UpdateMedicineUseFormRequest;
use App\Models\HIS\MedicineUseForm;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicineUseFormService;
use Illuminate\Http\Request;


class MedicineUseFormController extends BaseApiCacheController
{
    protected $medicineUseFormService;
    protected $medicineUseFormDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicineUseFormService $medicineUseFormService, MedicineUseForm $medicineUseForm)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicineUseFormService = $medicineUseFormService;
        $this->medicineUseForm = $medicineUseForm;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->medicineUseForm);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicineUseFormDTO = new MedicineUseFormDTO(
            $this->medicineUseFormName,
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
        $this->medicineUseFormService->withParams($this->medicineUseFormDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->medicineUseFormName);
            } else {
                $data = $this->medicineUseFormService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->medicineUseFormName);
            } else {
                $data = $this->medicineUseFormService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->medicineUseForm, $this->medicineUseFormName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicineUseFormName, $id);
        } else {
            $data = $this->medicineUseFormService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMedicineUseFormRequest $request)
    {
        return $this->medicineUseFormService->createMedicineUseForm($request);
    }
    public function update(UpdateMedicineUseFormRequest $request, $id)
    {
        return $this->medicineUseFormService->updateMedicineUseForm($id, $request);
    }
    public function destroy($id)
    {
        return $this->medicineUseFormService->deleteMedicineUseForm($id);
    }
}
