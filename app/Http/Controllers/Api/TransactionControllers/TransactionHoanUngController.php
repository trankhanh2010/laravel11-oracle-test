<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionHoanUngDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\CreateTransactionHoanUngRequest;
use App\Services\Transaction\TransactionHoanUngService;
use Illuminate\Http\Request;


class TransactionHoanUngController extends BaseApiCacheController
{
    protected $transactionHoanUngService;
    protected $transactionHoanUngDTO;
    public function __construct(Request $request, TransactionHoanUngService $transactionHoanUngService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionHoanUngService = $transactionHoanUngService;
        // Thêm tham số vào service
        $this->transactionHoanUngDTO = new TransactionHoanUngDTO(
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
        $this->transactionHoanUngService->withParams($this->transactionHoanUngDTO);
    }

    public function store(CreateTransactionHoanUngRequest $request)
    {
        return $this->transactionHoanUngService->createTransactionHoanUng($request);
    }

}
