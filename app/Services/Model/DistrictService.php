<?php

namespace App\Services\Model;

use App\DTOs\DistrictDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\District\InsertDistrictIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DistrictRepository;

class DistrictService 
{
    protected $districtRepository;
    protected $params;
    public function __construct(DistrictRepository $districtRepository)
    {
        $this->districtRepository = $districtRepository;
    }
    public function withParams(DistrictDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->districtRepository->applyJoins();
            $data = $this->districtRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->districtRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->districtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->districtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->districtName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->districtRepository->applyJoins();
                $data = $this->districtRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->districtRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->districtRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->districtName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->districtRepository->applyJoins()
                    ->where('sda_district.id', $id);
                $data = $this->districtRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }

    public function createDistrict($request)
    {
        try {
            $data = $this->districtRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->districtName));
            // Gọi event để thêm index vào elastic
            event(new InsertDistrictIndex($data, $this->params->districtName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }

    public function updateDistrict($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->districtRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->districtRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->districtName));
            // Gọi event để thêm index vào elastic
            event(new InsertDistrictIndex($data, $this->params->districtName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }

    public function deleteDistrict($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->districtRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->districtRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->districtName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->districtName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['district'], $e);
        }
    }
}