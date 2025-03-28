<?php

namespace App\Services\Model;

use App\DTOs\PayFormDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PayForm\InsertPayFormIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PayFormRepository;
use Illuminate\Support\Facades\Redis;

class PayFormService 
{
    protected $payFormRepository;
    protected $params;
    public function __construct(PayFormRepository $payFormRepository)
    {
        $this->payFormRepository = $payFormRepository;
    }
    public function withParams(PayFormDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->payFormRepository->applyJoins();
            $data = $this->payFormRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->payFormRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->payFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->payFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pay_form'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->payFormName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->payFormName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->payFormRepository->applyJoins();
                $data = $this->payFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->payFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->payFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pay_form'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->payFormName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->payFormRepository->applyJoins()
                    ->where('his_pay_form.id', $id);
                $data = $this->payFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pay_form'], $e);
        }
    }

    public function createPayForm($request)
    {
        try {
            $data = $this->payFormRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPayFormIndex($data, $this->params->payFormName));
             // Gọi event để xóa cache
             event(new DeleteCache($this->params->payFormName));           
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pay_form'], $e);
        }
    }

    public function updatePayForm($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->payFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->payFormRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertPayFormIndex($data, $this->params->payFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->payFormName));            
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pay_form'], $e);
        }
    }

    public function deletePayForm($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->payFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->payFormRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->payFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->payFormName));            
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pay_form'], $e);
        }
    }
}
