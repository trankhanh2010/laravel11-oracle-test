<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ExpMestReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ExpMestReason\CreateExpMestReasonRequest;
use App\Http\Requests\ExpMestReason\UpdateExpMestReasonRequest;
use App\Models\HIS\ExpMestReason;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ExpMestReasonService;
use Illuminate\Http\Request;


class ExpMestReasonController extends BaseApiCacheController
{
    protected $expMestReasonService;
    protected $expMestReasonDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ExpMestReasonService $expMestReasonService, ExpMestReason $expMestReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->expMestReasonService = $expMestReasonService;
        $this->expMestReason = $expMestReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->expMestReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->expMestReasonDTO = new ExpMestReasonDTO(
            $this->expMestReasonName,
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
        $this->expMestReasonService->withParams($this->expMestReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->expMestReasonName);
            } else {
                $data = $this->expMestReasonService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->expMestReasonName);
            } else {
                $data = $this->expMestReasonService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->expMestReason, $this->expMestReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->expMestReasonName, $id);
        } else {
            $data = $this->expMestReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateExpMestReasonRequest $request)
    {
        return $this->expMestReasonService->createExpMestReason($request);
    }
    public function update(UpdateExpMestReasonRequest $request, $id)
    {
        return $this->expMestReasonService->updateExpMestReason($id, $request);
    }
    public function destroy($id)
    {
        return $this->expMestReasonService->deleteExpMestReason($id);
    }
}
