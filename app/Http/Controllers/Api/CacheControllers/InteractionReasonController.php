<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\InteractionReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\InteractionReason\CreateInteractionReasonRequest;
use App\Http\Requests\InteractionReason\UpdateInteractionReasonRequest;
use App\Models\HIS\InteractionReason;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\InteractionReasonService;
use Illuminate\Http\Request;


class InteractionReasonController extends BaseApiCacheController
{
    protected $interactionReasonService;
    protected $interactionReasonDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, InteractionReasonService $interactionReasonService, InteractionReason $interactionReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->interactionReasonService = $interactionReasonService;
        $this->interactionReason = $interactionReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->interactionReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->interactionReasonDTO = new InteractionReasonDTO(
            $this->interactionReasonName,
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
        $this->interactionReasonService->withParams($this->interactionReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->interactionReasonName);
            } else {
                $data = $this->interactionReasonService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->interactionReasonName);
            } else {
                $data = $this->interactionReasonService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->interactionReason, $this->interactionReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->interactionReasonName, $id);
        } else {
            $data = $this->interactionReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateInteractionReasonRequest $request)
    {
        return $this->interactionReasonService->createInteractionReason($request);
    }
    public function update(UpdateInteractionReasonRequest $request, $id)
    {
        return $this->interactionReasonService->updateInteractionReason($id, $request);
    }
    public function destroy($id)
    {
        return $this->interactionReasonService->deleteInteractionReason($id);
    }
}
