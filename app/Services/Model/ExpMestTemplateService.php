<?php

namespace App\Services\Model;

use App\DTOs\ExpMestTemplateDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ExpMestTemplate\InsertExpMestTemplateIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ExpMestTemplateRepository;
use Illuminate\Support\Facades\Redis;

class ExpMestTemplateService
{
    protected $expMestTemplateRepository;
    protected $params;
    public function __construct(ExpMestTemplateRepository $expMestTemplateRepository)
    {
        $this->expMestTemplateRepository = $expMestTemplateRepository;
    }
    public function withParams(ExpMestTemplateDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->expMestTemplateRepository->applyJoins();
            $data = $this->expMestTemplateRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->expMestTemplateRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->expMestTemplateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->expMestTemplateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_template'], $e);
        }
    }

    private function getAllDataFromDatabase()
    {
        $data = $this->expMestTemplateRepository->applyJoins();
        $data = $this->expMestTemplateRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = $data->count();
        $data = $this->expMestTemplateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->expMestTemplateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataFromDatabaseSelectByLoginname()
    {
        $data = $this->expMestTemplateRepository->applyJoins();
        $data = $this->expMestTemplateRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->expMestTemplateRepository->applySelectByLoginnameFilter($data, $this->params->currentLoginname);
        $count = $data->count();
        $data = $this->expMestTemplateRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->expMestTemplateRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->expMestTemplateRepository->applyJoins()
            ->where('his_exp_mest_template.id', $id);
        $data = $this->expMestTemplateRepository->applyIsActiveFilter($data, 1);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAllDataFromDatabaseSelectByLoginname()
    {
        try {
            return $this->getAllDataFromDatabaseSelectByLoginname();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_template'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->expMestTemplateName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->expMestTemplateName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, $this->params->time, function () {
                    return $this->getAllDataFromDatabase();
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_template'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['exp_mest_template'], $e);
        }
    }
}
