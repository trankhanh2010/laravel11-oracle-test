<?php

namespace App\Services\Model;

use App\DTOs\BhytParamDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BhytParam\InsertBhytParamIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BhytParamRepository;

class BhytParamService 
{
    protected $bhytParamRepository;
    protected $params;
    public function __construct(BhytParamRepository $bhytParamRepository)
    {
        $this->bhytParamRepository = $bhytParamRepository;
    }
    public function withParams(BhytParamDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bhytParamRepository->applyJoins();
            $data = $this->bhytParamRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bhytParamRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bhytParamRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bhytParamRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_param'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->bhytParamName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->bhytParamRepository->applyJoins();
                $data = $this->bhytParamRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bhytParamRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bhytParamRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_param'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bhytParamName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bhytParamRepository->applyJoins()
                    ->where('his_bhyt_param.id', $id);
                $data = $this->bhytParamRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_param'], $e);
        }
    }

    public function createBhytParam($request)
    {
        try {
            $data = $this->bhytParamRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytParamName));
            // Gọi event để thêm index vào elastic
            event(new InsertBhytParamIndex($data, $this->params->bhytParamName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_param'], $e);
        }
    }

    public function updateBhytParam($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bhytParamRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bhytParamRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytParamName));
            // Gọi event để thêm index vào elastic
            event(new InsertBhytParamIndex($data, $this->params->bhytParamName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_param'], $e);
        }
    }

    public function deleteBhytParam($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bhytParamRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bhytParamRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bhytParamName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bhytParamName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bhyt_param'], $e);
        }
    }
}
