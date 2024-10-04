<?php

namespace App\Services\Model;

use App\DTOs\ImpSourceDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ImpSource\InsertImpSourceIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ImpSourceRepository;

class ImpSourceService 
{
    protected $impSourceRepository;
    protected $params;
    public function __construct(ImpSourceRepository $impSourceRepository)
    {
        $this->impSourceRepository = $impSourceRepository;
    }
    public function withParams(ImpSourceDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->impSourceRepository->applyJoins();
            $data = $this->impSourceRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->impSourceRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->impSourceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->impSourceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['imp_source'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->impSourceName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->impSourceRepository->applyJoins();
                $data = $this->impSourceRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->impSourceRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->impSourceRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['imp_source'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->impSourceName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->impSourceRepository->applyJoins()
                    ->where('his_imp_source.id', $id);
                $data = $this->impSourceRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['imp_source'], $e);
        }
    }

    public function createImpSource($request)
    {
        try {
            $data = $this->impSourceRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->impSourceName));
            // Gọi event để thêm index vào elastic
            event(new InsertImpSourceIndex($data, $this->params->impSourceName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['imp_source'], $e);
        }
    }

    public function updateImpSource($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->impSourceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->impSourceRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->impSourceName));
            // Gọi event để thêm index vào elastic
            event(new InsertImpSourceIndex($data, $this->params->impSourceName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['imp_source'], $e);
        }
    }

    public function deleteImpSource($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->impSourceRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->impSourceRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->impSourceName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->impSourceName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['imp_source'], $e);
        }
    }
}
