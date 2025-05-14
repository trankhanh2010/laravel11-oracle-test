<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TransactionListVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\UpdateCancelTransactionRequest;
use App\Models\View\TransactionListVView;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Model\TransactionListVViewService;
use Illuminate\Http\Request;


class TransactionListVViewController extends BaseApiCacheController
{
    protected $transactionListVViewService;
    protected $transactionListVViewDTO;
    public function __construct(Request $request, ElasticsearchService $elasticSearchService, TransactionListVViewService $transactionListVViewService, TransactionListVView $transactionListVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->elasticSearchService = $elasticSearchService;
        $this->transactionListVViewService = $transactionListVViewService;
        $this->transactionListVView = $transactionListVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [];
            $columns = $this->getColumnsTable($this->transactionListVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->transactionListVViewDTO = new TransactionListVViewDTO(
            $this->transactionListVViewName,
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
            $this->fromTime,
            $this->toTime,
            $this->transactionTypeIds,
            $this->lastId,
            $this->cursorPaginate,
            $this->treatmentCode,
            $this->transactionCode,
            $this->param,
            $this->noCache,
            $this->transReqCode,
            $this->accountBookCode,
        );
        $this->transactionListVViewService->withParams($this->transactionListVViewDTO);
    }
    public function index()
    {
        // Kiểm tra khoảng cách ngày
        // if (($this->fromTime !== null) && ($this->toTime !== null)) {
        //     if (($this->toTime - $this->fromTime) > 60235959) {
        //         $this->errors[$this->fromTimeName] = 'Khoảng thời gian vượt quá 60 ngày!';
        //         $this->fromTime = null;
        //     }
        // }
        if (($this->fromTime == null) && ($this->toTime == null) && ($this->treatmentCode == null) && ($this->transactionCode == null) && ($this->accountBookCode == null) && ($this->transReqCode == null)) {
            $this->errors[$this->fromTimeName] = 'Thiếu thời gian!';
            $this->errors[$this->toTimeName] = 'Thiếu thời gian!';
            $this->errors[$this->treatmentCodeName] = 'Thiếu mã điều trị!';
            $this->errors[$this->transactionCodeName] = 'Thiếu mã giao dịch!';
            $this->errors[$this->accountBookCodeName] = 'Thiếu mã sổ thu chi!';
            $this->errors[$this->transReqCodeName] = 'Thiếu mã giao dịch QR!';

        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->transactionListVViewService->handleDataBaseGetAll();
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
            $validationError = $this->validateAndCheckId($id, $this->transactionListVView, $this->transactionListVViewName);
            if ($validationError) {
                return $validationError;
            }
        }
        $data = $this->transactionListVViewService->handleDataBaseGetWithId($id);
        $paramReturn = [
            $this->idName => $id,
            $this->isActiveName => $this->isActive,
        ];
        return returnDataSuccess($paramReturn, $data);
    }
    public function cancelTransaction(UpdateCancelTransactionRequest $request, $id){
        return $this->transactionListVViewService->cancelTransaction($id, $request);
    }
}
