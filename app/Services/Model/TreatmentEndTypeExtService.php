<?php

namespace App\Services\Model;

use App\DTOs\TreatmentEndTypeExtDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TreatmentEndTypeExt\InsertTreatmentEndTypeExtIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TreatmentEndTypeExtRepository;
use Illuminate\Support\Facades\Redis;

class TreatmentEndTypeExtService
{
    protected $treatmentEndTypeExtRepository;
    protected $params;
    public function __construct(TreatmentEndTypeExtRepository $treatmentEndTypeExtRepository)
    {
        $this->treatmentEndTypeExtRepository = $treatmentEndTypeExtRepository;
    }
    public function withParams(TreatmentEndTypeExtDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->treatmentEndTypeExtRepository->applyJoins();
            $data = $this->treatmentEndTypeExtRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->treatmentEndTypeExtRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->treatmentEndTypeExtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->treatmentEndTypeExtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type_ext'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->treatmentEndTypeExtRepository->applyJoins();
        $data = $this->treatmentEndTypeExtRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->treatmentEndTypeExtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->treatmentEndTypeExtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->treatmentEndTypeExtRepository->applyJoins()
            ->where('his_treatment_end_type_ext.id', $id);
        $data = $this->treatmentEndTypeExtRepository->applyIsActiveFilter($data, $this->params->isActive);
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
                $cacheKey = $this->params->treatmentEndTypeExtName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->treatmentEndTypeExtName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type_ext'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getDataById($id);
            } else {
                $cacheKey = $this->params->treatmentEndTypeExtName . '_' . $id . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->treatmentEndTypeExtName; // Set để lưu danh sách key
                $data = Cache::remember($cacheKey, $this->params->time, function () use ($id) {
                    return $this->getDataById($id);
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type_ext'], $e);
        }
    }

    // public function createTreatmentEndTypeExt($request)
    // {
    //     try {
    //         $data = $this->treatmentEndTypeExtRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentEndTypeExtIndex($data, $this->params->treatmentEndTypeExtName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentEndTypeExtName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type_ext'], $e);
    //     }
    // }

    // public function updateTreatmentEndTypeExt($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentEndTypeExtRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentEndTypeExtRepository->update($request, $data, $this->params->time, $this->params->appModifier);

    //         // Gọi event để thêm index vào elastic
    //         event(new InsertTreatmentEndTypeExtIndex($data, $this->params->treatmentEndTypeExtName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentEndTypeExtName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type_ext'], $e);
    //     }
    // }

    // public function deleteTreatmentEndTypeExt($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->treatmentEndTypeExtRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->treatmentEndTypeExtRepository->delete($data);

    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->treatmentEndTypeExtName));
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->treatmentEndTypeExtName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['treatment_end_type_ext'], $e);
    //     }
    // }
}
