<?php

namespace App\Services\Transaction;

use App\DTOs\TransactionTamUngDTO;
use App\Repositories\TransactionRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;

class TransactionTamUngService 
{
    protected $transactionRepository;
    protected $treatmentMomoPaymentsRepository;
    protected $params;
    public function __construct(
        TransactionRepository $transactionRepository,
        TreatmentMoMoPaymentsRepository $treatmentMomoPaymentsRepository,
    )
    {
        $this->transactionRepository = $transactionRepository;
        $this->treatmentMomoPaymentsRepository = $treatmentMomoPaymentsRepository;
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
            $this->treatmentMomoPaymentsRepository->setResultCode1005($data->tdl_treatment_code);
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction_db'], $e);
        }
    }
}
