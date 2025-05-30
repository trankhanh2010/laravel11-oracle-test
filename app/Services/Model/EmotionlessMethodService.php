<?php

namespace App\Services\Model;

use App\DTOs\EmotionlessMethodDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\EmotionlessMethod\InsertEmotionlessMethodIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EmotionlessMethodRepository;
use Illuminate\Support\Facades\Redis;

class EmotionlessMethodService
{
    protected $emotionlessMethodRepository;
    protected $params;
    public function __construct(EmotionlessMethodRepository $emotionlessMethodRepository)
    {
        $this->emotionlessMethodRepository = $emotionlessMethodRepository;
    }
    public function withParams(EmotionlessMethodDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->emotionlessMethodRepository->applyJoins();
            $data = $this->emotionlessMethodRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->emotionlessMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->emotionlessMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->emotionlessMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->emotionlessMethodRepository->applyJoins();
        $data = $this->emotionlessMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->emotionlessMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->emotionlessMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->emotionlessMethodRepository->applyJoins()
            ->where('his_emotionless_method.id', $id);
        $data = $this->emotionlessMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->emotionlessMethodName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->emotionlessMethodName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->emotionlessMethodName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->emotionlessMethodName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }

    public function createEmotionlessMethod($request)
    {
        try {
            $data = $this->emotionlessMethodRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmotionlessMethodIndex($data, $this->params->emotionlessMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emotionlessMethodName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }

    public function updateEmotionlessMethod($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emotionlessMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emotionlessMethodRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertEmotionlessMethodIndex($data, $this->params->emotionlessMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emotionlessMethodName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }

    public function deleteEmotionlessMethod($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emotionlessMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emotionlessMethodRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->emotionlessMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emotionlessMethodName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }
}
