<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\RepayReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\RepayReason\CreateRepayReasonRequest;
use App\Http\Requests\RepayReason\UpdateRepayReasonRequest;
use App\Models\HIS\RepayReason;
use App\Services\Model\RepayReasonService;
use Illuminate\Http\Request;


class RepayReasonController extends BaseApiCacheController
{
    protected $repayReasonService;
    protected $repayReasonDTO;
    public function __construct(Request $request, RepayReasonService $repayReasonService, RepayReason $repayReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->repayReasonService = $repayReasonService;
        $this->repayReason = $repayReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->repayReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->repayReasonDTO = new RepayReasonDTO(
            $this->repayReasonName,
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
        $this->repayReasonService->withParams($this->repayReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if ($keyword == null) {
            $data = $this->repayReasonService->handleDataBaseGetAll();
        } else {
            $data = $this->repayReasonService->handleDataBaseSearch();
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
            $validationError = $this->validateAndCheckId($id, $this->repayReason, $this->repayReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->repayReasonName, $id);
        } else {
            $data = $this->repayReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    // public function store(CreateRepayReasonRequest $request)
    // {
    //     return $this->repayReasonService->createRepayReason($request);
    // }
    // public function update(UpdateRepayReasonRequest $request, $id)
    // {
    //     return $this->repayReasonService->updateRepayReason($id, $request);
    // }
    // public function destroy($id)
    // {
    //     return $this->repayReasonService->deleteRepayReason($id);
    // }
}
