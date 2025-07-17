<?php

namespace App\Services\Model;

use App\DTOs\BloodAboDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BloodAbo\InsertBloodAboIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BloodAboRepository;
use Illuminate\Support\Facades\Redis;

class BloodAboService
{
    protected $bloodAboRepository;
    protected $params;
    public function __construct(BloodAboRepository $bloodAboRepository)
    {
        $this->bloodAboRepository = $bloodAboRepository;
    }
    public function withParams(BloodAboDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bloodAboRepository->applyJoins();
            $data = $this->bloodAboRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bloodAboRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bloodAboRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bloodAboRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_abo'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->bloodAboRepository->applyJoins();
        $data = $this->bloodAboRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->bloodAboRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->bloodAboRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->bloodAboRepository->applyJoins()
            ->where('his_blood_abo.id', $id);
        $data = $this->bloodAboRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->bloodAboName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bloodAboName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_abo'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->bloodAboName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->bloodAboName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_abo'], $e);
        }
    }
}
