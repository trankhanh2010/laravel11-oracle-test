<?php

namespace App\Services\Model;

use App\DTOs\TranPatiReasonDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TranPatiReason\InsertTranPatiReasonIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TranPatiReasonRepository;
use Illuminate\Support\Facades\Redis;

class TranPatiReasonService
{
    protected $tranPatiReasonRepository;
    protected $params;
    public function __construct(TranPatiReasonRepository $tranPatiReasonRepository)
    {
        $this->tranPatiReasonRepository = $tranPatiReasonRepository;
    }
    public function withParams(TranPatiReasonDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->tranPatiReasonRepository->applyJoins();
            $data = $this->tranPatiReasonRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->tranPatiReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->tranPatiReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->tranPatiReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_reason'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->tranPatiReasonRepository->applyJoins();
        $data = $this->tranPatiReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->tranPatiReasonRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->tranPatiReasonRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->tranPatiReasonRepository->applyJoins()
            ->where('his_tran_pati_reason.id', $id);
        $data = $this->tranPatiReasonRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->tranPatiReasonName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->tranPatiReasonName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_reason'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->tranPatiReasonName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->tranPatiReasonName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_reason'], $e);
        }
    }
}
