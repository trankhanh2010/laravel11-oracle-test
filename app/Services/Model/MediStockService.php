<?php

namespace App\Services\Model;

use App\DTOs\MediStockDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediStock\InsertMediStockIndex;
use App\Events\Elastic\DeleteIndex;
use App\Http\Resources\DB\DataResource;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MediStockRepository;

class MediStockService 
{
    protected $mediStockRepository;
    protected $params;
    public function __construct(MediStockRepository $mediStockRepository)
    {
        $this->mediStockRepository = $mediStockRepository;
    }
    public function withParams(MediStockDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function applyResource($data)
    {
        try {
            $data = new DataResource(resource: $data);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['apply_resource'], $e);
        }
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mediStockRepository->applyJoins();
            $data = $this->mediStockRepository->applyWith($data);
            $data = $this->mediStockRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mediStockRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->mediStockRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mediStockRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            $data = $this->applyResource($data);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->mediStockName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->mediStockRepository->applyJoins();
                $data = $this->mediStockRepository->applyWith($data);
                $data = $this->mediStockRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->mediStockRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->mediStockRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                $data = $this->applyResource($data);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->mediStockName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->mediStockRepository->applyJoins()
                    ->where('his_medi_stock.id', $id);
                $data = $this->mediStockRepository->applyWith($data);
                $data = $this->mediStockRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                $data = $this->applyResource($data);
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }

    public function createMediStock($request)
    {
        try {
            $data = $this->mediStockRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMediStockIndex($data, $this->params->mediStockName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediStockName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }

    public function updateMediStock($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediStockRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediStockRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMediStockIndex($data, $this->params->mediStockName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediStockName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }

    public function deleteMediStock($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediStockRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediStockRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->mediStockName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediStockName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_stock'], $e);
        }
    }
}
