<?php

namespace App\Services\Model;

use App\DTOs\LocationStoreDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\LocationStore\InsertLocationStoreIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\LocationStoreRepository;

class LocationStoreService 
{
    protected $locationStoreRepository;
    protected $params;
    public function __construct(LocationStoreRepository $locationStoreRepository)
    {
        $this->locationStoreRepository = $locationStoreRepository;
    }
    public function withParams(LocationStoreDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->locationStoreRepository->applyJoins();
            $data = $this->locationStoreRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->locationStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->locationStoreRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->locationStoreRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->locationStoreName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->locationStoreRepository->applyJoins();
                $data = $this->locationStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->locationStoreRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->locationStoreRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->locationStoreName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->locationStoreRepository->applyJoins()
                    ->where('his_location_store.id', $id);
                $data = $this->locationStoreRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }

    public function createLocationStore($request)
    {
        try {
            $data = $this->locationStoreRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->locationStoreName));
            // Gọi event để thêm index vào elastic
            event(new InsertLocationStoreIndex($data, $this->params->locationStoreName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }

    public function updateLocationStore($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->locationStoreRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->locationStoreRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->locationStoreName));
            // Gọi event để thêm index vào elastic
            event(new InsertLocationStoreIndex($data, $this->params->locationStoreName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }

    public function deleteLocationStore($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->locationStoreRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->locationStoreRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->locationStoreName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->locationStoreName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['location_store'], $e);
        }
    }
}