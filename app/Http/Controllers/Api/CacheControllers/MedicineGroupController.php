<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\MedicineGroupDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\MedicineGroup\CreateMedicineGroupRequest;
use App\Http\Requests\MedicineGroup\UpdateMedicineGroupRequest;
use App\Models\HIS\MedicineGroup;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\MedicineGroupService;
use Illuminate\Http\Request;


class MedicineGroupController extends BaseApiCacheController
{
    protected $medicineGroupService;
    protected $medicineGroupDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, MedicineGroupService $medicineGroupService, MedicineGroup $medicineGroup)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->medicineGroupService = $medicineGroupService;
        $this->medicineGroup = $medicineGroup;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->medicineGroup);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->medicineGroupDTO = new MedicineGroupDTO(
            $this->medicineGroupName,
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
        $this->medicineGroupService->withParams($this->medicineGroupDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->medicineGroupName);
            } else {
                $data = $this->medicineGroupService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->medicineGroupName);
            } else {
                $data = $this->medicineGroupService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->medicineGroup, $this->medicineGroupName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->medicineGroupName, $id);
        } else {
            $data = $this->medicineGroupService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateMedicineGroupRequest $request)
    {
        return $this->medicineGroupService->createMedicineGroup($request);
    }
    public function update(UpdateMedicineGroupRequest $request, $id)
    {
        return $this->medicineGroupService->updateMedicineGroup($id, $request);
    }
    public function destroy($id)
    {
        return $this->medicineGroupService->deleteMedicineGroup($id);
    }
}
