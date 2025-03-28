<?php

namespace App\Services\Model;

use App\DTOs\HtuDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Htu\InsertHtuIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\HtuRepository;
use Illuminate\Support\Facades\Redis;

class HtuService 
{
    protected $htuRepository;
    protected $params;
    public function __construct(HtuRepository $htuRepository)
    {
        $this->htuRepository = $htuRepository;
    }
    public function withParams(HtuDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->htuRepository->applyJoins();
            $data = $this->htuRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->htuRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->htuRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->htuRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['htu'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->htuName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->htuName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->htuRepository->applyJoins();
                $data = $this->htuRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->htuRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->htuRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['htu'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->htuName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->htuRepository->applyJoins()
                    ->where('his_htu.id', $id);
                $data = $this->htuRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['htu'], $e);
        }
    }

    public function createHtu($request)
    {
        try {
            $data = $this->htuRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertHtuIndex($data, $this->params->htuName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->htuName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['htu'], $e);
        }
    }

    public function updateHtu($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->htuRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->htuRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertHtuIndex($data, $this->params->htuName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->htuName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['htu'], $e);
        }
    }

    public function deleteHtu($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->htuRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->htuRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->htuName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->htuName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['htu'], $e);
        }
    }
}
