<?php

namespace App\Services\Model;

use App\DTOs\CareerTitleDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\CareerTitle\InsertCareerTitleIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\CareerTitleRepository;
use Illuminate\Support\Facades\Redis;

class CareerTitleService 
{
    protected $careerTitleRepository;
    protected $params;
    public function __construct(CareerTitleRepository $careerTitleRepository)
    {
        $this->careerTitleRepository = $careerTitleRepository;
    }
    public function withParams(CareerTitleDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->careerTitleRepository->applyJoins();
            $data = $this->careerTitleRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->careerTitleRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->careerTitleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->careerTitleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career_title'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->careerTitleName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->careerTitleName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->careerTitleRepository->applyJoins();
                $data = $this->careerTitleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->careerTitleRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->careerTitleRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career_title'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->careerTitleName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->careerTitleName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->careerTitleRepository->applyJoins()
                    ->where('his_career_title.id', $id);
                $data = $this->careerTitleRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career_title'], $e);
        }
    }

    public function createCareerTitle($request)
    {
        try {
            $data = $this->careerTitleRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCareerTitleIndex($data, $this->params->careerTitleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->careerTitleName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career_title'], $e);
        }
    }

    public function updateCareerTitle($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->careerTitleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->careerTitleRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertCareerTitleIndex($data, $this->params->careerTitleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->careerTitleName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career_title'], $e);
        }
    }

    public function deleteCareerTitle($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->careerTitleRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->careerTitleRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->careerTitleName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->careerTitleName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['career_title'], $e);
        }
    }
}
