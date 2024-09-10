<?php

namespace App\Services\Model;

use App\DTOs\BedRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BedRoom\InsertBedRoomIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BedRoomRepository;

class BedRoomService 
{
    protected $bedRoomRepository;
    protected $params;
    public function __construct(BedRoomRepository $bedRoomRepository)
    {
        $this->bedRoomRepository = $bedRoomRepository;
    }
    public function withParams(BedRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bedRoomRepository->applyJoins();
            $data = $this->bedRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bedRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->bedRoomRepository->applyDepartmentIdFilter($data, $this->params->departmentId);
            $count = $data->count();
            $data = $this->bedRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bedRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->bedRoomName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->bedRoomRepository->applyJoins();
                $data = $this->bedRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->bedRoomRepository->applyDepartmentIdFilter($data, $this->params->departmentId);
                $count = $data->count();
                $data = $this->bedRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bedRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bedRoomName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bedRoomRepository->applyJoins()
                    ->where('his_bed_room.id', $id);
                $data = $this->bedRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_room'], $e);
        }
    }

    public function createBedRoom($request)
    {
        try {
            $data = $this->bedRoomRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedRoomName));
            // Gọi event để thêm index vào elastic
            event(new InsertBedRoomIndex($data, $this->params->bedRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_room'], $e);
        }
    }

    public function updateBedRoom($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedRoomRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedRoomName));
            // Gọi event để thêm index vào elastic
            event(new InsertBedRoomIndex($data, $this->params->bedRoomName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_room'], $e);
        }
    }

    public function deleteBedRoom($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bedRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bedRoomRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bedRoomName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bedRoomName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bed_room'], $e);
        }
    }
}
