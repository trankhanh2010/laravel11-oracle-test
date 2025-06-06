<?php

namespace App\Services\Model;

use App\DTOs\NextTreaIntrDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\NextTreaIntr\InsertNextTreaIntrIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\NextTreaIntrRepository;
use Illuminate\Support\Facades\Redis;

class NextTreaIntrService
{
    protected $nextTreaIntrRepository;
    protected $params;
    public function __construct(NextTreaIntrRepository $nextTreaIntrRepository)
    {
        $this->nextTreaIntrRepository = $nextTreaIntrRepository;
    }
    public function withParams(NextTreaIntrDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->nextTreaIntrRepository->applyJoins();
            $data = $this->nextTreaIntrRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->nextTreaIntrRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->nextTreaIntrRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->nextTreaIntrRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['next_trea_intr'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->nextTreaIntrRepository->applyJoins();
        $data = $this->nextTreaIntrRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->nextTreaIntrRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->nextTreaIntrRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->nextTreaIntrRepository->applyJoins()
            ->where('his_next_trea_intr.id', $id);
        $data = $this->nextTreaIntrRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->nextTreaIntrName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->nextTreaIntrName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['next_trea_intr'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->nextTreaIntrName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->nextTreaIntrName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['next_trea_intr'], $e);
        }
    }

    // public function createNextTreaIntr($request)
    // {
    //     try {
    //         $data = $this->nextTreaIntrRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertNextTreaIntrIndex($data, $this->params->nextTreaIntrName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->nextTreaIntrName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['next_trea_intr'], $e);
    //     }
    // }

    // public function updateNextTreaIntr($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->nextTreaIntrRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->nextTreaIntrRepository->update($request, $data, $this->params->time, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertNextTreaIntrIndex($data, $this->params->nextTreaIntrName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->nextTreaIntrName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['next_trea_intr'], $e);
    //     }
    // }

    // public function deleteNextTreaIntr($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->nextTreaIntrRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->nextTreaIntrRepository->delete($data);

    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->nextTreaIntrName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->nextTreaIntrName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['next_trea_intr'], $e);
    //     }
    // }
}
