<?php

namespace App\Services\Transaction;

use App\DTOs\TransactionRestoreDTO;
use App\Models\HIS\Transaction;
use App\Repositories\TransactionRepository;

class TransactionRestoreService
{
    protected $transaction;
    protected $transactionRepository;
    protected $params;
    public function __construct(
        Transaction $transaction,
        TransactionRepository $transactionRepository,
        )
    {
        $this->transaction = $transaction;
        $this->transactionRepository = $transactionRepository;
    }
    public function withParams(TransactionRestoreDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function restoreTransaction($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->transaction->find($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->transactionRepository->restoreTransaction($request, $data, $this->params->time, $this->params->appModifier);
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
        }
    }
}
