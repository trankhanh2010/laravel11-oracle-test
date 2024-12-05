<?php

namespace App\Services\Model;

use App\DTOs\SpecialityDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Speciality\InsertSpecialityIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SpecialityRepository;

class SpecialityService 
{
    protected $specialityRepository;
    protected $params;
    public function __construct(SpecialityRepository $specialityRepository)
    {
        $this->specialityRepository = $specialityRepository;
    }
    public function withParams(SpecialityDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->specialityRepository->applyJoins();
            $data = $this->specialityRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->specialityRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->specialityRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->specialityRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speciality'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->specialityName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->specialityRepository->applyJoins();
                $data = $this->specialityRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->specialityRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->specialityRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speciality'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->specialityName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->specialityRepository->applyJoins()
                    ->where('his_speciality.id', $id);
                $data = $this->specialityRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speciality'], $e);
        }
    }

    public function createSpeciality($request)
    {
        try {
            $data = $this->specialityRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSpecialityIndex($data, $this->params->specialityName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->specialityName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speciality'], $e);
        }
    }

    public function updateSpeciality($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->specialityRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->specialityRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertSpecialityIndex($data, $this->params->specialityName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->specialityName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speciality'], $e);
        }
    }

    public function deleteSpeciality($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->specialityRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->specialityRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->specialityName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->specialityName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['speciality'], $e);
        }
    }
}
