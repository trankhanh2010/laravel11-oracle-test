<?php

namespace App\Services\Model;

use App\DTOs\MaterialTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MaterialType\InsertMaterialTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MaterialTypeRepository;

class MaterialTypeService 
{
    protected $materialTypeRepository;
    protected $params;
    public function __construct(MaterialTypeRepository $materialTypeRepository)
    {
        $this->materialTypeRepository = $materialTypeRepository;
    }
    public function withParams(MaterialTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->materialTypeRepository->applyJoins();
            $data = $this->materialTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->materialTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->materialTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->materialTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->materialTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->materialTypeRepository->applyJoins();
                $data = $this->materialTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->materialTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->materialTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->materialTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->materialTypeRepository->applyJoins()
                    ->where('his_material_type.id', $id);
                $data = $this->materialTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type'], $e);
        }
    }
    public function createMaterialType($request)
    {
        try {
            $data = $this->materialTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMaterialTypeIndex($data, $this->params->materialTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->materialTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type'], $e);
        }
    }

    public function updateMaterialType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->materialTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->materialTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMaterialTypeIndex($data, $this->params->materialTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->materialTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type'], $e);
        }
    }

    public function deleteMaterialType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->materialTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->materialTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->materialTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->materialTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['material_type'], $e);
        }
    }
}
