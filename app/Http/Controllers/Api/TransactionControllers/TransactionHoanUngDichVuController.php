<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionHoanUngDichVuDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\CreateTransactionHoanUngDichVuRequest;
use App\Services\Transaction\TransactionHoanUngDichVuService;
use Illuminate\Http\Request;


class TransactionHoanUngDichVuController extends BaseApiCacheController
{
    protected $transactionHoanUngDichVuService;
    protected $transactionHoanUngDichVuDTO;
    public function __construct(Request $request, TransactionHoanUngDichVuService $transactionHoanUngDichVuService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionHoanUngDichVuService = $transactionHoanUngDichVuService;
        // Thêm tham số vào service
        $this->transactionHoanUngDichVuDTO = new TransactionHoanUngDichVuDTO(
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
        $this->transactionHoanUngDichVuService->withParams($this->transactionHoanUngDichVuDTO);
    }

    public function store(CreateTransactionHoanUngDichVuRequest $request)
    {
        return $this->transactionHoanUngDichVuService->createTransactionHoanUngDichVu($request);
    }

}
