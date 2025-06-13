<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TranPatiReasonDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TranPatiReason\CreateTranPatiReasonRequest;
use App\Http\Requests\TranPatiReason\UpdateTranPatiReasonRequest;
use App\Models\HIS\TranPatiReason;
use App\Services\Model\TranPatiReasonService;
use Illuminate\Http\Request;


class TranPatiReasonController extends BaseApiCacheController
{
    protected $tranPatiReasonService;
    protected $tranPatiReasonDTO;
    public function __construct(Request $request, TranPatiReasonService $tranPatiReasonService, TranPatiReason $tranPatiReason)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->tranPatiReasonService = $tranPatiReasonService;
        $this->tranPatiReason = $tranPatiReason;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->tranPatiReason);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->tranPatiReasonDTO = new TranPatiReasonDTO(
            $this->tranPatiReasonName,
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
        $this->tranPatiReasonService->withParams($this->tranPatiReasonDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->tranPatiReasonName);
            } else {
                $data = $this->tranPatiReasonService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->tranPatiReasonName);
            } else {
                $data = $this->tranPatiReasonService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->tranPatiReason, $this->tranPatiReasonName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->tranPatiReasonName, $id);
        } else {
            $data = $this->tranPatiReasonService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
}
