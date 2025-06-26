<?php

namespace App\Services\Model;

use App\DTOs\AcinInteractiveDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AcinInteractive\InsertAcinInteractiveIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AcinInteractiveRepository;
use Illuminate\Support\Facades\Redis;

class AcinInteractiveService
{
    protected $acinInteractiveRepository;
    protected $params;
    public function __construct(AcinInteractiveRepository $acinInteractiveRepository)
    {
        $this->acinInteractiveRepository = $acinInteractiveRepository;
    }
    public function withParams(AcinInteractiveDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->acinInteractiveRepository->applyJoins();
            $data = $this->acinInteractiveRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->acinInteractiveRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->acinInteractiveRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->acinInteractiveRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            $data = $this->acinInteractiveRepository->applyGroupByField($data, $this->params->groupBy);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['acin_interactive'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->acinInteractiveRepository->applyJoins();
        $data = $this->acinInteractiveRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->acinInteractiveRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->acinInteractiveRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        $data = $this->acinInteractiveRepository->applyGroupByField($data, $this->params->groupBy);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->acinInteractiveRepository->applyJoins()
            ->where('his_acin_interactive.id', $id);
        $data = $this->acinInteractiveRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->acinInteractiveName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->acinInteractiveName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['acin_interactive'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->acinInteractiveName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->acinInteractiveName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['acin_interactive'], $e);
        }
    }
}
