<?php

namespace App\Services\Model;

use App\DTOs\BloodVolumeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BloodVolume\InsertBloodVolumeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BloodVolumeRepository;
use Illuminate\Support\Facades\Redis;

class BloodVolumeService 
{
    protected $bloodVolumeRepository;
    protected $params;
    public function __construct(BloodVolumeRepository $bloodVolumeRepository)
    {
        $this->bloodVolumeRepository = $bloodVolumeRepository;
    }
    public function withParams(BloodVolumeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bloodVolumeRepository->applyJoins();
            $data = $this->bloodVolumeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bloodVolumeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bloodVolumeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bloodVolumeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_volume'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->bloodVolumeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->bloodVolumeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->bloodVolumeRepository->applyJoins();
                $data = $this->bloodVolumeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bloodVolumeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bloodVolumeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_volume'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->bloodVolumeName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->bloodVolumeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->bloodVolumeRepository->applyJoins()
                    ->where('his_blood_volume.id', $id);
                $data = $this->bloodVolumeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_volume'], $e);
        }
    }

    public function createBloodVolume($request)
    {
        try {
            $data = $this->bloodVolumeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBloodVolumeIndex($data, $this->params->bloodVolumeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bloodVolumeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_volume'], $e);
        }
    }

    public function updateBloodVolume($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bloodVolumeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bloodVolumeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBloodVolumeIndex($data, $this->params->bloodVolumeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bloodVolumeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_volume'], $e);
        }
    }

    public function deleteBloodVolume($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bloodVolumeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bloodVolumeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bloodVolumeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bloodVolumeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['blood_volume'], $e);
        }
    }
}
