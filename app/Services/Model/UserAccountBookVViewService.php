<?php

namespace App\Services\Model;

use App\DTOs\UserAccountBookVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\UserAccountBookVView\InsertUserAccountBookVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\UserAccountBookVViewRepository;

class UserAccountBookVViewService
{
    protected $userAccountBookVViewRepository;
    protected $params;
    public function __construct(UserAccountBookVViewRepository $userAccountBookVViewRepository)
    {
        $this->userAccountBookVViewRepository = $userAccountBookVViewRepository;
    }
    public function withParams(UserAccountBookVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->userAccountBookVViewRepository->applyJoins();
            $data = $this->userAccountBookVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->userAccountBookVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->userAccountBookVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $this->userAccountBookVViewRepository->applyIsForDepositFilter($data, $this->params->isForDeposit);
            $data = $this->userAccountBookVViewRepository->applyIsForRepayFilter($data, $this->params->isForRepay);
            $data = $this->userAccountBookVViewRepository->applyIsForBillFilter($data, $this->params->isForBill);
            $data = $this->userAccountBookVViewRepository->applyLoginnameFilter($data, $this->params->currentLoginname);
            $data = $this->userAccountBookVViewRepository->applyTabFilter($data, $this->params->tab);

            $count = $data->count();
            $data = $this->userAccountBookVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->userAccountBookVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_account_book_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->userAccountBookVViewRepository->applyJoins();
        $data = $this->userAccountBookVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->userAccountBookVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $this->userAccountBookVViewRepository->applyIsForDepositFilter($data, $this->params->isForDeposit);
        $data = $this->userAccountBookVViewRepository->applyIsForRepayFilter($data, $this->params->isForRepay);
        $data = $this->userAccountBookVViewRepository->applyIsForBillFilter($data, $this->params->isForBill);
        $data = $this->userAccountBookVViewRepository->applyLoginnameFilter($data, $this->params->currentLoginname);
        $data = $this->userAccountBookVViewRepository->applyTabFilter($data, $this->params->tab);
        $count = $data->count();
        $data = $this->userAccountBookVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->userAccountBookVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->userAccountBookVViewRepository->applyJoins()
        ->where('v_his_account_book.id', $id);
    $data = $this->userAccountBookVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
    $data = $this->userAccountBookVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
    $data = $data->first();
    return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_account_book_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['user_account_book_v_view'], $e);
        }
    }

    // public function createUserAccountBookVView($request)
    // {
    //     try {
    //         $data = $this->userAccountBookVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->userAccountBookVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertUserAccountBookVViewIndex($data, $this->params->userAccountBookVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['user_account_book_v_view'], $e);
    //     }
    // }

    // public function updateUserAccountBookVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->userAccountBookVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->userAccountBookVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->userAccountBookVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertUserAccountBookVViewIndex($data, $this->params->userAccountBookVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['user_account_book_v_view'], $e);
    //     }
    // }

    // public function deleteUserAccountBookVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->userAccountBookVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->userAccountBookVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->userAccountBookVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->userAccountBookVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['user_account_book_v_view'], $e);
    //     }
    // }
}
