<?php

namespace App\Services\Model;

use App\DTOs\AccountBookVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccountBookVView\InsertAccountBookVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AccountBookVViewRepository;

class AccountBookVViewService
{
    protected $accountBookVViewRepository;
    protected $params;
    public function __construct(AccountBookVViewRepository $accountBookVViewRepository)
    {
        $this->accountBookVViewRepository = $accountBookVViewRepository;
    }
    public function withParams(AccountBookVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->accountBookVViewRepository->applyJoins();
            $data = $this->accountBookVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->accountBookVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->accountBookVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->accountBookVViewRepository->applyIsForDepositFilter($data, $this->params->isForDeposit);
            $data = $this->accountBookVViewRepository->applyIsForRepayFilter($data, $this->params->isForRepay);
            $data = $this->accountBookVViewRepository->applyIsForBillFilter($data, $this->params->isForBill);
            $count = $data->count();
            $data = $this->accountBookVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->accountBookVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['account_book_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = $this->accountBookVViewRepository->applyJoins();
            $data = $this->accountBookVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->accountBookVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->accountBookVViewRepository->applyIsForDepositFilter($data, $this->params->isForDeposit);
            $data = $this->accountBookVViewRepository->applyIsForRepayFilter($data, $this->params->isForRepay);
            $data = $this->accountBookVViewRepository->applyIsForBillFilter($data, $this->params->isForBill);
            $count = $data->count();
            $data = $this->accountBookVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->accountBookVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['account_book_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->accountBookVViewRepository->applyJoins()
                ->where('v_his_account_book.id', $id);
            $data = $this->accountBookVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->accountBookVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['account_book_v_view'], $e);
        }
    }

    // public function createAccountBookVView($request)
    // {
    //     try {
    //         $data = $this->accountBookVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->accountBookVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertAccountBookVViewIndex($data, $this->params->accountBookVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['account_book_v_view'], $e);
    //     }
    // }

    // public function updateAccountBookVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->accountBookVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->accountBookVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->accountBookVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertAccountBookVViewIndex($data, $this->params->accountBookVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['account_book_v_view'], $e);
    //     }
    // }

    // public function deleteAccountBookVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->accountBookVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->accountBookVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->accountBookVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->accountBookVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['account_book_v_view'], $e);
    //     }
    // }
}
