<?php

namespace App\Services\Transaction;

use App\DTOs\TransactionThanhToanDTO;
use App\Repositories\TransactionRepository;

class TransactionThanhToanService 
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
    public function withParams(TransactionThanhToanDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function createTransactionThanhToan($request)
    {
        try {
            $data = $this->transactionRepository->createTransactionThanhToan($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);          
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_db'], $e);
        }
    }
}
