<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionThanhToanDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\CreateTransactionThanhToanRequest;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Transaction\TransactionThanhToanService;
use Illuminate\Http\Request;


class TransactionThanhToanController extends BaseApiCacheController
{
    protected $transactionThanhToanService;
    protected $transactionThanhToanDTO;
    public function __construct(Request $request, TransactionThanhToanService $transactionThanhToanService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionThanhToanService = $transactionThanhToanService;
        // Thêm tham số vào service
        $this->transactionThanhToanDTO = new TransactionThanhToanDTO(
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
        $this->transactionThanhToanService->withParams($this->transactionThanhToanDTO);
    }

    public function store(CreateTransactionThanhToanRequest $request)
    {
        return $this->transactionThanhToanService->createTransactionThanhToan($request);
    }

}
