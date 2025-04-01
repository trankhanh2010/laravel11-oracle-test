<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\PtttMethodDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\PtttMethod\CreatePtttMethodRequest;
use App\Http\Requests\PtttMethod\UpdatePtttMethodRequest;
use App\Models\HIS\PtttMethod;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\PtttMethodService;
use Illuminate\Http\Request;


class PtttMethodController extends BaseApiCacheController
{
    protected $ptttMethodService;
    protected $ptttMethodDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, PtttMethodService $ptttMethodService, PtttMethod $ptttMethod)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->ptttMethodService = $ptttMethodService;
        $this->ptttMethod = $ptttMethod;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->ptttMethod);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->ptttMethodDTO = new PtttMethodDTO(
            $this->ptttMethodName,
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
        $this->ptttMethodService->withParams($this->ptttMethodDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->ptttMethodName);
            } else {
                $data = $this->ptttMethodService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->ptttMethodName);
            } else {
                $data = $this->ptttMethodService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->ptttMethod, $this->ptttMethodName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->ptttMethodName, $id);
        } else {
            $data = $this->ptttMethodService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreatePtttMethodRequest $request)
    {
        return $this->ptttMethodService->createPtttMethod($request);
    }
    public function update(UpdatePtttMethodRequest $request, $id)
    {
        return $this->ptttMethodService->updatePtttMethod($id, $request);
    }
    public function destroy($id)
    {
        return $this->ptttMethodService->deletePtttMethod($id);
    }
}
