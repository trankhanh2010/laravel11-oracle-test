<?php

namespace App\Http\Controllers\Api\NoCacheControllers;

use App\DTOs\TransactionTUDetailVViewDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Models\View\TransactionTUDetailVView;
use App\Services\Model\TransactionTUDetailVViewService;
use Illuminate\Http\Request;


class TransactionTUDetailVViewController extends BaseApiCacheController
{
    protected $transactionTUDetailVViewService;
    protected $transactionTUDetailVViewDTO;
    public function __construct(Request $request, TransactionTUDetailVViewService $transactionTUDetailVViewService, TransactionTUDetailVView $transactionTUDetailVView)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionTUDetailVViewService = $transactionTUDetailVViewService;
        $this->transactionTUDetailVView = $transactionTUDetailVView;
        // Kiểm tra tên trường trong bảng
        if ($this->orderBy != null) {
            $this->orderByJoin = [
            ];
            $columns = $this->getColumnsTable($this->transactionTUDetailVView, true);
            $this->orderBy = $this->checkOrderBy($this->orderBy, $columns, $this->orderByJoin ?? []);
        }
        // Thêm tham số vào service
        $this->transactionTUDetailVViewDTO = new TransactionTUDetailVViewDTO(
            $this->transactionTUDetailVViewName,
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
            $this->depositId,
            $this->depositCode,
            $this->param,
            $this->noCache,
            $this->groupBy,
        );
        $this->transactionTUDetailVViewService->withParams($this->transactionTUDetailVViewDTO);
    }
    public function index()
    {
        if(($this->depositId == null) && ($this->depositCode == null)){
            $this->errors[$this->depositIdName] = "Thiếu BillId";
            $this->errors[$this->depositCodeName] = "Thiếu BillCode";
        }
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->transactionTUDetailVViewService->handleDataBaseGetAll();
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
