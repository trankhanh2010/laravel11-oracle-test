<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\MedicineListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicineListVView\CreateMedicineListVViewRequest;
use App\Http\Requests\MedicineListVView\UpdateMedicineListVViewRequest;
use App\Models\View\MedicineListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicineListVViewService;
use Illuminate\Http\Request;


class MedicineListVViewController extends BaseApiCacheController
{
    protected $medicineListVViewService;
    protected $medicineListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicineListVViewService $medicineListVViewService, MedicineListVView $medicineListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicineListVViewService = $medicineListVViewService;
        $this->medicineListVView = $medicineListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->medicineListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicineListVViewDTO = new MedicineListVViewDTO(
            $this->medicineListVViewName,
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
    $this->param,
            $this->noCache,
            $this->tab,
            $this->treatmentCode,
            $this->patientCode,
            $this->intructionTimeFrom,
            $this->intructionTimeTo,
        );
        $this->medicineListVViewService->withParams($this->medicineListVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        if ($this->treatmentCode == null && $this->patientCode == null) {
            return returnDataSuccess(null, []);
        }
        if($this->keyword == null){
            $data = $this->medicineListVViewService->handleDataBaseGetAll();
        }else{
            $data = $this->medicineListVViewService->handleDataBaseSearch();
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
            $validationError = $this->validateAndCheckId($id, $this->medicineListVView, $this->medicineListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicineListVViewName, $id);
        } else {
            $data = $this->medicineListVViewService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
