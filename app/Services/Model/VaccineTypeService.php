<?php

namespace App\Services\Model;

use App\DTOs\VaccineTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\VaccineType\InsertVaccineTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\VaccineTypeRepository;

class VaccineTypeService 
{
    protected $vaccineTypeRepository;
    protected $params;
    public function __construct(VaccineTypeRepository $vaccineTypeRepository)
    {
        $this->vaccineTypeRepository = $vaccineTypeRepository;
    }
    public function withParams(VaccineTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->vaccineTypeRepository->applyJoins();
            $data = $this->vaccineTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->vaccineTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->vaccineTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->vaccineTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['vaccine_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->vaccineTypeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->vaccineTypeRepository->applyJoins();
                $data = $this->vaccineTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->vaccineTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->vaccineTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['vaccine_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->vaccineTypeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->vaccineTypeRepository->applyJoins()
                    ->where('his_vaccine_type.id', $id);
                $data = $this->vaccineTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['vaccine_type'], $e);
        }
    }

    public function createVaccineType($request)
    {
        try {
            $data = $this->vaccineTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->vaccineTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertVaccineTypeIndex($data, $this->params->vaccineTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['vaccine_type'], $e);
        }
    }

    public function updateVaccineType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->vaccineTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->vaccineTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->vaccineTypeName));
            // Gọi event để thêm index vào elastic
            event(new InsertVaccineTypeIndex($data, $this->params->vaccineTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['vaccine_type'], $e);
        }
    }

    public function deleteVaccineType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->vaccineTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->vaccineTypeRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->vaccineTypeName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->vaccineTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['vaccine_type'], $e);
        }
    }
}
