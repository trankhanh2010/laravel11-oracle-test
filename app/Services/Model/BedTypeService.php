<?php

namespace App\Services\Model;

use App\DTOs\BedTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BedType\InsertBedTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BedTypeRepository;

class BedTypeService 
{
    protected $bedTypeRepository;
    protected $params;
    public function __construct(BedTypeRepository $bedTypeRepository)
    {
        $this->bedTypeRepository = $bedTypeRepository;
    }
    public function withParams(BedTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bedTypeRepository->applyJoins();
            $data = $this->bedTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bedTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bedTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bedTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->bedTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->bedTypeRepository->applyJoins();
                $data = $this->bedTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bedTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bedTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bedTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bedTypeRepository->applyJoins()
                    ->where('his_bed_type.id', $id);
                $data = $this->bedTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_type'], $e);
        }
    }
    public function createBedType($request)
    {
        try {
            $data = $this->bedTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBedTypeIndex($data, $this->params->bedTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_type'], $e);
        }
    }

    public function updateBedType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBedTypeIndex($data, $this->params->bedTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_type'], $e);
        }
    }

    public function deleteBedType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bedTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_type'], $e);
        }
    }
}
