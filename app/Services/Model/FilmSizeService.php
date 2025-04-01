<?php

namespace App\Services\Model;

use App\DTOs\FilmSizeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\FilmSize\InsertFilmSizeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\FilmSizeRepository;
use Illuminate\Support\Facades\Redis;

class FilmSizeService
{
    protected $filmSizeRepository;
    protected $params;
    public function __construct(FilmSizeRepository $filmSizeRepository)
    {
        $this->filmSizeRepository = $filmSizeRepository;
    }
    public function withParams(FilmSizeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->filmSizeRepository->applyJoins();
            $data = $this->filmSizeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->filmSizeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->filmSizeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->filmSizeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->filmSizeRepository->applyJoins();
        $data = $this->filmSizeRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->filmSizeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->filmSizeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->filmSizeRepository->applyJoins()
            ->where('his_film_size.id', $id);
        $data = $this->filmSizeRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->filmSizeName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->filmSizeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->filmSizeName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->filmSizeName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
    public function createFilmSize($request)
    {
        try {
            $data = $this->filmSizeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertFilmSizeIndex($data, $this->params->filmSizeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->filmSizeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }

    public function updateFilmSize($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->filmSizeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->filmSizeRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertFilmSizeIndex($data, $this->params->filmSizeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->filmSizeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }

    public function deleteFilmSize($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->filmSizeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->filmSizeRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->filmSizeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->filmSizeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
}
