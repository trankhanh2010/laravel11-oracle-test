<?php

namespace App\Services\Transaction;

use App\DTOs\TransactionTamUngDTO;
use App\Repositories\TransactionRepository;

class TransactionTamUngService 
{
    protected $transactionRepository;
    protected $params;
    public function __construct(TransactionRepository $transactionRepository)
    {
        $this->transactionRepository = $transactionRepository;
    }
    public function withParams(TransactionTamUngDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function createTransactionTamUng($request)
    {
        try {
            $data = $this->transactionRepository->createTransactionTamUng($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);          
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_db'], $e);
        }
    }
}
