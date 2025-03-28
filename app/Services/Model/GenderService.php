<?php

namespace App\Services\Model;

use App\DTOs\GenderDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Gender\InsertGenderIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\GenderRepository;
use Illuminate\Support\Facades\Redis;

class GenderService 
{
    protected $genderRepository;
    protected $params;
    public function __construct(GenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }
    public function withParams(GenderDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->genderRepository->applyJoins();
            $data = $this->genderRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->genderRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->genderRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->genderRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->genderName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->genderName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->genderRepository->applyJoins();
                $data = $this->genderRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->genderRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->genderRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->genderName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->genderRepository->applyJoins()
                    ->where('his_gender.id', $id);
                $data = $this->genderRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
    public function createGender($request)
    {
        try {
            $data = $this->genderRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertGenderIndex($data, $this->params->genderName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->genderName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }

    public function updateGender($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->genderRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->genderRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertGenderIndex($data, $this->params->genderName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->genderName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }

    public function deleteGender($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->genderRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->genderRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->genderName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->genderName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
}
