<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PtttConditionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PtttCondition\CreatePtttConditionRequest;
use App\Http\Requests\PtttCondition\UpdatePtttConditionRequest;
use App\Models\HIS\PtttCondition;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PtttConditionService;
use Illuminate\Http\Request;


class PtttConditionController extends BaseApiCacheController
{
    protected $ptttConditionService;
    protected $ptttConditionDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PtttConditionService $ptttConditionService, PtttCondition $ptttCondition)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ptttConditionService = $ptttConditionService;
        $this->ptttCondition = $ptttCondition;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->ptttCondition);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ptttConditionDTO = new PtttConditionDTO(
            $this->ptttConditionName,
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
        $this->ptttConditionService->withParams($this->ptttConditionDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttConditionName);
            } else {
                $data = $this->ptttConditionService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->ptttConditionName);
            } else {
                $data = $this->ptttConditionService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ptttCondition, $this->ptttConditionName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ptttConditionName, $id);
        } else {
            $data = $this->ptttConditionService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePtttConditionRequest $request)
    {
        return $this->ptttConditionService->createPtttCondition($request);
    }
    public function update(UpdatePtttConditionRequest $request, $id)
    {
        return $this->ptttConditionService->updatePtttCondition($id, $request);
    }
    public function destroy($id)
    {
        return $this->ptttConditionService->deletePtttCondition($id);
    }
}
