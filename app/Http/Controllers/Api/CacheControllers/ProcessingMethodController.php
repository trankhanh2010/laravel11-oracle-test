<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\ProcessingMethodDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\ProcessingMethod\CreateProcessingMethodRequest;
use App\Http\Requests\ProcessingMethod\UpdateProcessingMethodRequest;
use App\Models\HIS\ProcessingMethod;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\ProcessingMethodService;
use Illuminate\Http\Request;


class ProcessingMethodController extends BaseApiCacheController
{
    protected $processingMethodService;
    protected $processingMethodDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, ProcessingMethodService $processingMethodService, ProcessingMethod $processingMethod)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->processingMethodService = $processingMethodService;
        $this->processingMethod = $processingMethod;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->processingMethod);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->processingMethodDTO = new ProcessingMethodDTO(
            $this->processingMethodName,
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
        $this->processingMethodService->withParams($this->processingMethodDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->processingMethodName);
            } else {
                $data = $this->processingMethodService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->processingMethodName);
            } else {
                $data = $this->processingMethodService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->processingMethod, $this->processingMethodName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->processingMethodName, $id);
        } else {
            $data = $this->processingMethodService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function store(CreateProcessingMethodRequest $request)
    {
        return $this->processingMethodService->createProcessingMethod($request);
    }
    public function update(UpdateProcessingMethodRequest $request, $id)
    {
        return $this->processingMethodService->updateProcessingMethod($id, $request);
    }
    public function destroy($id)
    {
        return $this->processingMethodService->deleteProcessingMethod($id);
    }
}
