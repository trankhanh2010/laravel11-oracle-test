<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\VaccineTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\VaccineType\CreateVaccineTypeRequest;
use App\Http\Requests\VaccineType\UpdateVaccineTypeRequest;
use App\Models\HIS\VaccineType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\VaccineTypeService;
use Illuminate\Http\Request;


class VaccineTypeController extends BaseApiCacheController
{
    protected $vaccineTypeService;
    protected $vaccineTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, VaccineTypeService $vaccineTypeService, VaccineType $vaccineType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->vaccineTypeService = $vaccineTypeService;
        $this->vaccineType = $vaccineType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->vaccineType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->vaccineTypeDTO = new VaccineTypeDTO(
            $this->vaccineTypeName,
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
        $this->vaccineTypeService->withParams($this->vaccineTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->vaccineTypeName);
            } else {
                $data = $this->vaccineTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->vaccineTypeName);
            } else {
                $data = $this->vaccineTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->vaccineType, $this->vaccineTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->vaccineTypeName, $id);
        } else {
            $data = $this->vaccineTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateVaccineTypeRequest $request)
    {
        return $this->vaccineTypeService->createVaccineType($request);
    }
    public function update(UpdateVaccineTypeRequest $request, $id)
    {
        return $this->vaccineTypeService->updateVaccineType($id, $request);
    }
    public function destroy($id)
    {
        return $this->vaccineTypeService->deleteVaccineType($id);
    }
}
