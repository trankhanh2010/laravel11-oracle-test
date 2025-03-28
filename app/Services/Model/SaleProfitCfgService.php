<?php

namespace App\Services\Model;

use App\DTOs\SaleProfitCfgDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SaleProfitCfg\InsertSaleProfitCfgIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SaleProfitCfgRepository;
use Illuminate\Support\Facades\Redis;

class SaleProfitCfgService 
{
    protected $saleProfitCfgRepository;
    protected $params;
    public function __construct(SaleProfitCfgRepository $saleProfitCfgRepository)
    {
        $this->saleProfitCfgRepository = $saleProfitCfgRepository;
    }
    public function withParams(SaleProfitCfgDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->saleProfitCfgRepository->applyJoins();
            $data = $this->saleProfitCfgRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->saleProfitCfgRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->saleProfitCfgRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->saleProfitCfgRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sale_profit_cfg'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->saleProfitCfgName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->saleProfitCfgName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->saleProfitCfgRepository->applyJoins();
                $data = $this->saleProfitCfgRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->saleProfitCfgRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->saleProfitCfgRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sale_profit_cfg'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->saleProfitCfgName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->saleProfitCfgRepository->applyJoins()
                    ->where('his_sale_profit_cfg.id', $id);
                $data = $this->saleProfitCfgRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sale_profit_cfg'], $e);
        }
    }

    public function createSaleProfitCfg($request)
    {
        try {
            $data = $this->saleProfitCfgRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSaleProfitCfgIndex($data, $this->params->saleProfitCfgName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->saleProfitCfgName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sale_profit_cfg'], $e);
        }
    }

    public function updateSaleProfitCfg($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->saleProfitCfgRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->saleProfitCfgRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSaleProfitCfgIndex($data, $this->params->saleProfitCfgName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->saleProfitCfgName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sale_profit_cfg'], $e);
        }
    }

    public function deleteSaleProfitCfg($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->saleProfitCfgRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->saleProfitCfgRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->saleProfitCfgName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->saleProfitCfgName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sale_profit_cfg'], $e);
        }
    }
}
