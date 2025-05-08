<?php

namespace App\Services\Transaction;

use App\DTOs\TransactionTamThuDichVuDTO;
use App\Repositories\TransactionRepository;

class TransactionTamThuDichVuService 
{
    protected $transactionRepository;
    protected $treatmentMomoPaymentsRepository;
    protected $params;
    public function __construct(
        TransactionRepository $transactionRepository,
    )
    {
        $this->transactionRepository = $transactionRepository;
    }
    public function withParams(TransactionTamThuDichVuDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function createTransactionTamThuDichVu($request)
    {
        try {
            $data = $this->transactionRepository->createTransactionTamThuDichVu($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);          
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_db'], $e);
        }
    }
}
