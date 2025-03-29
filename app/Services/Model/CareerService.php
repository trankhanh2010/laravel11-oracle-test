<?php

namespace App\Services\Model;

use App\DTOs\CareerDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Career\InsertCareerIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\CareerRepository;
use Illuminate\Support\Facades\Redis;

class CareerService 
{
    protected $careerRepository;
    protected $params;
    public function __construct(CareerRepository $careerRepository)
    {
        $this->careerRepository = $careerRepository;
    }
    public function withParams(CareerDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->careerRepository->applyJoins();
            $data = $this->careerRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->careerRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->careerRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->careerRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->careerName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->careerName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->careerRepository->applyJoins();
                $data = $this->careerRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->careerRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->careerRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->careerName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->careerName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->careerRepository->applyJoins()
                    ->where('his_career.id', $id);
                $data = $this->careerRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career'], $e);
        }
    }

    public function createCareer($request)
    {
        try {
            $data = $this->careerRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCareerIndex($data, $this->params->careerName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->careerName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career'], $e);
        }
    }

    public function updateCareer($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->careerRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->careerRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCareerIndex($data, $this->params->careerName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->careerName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career'], $e);
        }
    }

    public function deleteCareer($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->careerRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->careerRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->careerName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->careerName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career'], $e);
        }
    }
}
