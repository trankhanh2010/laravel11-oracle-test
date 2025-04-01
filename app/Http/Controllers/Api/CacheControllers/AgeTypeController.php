<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\AgeTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\AgeType\CreateAgeTypeRequest;
use App\Http\Requests\AgeType\UpdateAgeTypeRequest;
use App\Models\HIS\AgeType;
use Illuminate\Http\Request;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\AgeTypeService;
class AgeTypeController extends BaseApiCacheController
{
    protected $ageTypeService;
    protected $ageTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, AgeTypeService $ageTypeService, AgeType $ageType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ageTypeService = $ageTypeService;
        $this->ageType = $ageType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->ageType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ageTypeDTO = new AgeTypeDTO(
            $this->ageTypeName,
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
        $this->ageTypeService->withParams($this->ageTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ageTypeName);
            } else {
                $data = $this->ageTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->ageTypeName);
            } else {
                $data = $this->ageTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ageType, $this->ageTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ageTypeName, $id);
        } else {
            $data = $this->ageTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateAgeTypeRequest $request)
    {
        return $this->ageTypeService->createAgeType($request);
    }
    public function update(UpdateAgeTypeRequest $request, $id)
    {
        return $this->ageTypeService->updateAgeType($id, $request);
    }
    public function destroy($id)
    {
        return $this->ageTypeService->deleteAgeType($id);
    }
}
