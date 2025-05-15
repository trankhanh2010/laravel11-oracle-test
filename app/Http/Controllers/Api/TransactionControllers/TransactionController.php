<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\UpdateTransactionRequest;
use App\Services\Model\TransactionService;
use Illuminate\Http\Request;


class TransactionController extends BaseApiCacheController
{
    protected $transactionService;
    protected $transactionDTO;
    public function __construct(Request $request, TransactionService $transactionService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionService = $transactionService;
        // Thêm tham số vào service
        $this->transactionDTO = new TransactionDTO(
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
        $this->transactionService->withParams($this->transactionDTO);
    }

    public function update(UpdateTransactionRequest $request, $id)
    {
        return $this->transactionService->updateTransaction($id, $request);
    }

}
