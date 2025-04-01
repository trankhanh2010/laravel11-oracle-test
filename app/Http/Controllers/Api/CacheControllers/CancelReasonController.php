<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\CancelReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\CancelReason\CreateCancelReasonRequest;
use App\Http\Requests\CancelReason\UpdateCancelReasonRequest;
use App\Models\HIS\CancelReason;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\CancelReasonService;
use Illuminate\Http\Request;


class CancelReasonController extends BaseApiCacheController
{
    protected $cancelReasonService;
    protected $cancelReasonDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, CancelReasonService $cancelReasonService, CancelReason $cancelReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->cancelReasonService = $cancelReasonService;
        $this->cancelReason = $cancelReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->cancelReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->cancelReasonDTO = new CancelReasonDTO(
            $this->cancelReasonName,
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
        $this->cancelReasonService->withParams($this->cancelReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->cancelReasonName);
            } else {
                $data = $this->cancelReasonService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->cancelReasonName);
            } else {
                $data = $this->cancelReasonService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->cancelReason, $this->cancelReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->cancelReasonName, $id);
        } else {
            $data = $this->cancelReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateCancelReasonRequest $request)
    {
        return $this->cancelReasonService->createCancelReason($request);
    }
    public function update(UpdateCancelReasonRequest $request, $id)
    {
        return $this->cancelReasonService->updateCancelReason($id, $request);
    }
    public function destroy($id)
    {
        return $this->cancelReasonService->deleteCancelReason($id);
    }
}
