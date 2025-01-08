<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionTamUngDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\CreateTransactionTamUngRequest;
use App\Services\Elastic\ElasticsearchService;
use App\Services\Transaction\TransactionTamUngService;
use Illuminate\Http\Request;


class TransactionTamUngController extends BaseApiCacheController
{
    protected $transactionTamUngService;
    protected $transactionTamUngDTO;
    public function __construct(Request $request, TransactionTamUngService $transactionTamUngService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionTamUngService = $transactionTamUngService;
        // Thêm tham số vào service
        $this->transactionTamUngDTO = new TransactionTamUngDTO(
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
        );
        $this->transactionTamUngService->withParams($this->transactionTamUngDTO);
    }

    public function store(CreateTransactionTamUngRequest $request)
    {
        return $this->transactionTamUngService->createTransactionTamUng($request);
    }

}
