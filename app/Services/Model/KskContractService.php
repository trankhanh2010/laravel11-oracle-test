<?php

namespace App\Services\Model;

use App\DTOs\KskContractDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\KskContract\InsertKskContractIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\KskContractRepository;
use Illuminate\Support\Facades\Redis;

class KskContractService
{
    protected $kskContractRepository;
    protected $params;
    public function __construct(KskContractRepository $kskContractRepository)
    {
        $this->kskContractRepository = $kskContractRepository;
    }
    public function withParams(KskContractDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->kskContractRepository->applyJoins();
            $data = $this->kskContractRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->kskContractRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->kskContractRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->kskContractRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ksk_contract'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->kskContractRepository->applyJoins();
        $data = $this->kskContractRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->kskContractRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->kskContractRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->kskContractRepository->applyJoins()
            ->where('his_ksk_contract.id', $id);
        $data = $this->kskContractRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->kskContractName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->kskContractName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ksk_contract'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->kskContractName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->kskContractName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['ksk_contract'], $e);
        }
    }

}
