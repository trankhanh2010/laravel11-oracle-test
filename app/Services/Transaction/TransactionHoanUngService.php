<?php

namespace App\Services\Transaction;

use App\DTOs\TransactionHoanUngDTO;
use App\Repositories\TransactionRepository;

class TransactionHoanUngService 
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
    public function withParams(TransactionHoanUngDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function createTransactionHoanUng($request)
    {
        try {
            $data = $this->transactionRepository->createTransactionHoanUng($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);          
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_db'], $e);
        }
    }
}
