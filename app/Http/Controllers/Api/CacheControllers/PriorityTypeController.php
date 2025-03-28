<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PriorityTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PriorityType\CreatePriorityTypeRequest;
use App\Http\Requests\PriorityType\UpdatePriorityTypeRequest;
use App\Models\HIS\PriorityType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PriorityTypeService;
use Illuminate\Http\Request;


class PriorityTypeController extends BaseApiCacheController
{
    protected $priorityTypeService;
    protected $priorityTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PriorityTypeService $priorityTypeService, PriorityType $priorityType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->priorityTypeService = $priorityTypeService;
        $this->priorityType = $priorityType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->priorityType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->priorityTypeDTO = new PriorityTypeDTO(
            $this->priorityTypeName,
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
        $this->priorityTypeService->withParams($this->priorityTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->priorityTypeName);
            } else {
                $data = $this->priorityTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->priorityTypeName);
            } else {
                $data = $this->priorityTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->priorityType, $this->priorityTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->priorityTypeName, $id);
        } else {
            $data = $this->priorityTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePriorityTypeRequest $request)
    {
        return $this->priorityTypeService->createPriorityType($request);
    }
    public function update(UpdatePriorityTypeRequest $request, $id)
    {
        return $this->priorityTypeService->updatePriorityType($id, $request);
    }
    public function destroy($id)
    {
        return $this->priorityTypeService->deletePriorityType($id);
    }
}
