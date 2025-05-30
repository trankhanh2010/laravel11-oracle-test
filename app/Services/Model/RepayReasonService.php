<?php

namespace App\Services\Model;

use App\DTOs\RepayReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\RepayReason\InsertRepayReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RepayReasonRepository;
use Illuminate\Support\Facades\Redis;

class RepayReasonService
{
    protected $repayReasonRepository;
    protected $params;
    public function __construct(RepayReasonRepository $repayReasonRepository)
    {
        $this->repayReasonRepository = $repayReasonRepository;
    }
    public function withParams(RepayReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->repayReasonRepository->applyJoins();
            $data = $this->repayReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->repayReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->repayReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->repayReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['repay_reason'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->repayReasonRepository->applyJoins();
        $data = $this->repayReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->repayReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->repayReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->repayReasonRepository->applyJoins()
            ->where('his_repay_reason.id', $id);
        $data = $this->repayReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->repayReasonName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->repayReasonName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['repay_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->repayReasonName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->repayReasonName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['repay_reason'], $e);
        }
    }

    // public function createRepayReason($request)
    // {
    //     try {
    //         $data = $this->repayReasonRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertRepayReasonIndex($data, $this->params->repayReasonName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->repayReasonName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['repay_reason'], $e);
    //     }
    // }

    // public function updateRepayReason($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->repayReasonRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->repayReasonRepository->update($request, $data, $this->params->time, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertRepayReasonIndex($data, $this->params->repayReasonName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->repayReasonName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['repay_reason'], $e);
    //     }
    // }

    // public function deleteRepayReason($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->repayReasonRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->repayReasonRepository->delete($data);

    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->repayReasonName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->repayReasonName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['repay_reason'], $e);
    //     }
    // }
}
