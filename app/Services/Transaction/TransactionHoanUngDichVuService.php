<?php

namespace App\Services\Transaction;

use App\DTOs\TransactionHoanUngDichVuDTO;
use App\Repositories\TransactionRepository;

class TransactionHoanUngDichVuService 
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
    public function withParams(TransactionHoanUngDichVuDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function createTransactionHoanUngDichVu($request)
    {
        try {
            $data = $this->transactionRepository->createTransactionHoanUngDichVu($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);          
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_db'], $e);
        }
    }
}
