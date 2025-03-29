<?php

namespace App\Services\Model;

use App\DTOs\ServiceReqSttDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceReqStt\InsertServiceReqSttIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ServiceReqSttRepository;
use Illuminate\Support\Facades\Redis;

class ServiceReqSttService 
{
    protected $serviceReqSttRepository;
    protected $params;
    public function __construct(ServiceReqSttRepository $serviceReqSttRepository)
    {
        $this->serviceReqSttRepository = $serviceReqSttRepository;
    }
    public function withParams(ServiceReqSttDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->serviceReqSttRepository->applyJoins();
            $data = $this->serviceReqSttRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->serviceReqSttRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->serviceReqSttRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->serviceReqSttRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_stt'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->serviceReqSttName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->serviceReqSttName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->serviceReqSttRepository->applyJoins();
                $data = $this->serviceReqSttRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->serviceReqSttRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->serviceReqSttRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_stt'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->serviceReqSttName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->serviceReqSttName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->serviceReqSttRepository->applyJoins()
                    ->where('his_service_req_stt.id', $id);
                $data = $this->serviceReqSttRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_stt'], $e);
        }
    }

    public function createServiceReqStt($request)
    {
        try {
            $data = $this->serviceReqSttRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceReqSttIndex($data, $this->params->serviceReqSttName));
             // Gọi event để xóa cache
             event(new DeleteCache($this->params->serviceReqSttName));           
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_stt'], $e);
        }
    }

    public function updateServiceReqStt($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceReqSttRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceReqSttRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertServiceReqSttIndex($data, $this->params->serviceReqSttName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqSttName));            
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_stt'], $e);
        }
    }

    public function deleteServiceReqStt($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->serviceReqSttRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->serviceReqSttRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->serviceReqSttName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->serviceReqSttName));            
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['service_req_stt'], $e);
        }
    }
}
