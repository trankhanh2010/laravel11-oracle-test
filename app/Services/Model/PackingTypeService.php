<?php

namespace App\Services\Model;

use App\DTOs\PackingTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PackingType\InsertPackingTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PackingTypeRepository;

class PackingTypeService 
{
    protected $packingTypeRepository;
    protected $params;
    public function __construct(PackingTypeRepository $packingTypeRepository)
    {
        $this->packingTypeRepository = $packingTypeRepository;
    }
    public function withParams(PackingTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->packingTypeRepository->applyJoins();
            $data = $this->packingTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->packingTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->packingTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->packingTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->packingTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->packingTypeRepository->applyJoins();
                $data = $this->packingTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->packingTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->packingTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->packingTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->packingTypeRepository->applyJoins()
                    ->where('his_packing_type.id', $id);
                $data = $this->packingTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }

    public function createPackingType($request)
    {
        try {
            $data = $this->packingTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPackingTypeIndex($data, $this->params->packingTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packingTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }

    public function updatePackingType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packingTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packingTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertPackingTypeIndex($data, $this->params->packingTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packingTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }

    public function deletePackingType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->packingTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->packingTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->packingTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->packingTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['packing_type'], $e);
        }
    }
}
