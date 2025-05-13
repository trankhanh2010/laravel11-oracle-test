<?php

namespace App\Http\Controllers\Api\TransactionControllers;

use App\DTOs\TransactionTamThuDichVuDTO;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use App\Http\Requests\Transaction\CreateTransactionTamThuDichVuRequest;
use App\Services\Transaction\TransactionTamThuDichVuService;
use Illuminate\Http\Request;


class TransactionTamThuDichVuController extends BaseApiCacheController
{
    protected $transactionTamThuDichVuService;
    protected $transactionTamThuDichVuDTO;
    public function __construct(Request $request, TransactionTamThuDichVuService $transactionTamThuDichVuService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->transactionTamThuDichVuService = $transactionTamThuDichVuService;
        // Thêm tham số vào service
        $this->transactionTamThuDichVuDTO = new TransactionTamThuDichVuDTO(
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
        $this->transactionTamThuDichVuService->withParams($this->transactionTamThuDichVuDTO);
    }

    public function store(CreateTransactionTamThuDichVuRequest $request)
    {
        return $this->transactionTamThuDichVuService->createTransactionTamThuDichVu($request);
    }

}
