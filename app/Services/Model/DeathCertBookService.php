<?php

namespace App\Services\Model;

use App\DTOs\DeathCertBookDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DeathCertBook\InsertDeathCertBookIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DeathCertBookRepository;
use Illuminate\Support\Facades\Redis;

class DeathCertBookService
{
    protected $deathCertBookRepository;
    protected $params;
    public function __construct(DeathCertBookRepository $deathCertBookRepository)
    {
        $this->deathCertBookRepository = $deathCertBookRepository;
    }
    public function withParams(DeathCertBookDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->deathCertBookRepository->applyJoins();
            $data = $this->deathCertBookRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->deathCertBookRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->deathCertBookRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->deathCertBookRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_cert_book'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->deathCertBookRepository->applyJoins();
        $data = $this->deathCertBookRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->deathCertBookRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->deathCertBookRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->deathCertBookRepository->applyJoins()
            ->where('his_death_cert_book.id', $id);
        $data = $this->deathCertBookRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->deathCertBookName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->deathCertBookName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_cert_book'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->deathCertBookName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->deathCertBookName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_cert_book'], $e);
        }
    }
}
