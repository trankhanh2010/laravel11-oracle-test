<?php

namespace App\Services\Model;

use App\DTOs\MaterialPatyDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MaterialPaty\InsertMaterialPatyIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MaterialPatyRepository;
use Illuminate\Support\Facades\Redis;

class MaterialPatyService
{
    protected $materialPatyRepository;
    protected $params;
    public function __construct(MaterialPatyRepository $materialPatyRepository)
    {
        $this->materialPatyRepository = $materialPatyRepository;
    }
    public function withParams(MaterialPatyDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            if ($this->params->tab == 'getData') {
                $data = $this->materialPatyRepository->applyJoinsGetData();
            } else {
                $data = $this->materialPatyRepository->applyJoins();
            }
            $data = $this->materialPatyRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->materialPatyRepository->applyIsActiveFilter($data, $this->params->isActive);
            if ($this->params->tab == 'getData') {
                $count = null;
            } else {
                $count = $data->count();
            }
            $data = $this->materialPatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->materialPatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_paty'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        if ($this->params->tab == 'getData') {
            $data = $this->materialPatyRepository->applyJoinsGetData();
        } else {
            $data = $this->materialPatyRepository->applyJoins();
        }
        $data = $this->materialPatyRepository->applyIsActiveFilter($data, $this->params->isActive);
        if ($this->params->tab == 'getData') {
            $count = null;
        } else {
            $count = $data->count();
        }
        $data = $this->materialPatyRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->materialPatyRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->materialPatyRepository->applyJoins()
            ->where('his_material_paty.id', $id);
        $data = $this->materialPatyRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->materialPatyName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->materialPatyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_paty'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->materialPatyName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->materialPatyName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_paty'], $e);
        }
    }

    // public function createMaterialPaty($request)
    // {
    //     try {
    //         $data = $this->materialPatyRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertMaterialPatyIndex($data, $this->params->materialPatyName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->materialPatyName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['material_paty'], $e);
    //     }
    // }

    // public function updateMaterialPaty($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->materialPatyRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->materialPatyRepository->update($request, $data, $this->params->time, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertMaterialPatyIndex($data, $this->params->materialPatyName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->materialPatyName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['material_paty'], $e);
    //     }
    // }

    // public function deleteMaterialPaty($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->materialPatyRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->materialPatyRepository->delete($data);

    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->materialPatyName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->materialPatyName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['material_paty'], $e);
    //     }
    // }
}
