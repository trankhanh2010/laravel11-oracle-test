<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TransactionTTDetailVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\TransactionTTDetailVView\CreateTransactionTTDetailVViewRequest;
use App\Http\Requests\TransactionTTDetailVView\UpdateTransactionTTDetailVViewRequest;
use App\Models\View\TransactionTTDetailVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TransactionTTDetailVViewService;
use Illuminate\Http\Request;


class TransactionTTDetailVViewController extends BaseApiCacheController
{
    protected $transactionTTDetailVViewService;
    protected $transactionTTDetailVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TransactionTTDetailVViewService $transactionTTDetailVViewService, TransactionTTDetailVView $transactionTTDetailVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->transactionTTDetailVViewService = $transactionTTDetailVViewService;
        $this->transactionTTDetailVView = $transactionTTDetailVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->transactionTTDetailVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->transactionTTDetailVViewDTO = new TransactionTTDetailVViewDTO(
            $this->transactionTTDetailVViewName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
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
            $this->billId,
            $this->billCode,
            $this->param,
            $this->noCache,
        );
        $this->transactionTTDetailVViewService->withParams($this->transactionTTDetailVViewDTO);
    }
    public function index()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->transactionTTDetailVViewService->handleDataBaseGetAll();
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
}
