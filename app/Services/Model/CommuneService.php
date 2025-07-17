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
    private function getAllDataFromDatabase()
    {
        $data = $this->communeRepository->applyJoins();
        $data = $this->communeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->communeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->communeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseGetDataSelect()
    {
        $data = $this->communeRepository->applyJoinsGetDataSelect();
        $data = $this->communeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->communeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->communeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseGetDataSelect2Cap()
    {
        $data = $this->communeRepository->applyJoinsGetDataSelect2Cap();
        $data = $this->communeRepository->applyIsActiveFilter($data, 1);
        $data = $this->communeRepository->applyIsDeleteFilter($data, 0);
        $count = $data->count();
        $data = $this->communeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->communeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseGetDataSelectTHX()
    {
        $data = $this->communeRepository->applyJoinsGetDataSelectTHX();
        $data = $this->communeRepository->applyIsActiveFilter($data, 1);
        $data = $this->communeRepository->applyIsDeleteFilter($data, 0);
        $count = $data->count();
        $data = $this->communeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->communeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->communeRepository->applyJoins()
            ->where('sda_commune.id', $id);
        $data = $this->communeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->communeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->communeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });

                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }
    public function handleDataBaseGetAllGetDataSelect()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabaseGetDataSelect();
            } else {
                $cacheKey = $this->params->communeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->communeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabaseGetDataSelect();
                });

                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }
    public function handleDataBaseGetAllGetDataSelect2Cap()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabaseGetDataSelect2Cap();
            } else {
                $cacheKey = $this->params->communeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->communeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabaseGetDataSelect2Cap();
                });

                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }
    public function handleDataBaseGetAllGetDataSelectTHX()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabaseGetDataSelectTHX();
            } else {
                $cacheKey = $this->params->communeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->communeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabaseGetDataSelectTHX();
                });

                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['commune'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->communeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->communeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
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
