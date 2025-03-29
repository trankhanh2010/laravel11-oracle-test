<?php

namespace App\Services\Model;

use App\DTOs\SupplierDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Supplier\InsertSupplierIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SupplierRepository;
use Illuminate\Support\Facades\Redis;

class SupplierService 
{
    protected $supplierRepository;
    protected $params;
    public function __construct(SupplierRepository $supplierRepository)
    {
        $this->supplierRepository = $supplierRepository;
    }
    public function withParams(SupplierDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->supplierRepository->applyJoins();
            $data = $this->supplierRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->supplierRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->supplierRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->supplierRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['supplier'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->supplierName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->supplierName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->supplierRepository->applyJoins();
                $data = $this->supplierRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->supplierRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->supplierRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['supplier'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->supplierName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->supplierName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->supplierRepository->applyJoins()
                    ->where('his_supplier.id', $id);
                $data = $this->supplierRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['supplier'], $e);
        }
    }

    public function createSupplier($request)
    {
        try {
            $data = $this->supplierRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSupplierIndex($data, $this->params->supplierName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->supplierName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['supplier'], $e);
        }
    }

    public function updateSupplier($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->supplierRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->supplierRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSupplierIndex($data, $this->params->supplierName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->supplierName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['supplier'], $e);
        }
    }

    public function deleteSupplier($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->supplierRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->supplierRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->supplierName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->supplierName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['supplier'], $e);
        }
    }
}
