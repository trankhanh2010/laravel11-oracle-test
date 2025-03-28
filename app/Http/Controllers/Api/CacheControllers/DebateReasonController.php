<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\DebateReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\DebateReason\CreateDebateReasonRequest;
use App\Http\Requests\DebateReason\UpdateDebateReasonRequest;
use App\Models\HIS\DebateReason;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\DebateReasonService;
use Illuminate\Http\Request;


class DebateReasonController extends BaseApiCacheController
{
    protected $debateReasonService;
    protected $debateReasonDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, DebateReasonService $debateReasonService, DebateReason $debateReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->debateReasonService = $debateReasonService;
        $this->debateReason = $debateReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->debateReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->debateReasonDTO = new DebateReasonDTO(
            $this->debateReasonName,
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
        $this->debateReasonService->withParams($this->debateReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->debateReasonName);
            } else {
                $data = $this->debateReasonService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->debateReasonName);
            } else {
                $data = $this->debateReasonService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->debateReason, $this->debateReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->debateReasonName, $id);
        } else {
            $data = $this->debateReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateDebateReasonRequest $request)
    {
        return $this->debateReasonService->createDebateReason($request);
    }
    public function update(UpdateDebateReasonRequest $request, $id)
    {
        return $this->debateReasonService->updateDebateReason($id, $request);
    }
    public function destroy($id)
    {
        return $this->debateReasonService->deleteDebateReason($id);
    }
}
