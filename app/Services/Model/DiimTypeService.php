<?php

namespace App\Services\Model;

use App\DTOs\DiimTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DiimType\InsertDiimTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DiimTypeRepository;

class DiimTypeService 
{
    protected $diimTypeRepository;
    protected $params;
    public function __construct(DiimTypeRepository $diimTypeRepository)
    {
        $this->diimTypeRepository = $diimTypeRepository;
    }
    public function withParams(DiimTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->diimTypeRepository->applyJoins();
            $data = $this->diimTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->diimTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->diimTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->diimTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['diim_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->diimTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->diimTypeRepository->applyJoins();
                $data = $this->diimTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->diimTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->diimTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['diim_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->diimTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->diimTypeRepository->applyJoins()
                    ->where('his_diim_type.id', $id);
                $data = $this->diimTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['diim_type'], $e);
        }
    }

    public function createDiimType($request)
    {
        try {
            $data = $this->diimTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDiimTypeIndex($data, $this->params->diimTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->diimTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['diim_type'], $e);
        }
    }

    public function updateDiimType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->diimTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->diimTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDiimTypeIndex($data, $this->params->diimTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->diimTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['diim_type'], $e);
        }
    }

    public function deleteDiimType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->diimTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->diimTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->diimTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->diimTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['diim_type'], $e);
        }
    }
}
