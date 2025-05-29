<?php

namespace App\Services\Model;

use App\DTOs\EquipmentSetDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\EquipmentSet\InsertEquipmentSetIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EquipmentSetRepository;
use Illuminate\Support\Facades\Redis;

class EquipmentSetService
{
    protected $equipmentSetRepository;
    protected $params;
    public function __construct(EquipmentSetRepository $equipmentSetRepository)
    {
        $this->equipmentSetRepository = $equipmentSetRepository;
    }
    public function withParams(EquipmentSetDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->equipmentSetRepository->applyJoins();
            $data = $this->equipmentSetRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->equipmentSetRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->equipmentSetRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->equipmentSetRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['equipment_set'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->equipmentSetRepository->applyJoins();
        $data = $this->equipmentSetRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->equipmentSetRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->equipmentSetRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->equipmentSetRepository->applyJoins()
            ->where('his_equipment_set.id', $id);
        $data = $this->equipmentSetRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->equipmentSetName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->equipmentSetName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['equipment_set'], $e);
        }
    }
    // public function handleDataBaseGetWithId($id)
    // {
    //     try {
    //         // Nếu không lưu cache
    //         if ($this->params->noCache) {
    //             return $this->getDataById($id);
    //         } else {
    //             $cacheKey = $this->params->equipmentSetName . '_' . $id . '_' . $this->params->param;
    //             $cacheKeySet = "cache_keys:" . $this->params->equipmentSetName; // Set để lưu danh sách key
    //             $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
    //                 return $this->getDataById($id);
    //             });
    //             // Lưu key vào Redis Set để dễ xóa sau này
    //             Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
    //             return $data;
    //         }
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['equipment_set'], $e);
    //     }
    // }

    // public function createEquipmentSet($request)
    // {
    //     try {
    //         $data = $this->equipmentSetRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertEquipmentSetIndex($data, $this->params->equipmentSetName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->equipmentSetName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['equipment_set'], $e);
    //     }
    // }

    // public function updateEquipmentSet($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->equipmentSetRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->equipmentSetRepository->update($request, $data, $this->params->time, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertEquipmentSetIndex($data, $this->params->equipmentSetName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->equipmentSetName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['equipment_set'], $e);
    //     }
    // }

    // public function deleteEquipmentSet($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->equipmentSetRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->equipmentSetRepository->delete($data);

    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->equipmentSetName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->equipmentSetName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['equipment_set'], $e);
    //     }
    // }
}
