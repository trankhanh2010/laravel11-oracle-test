<?php

namespace App\Http\Controllers\Api\CacheControllers;

use App\DTOs\TransactionTypeDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TransactionType\CreateTransactionTypeRequest;
use App\Http\Requests\TransactionType\UpdateTransactionTypeRequest;
use App\Models\HIS\TransactionType;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TransactionTypeService;
use Illuminate\Http\Request;


class TransactionTypeController extends BaseApiCacheController
{
    protected $transactionTypeService;
    protected $transactionTypeDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TransactionTypeService $transactionTypeService, TransactionType $transactionType)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->transactionTypeService = $transactionTypeService;
        $this->transactionType = $transactionType;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->transactionType);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->transactionTypeDTO = new TransactionTypeDTO(
            $this->transactionTypeName,
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
        $this->transactionTypeService->withParams($this->transactionTypeDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $keyword = $this->keyword;
        if (($keyword != null || $this->elasticSearchType != null) && !$this->cache) {
            if ($this->elasticSearchType != null) {
                $data = $this->elasticSearchService->handleElasticSearchSearch($this->transactionTypeName);
            } else {
                $data = $this->transactionTypeService->handleDataBaseSearch();
            }
        } else {
            if ($this->elastic) {
                $data = $this->elasticSearchService->handleElasticSearchGetAll($this->transactionTypeName);
            } else {
                $data = $this->transactionTypeService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->transactionType, $this->transactionTypeName);
            if ($validationError) {
                return $validationError;
            }
        }
        if ($this->elastic) {
            $data = $this->elasticSearchService->handleElasticSearchGetWithId($this->transactionTypeName, $id);
        } else {
            $data = $this->transactionTypeService->handleDataBaseGetWithId($id);
        }
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    // public function store(CreateTransactionTypeRequest $request)
    // {
    //     return $this->transactionTypeService->createTransactionType($request);
    // }
    // public function update(UpdateTransactionTypeRequest $request, $id)
    // {
    //     return $this->transactionTypeService->updateTransactionType($id, $request);
    // }
    public function destroy($id)
    {
        return $this->transactionTypeService->deleteTransactionType($id);
    }
}
