<?php

namespace App\Services\Model;

use App\DTOs\CommuneDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Commune\InsertCommuneIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\CommuneRepository;
use Illuminate\Support\Facades\Redis;

class CommuneService 
{
    protected $communeRepository;
    protected $params;
    public function __construct(CommuneRepository $communeRepository)
    {
        $this->communeRepository = $communeRepository;
    }
    public function withParams(CommuneDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->communeRepository->applyJoins();
            $data = $this->communeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->communeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->communeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->communeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->communeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->communeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->communeRepository->applyJoins();
                $data = $this->communeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->communeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->communeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->communeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->communeRepository->applyJoins()
                    ->where('sda_commune.id', $id);
                $data = $this->communeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }

    public function createCommune($request)
    {
        try {
            $data = $this->communeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCommuneIndex($data, $this->params->communeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->communeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }

    public function updateCommune($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->communeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->communeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCommuneIndex($data, $this->params->communeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->communeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }

    public function deleteCommune($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->communeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->communeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->communeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->communeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }
}
