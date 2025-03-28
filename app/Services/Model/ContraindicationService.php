<?php

namespace App\Services\Model;

use App\DTOs\ContraindicationDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Contraindication\InsertContraindicationIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ContraindicationRepository;
use Illuminate\Support\Facades\Redis;

class ContraindicationService 
{
    protected $contraindicationRepository;
    protected $params;
    public function __construct(ContraindicationRepository $contraindicationRepository)
    {
        $this->contraindicationRepository = $contraindicationRepository;
    }
    public function withParams(ContraindicationDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->contraindicationRepository->applyJoins();
            $data = $this->contraindicationRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->contraindicationRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->contraindicationRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->contraindicationRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['contraindication'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->contraindicationName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->contraindicationName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->contraindicationRepository->applyJoins();
                $data = $this->contraindicationRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->contraindicationRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->contraindicationRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['contraindication'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->contraindicationName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->contraindicationRepository->applyJoins()
                    ->where('his_contraindication.id', $id);
                $data = $this->contraindicationRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['contraindication'], $e);
        }
    }

    public function createContraindication($request)
    {
        try {
            $data = $this->contraindicationRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertContraindicationIndex($data, $this->params->contraindicationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->contraindicationName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['contraindication'], $e);
        }
    }

    public function updateContraindication($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->contraindicationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->contraindicationRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertContraindicationIndex($data, $this->params->contraindicationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->contraindicationName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['contraindication'], $e);
        }
    }

    public function deleteContraindication($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->contraindicationRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->contraindicationRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->contraindicationName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->contraindicationName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['contraindication'], $e);
        }
    }
}
