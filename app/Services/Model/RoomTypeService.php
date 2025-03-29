<?php

namespace App\Services\Model;

use App\DTOs\RoomTypeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\RoomType\InsertRoomTypeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\RoomTypeRepository;
use Illuminate\Support\Facades\Redis;

class RoomTypeService 
{
    protected $roomTypeRepository;
    protected $params;
    public function __construct(RoomTypeRepository $roomTypeRepository)
    {
        $this->roomTypeRepository = $roomTypeRepository;
    }
    public function withParams(RoomTypeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->roomTypeRepository->applyJoins();
            $data = $this->roomTypeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->roomTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->roomTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->roomTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_type'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->roomTypeName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->roomTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () {
                $data = $this->roomTypeRepository->applyJoins();
                $data = $this->roomTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->roomTypeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->roomTypeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_type'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $cacheKey = $this->params->roomTypeName .'_'.$id.'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->roomTypeName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, $this->params->time, function () use($id){
                $data = $this->roomTypeRepository->applyJoins()
                    ->where('his_room_type.id', $id);
                $data = $this->roomTypeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_type'], $e);
        }
    }

    public function createRoomType($request)
    {
        try {
            $data = $this->roomTypeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertRoomTypeIndex($data, $this->params->roomTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->roomTypeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_type'], $e);
        }
    }

    public function updateRoomType($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->roomTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->roomTypeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertRoomTypeIndex($data, $this->params->roomTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->roomTypeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_type'], $e);
        }
    }

    public function deleteRoomType($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->roomTypeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->roomTypeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->roomTypeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->roomTypeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['room_type'], $e);
        }
    }
}
