<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TransactionTTDetailVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\TransactionTTDetailVView;
use App\Services\Model\TransactionTTDetailVViewService;
use Illuminate\Http\Request;


class TransactionTTDetailVViewController extends BaseApiCacheController
{
    protected $transactionTTDetailVViewService;
    protected $transactionTTDetailVViewDTO;
    public function __construct(Request $request, TransactionTTDetailVViewService $transactionTTDetailVViewService, TransactionTTDetailVView $transactionTTDetailVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
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
            $this->groupBy,
        );
        $this->transactionTTDetailVViewService->withParams($this->transactionTTDetailVViewDTO);
    }
    public function index()
    {
        if(($this->billId == null) && ($this->billCode == null)){
            $this->errors[$this->billIdName] = "Thiếu BillId";
            $this->errors[$this->billCodeName] = "Thiếu BillCode";
        }
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
