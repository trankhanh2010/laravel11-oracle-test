<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionRestoreDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\UpdateRestoreTransactionRequest;
use App\Services\Transaction\TransactionRestoreService;
use Illuminate\Http\Request;


class TransactionRestoreController extends BaseApiCacheController
{
    protected $transactionRestoreService;
    protected $transactionRestoreDTO;
    public function __construct(Request $request, TransactionRestoreService $transactionRestoreService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionRestoreService = $transactionRestoreService;
        // Thêm tham số vào service
        $this->transactionRestoreDTO = new TransactionRestoreDTO(
            $this->transactionName,
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
        $this->transactionRestoreService->withParams($this->transactionRestoreDTO);
    }

    public function update(UpdateRestoreTransactionRequest $request, $id)
    {
        return $this->transactionRestoreService->restoreTransaction($id, $request);
    }

}
