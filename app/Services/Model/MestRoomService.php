<?php

namespace App\Services\Model;

use App\DTOs\MestRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MestRoom\InsertMestRoomIndex;
use App\Events\Elastic\DeleteIndex;
use App\Repositories\RoomRepository;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MestRoomRepository;
use App\Repositories\MediStockRepository;
use Illuminate\Support\Facades\DB;

class MestRoomService
{
    protected $mestRoomRepository;
    protected $mediStockRepository;
    protected $roomRepository;
    protected $params;
    public function __construct(MestRoomRepository $mestRoomRepository, MediStockRepository $mediStockRepository, RoomRepository $roomRepository)
    {
        $this->mestRoomRepository = $mestRoomRepository;
        $this->mediStockRepository = $mediStockRepository;
        $this->roomRepository = $roomRepository;
    }
    public function withParams(MestRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mestRoomRepository->applyJoins();
            $data = $this->mestRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mestRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->mestRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
            $data = $this->mestRoomRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
            $count = $data->count();
            $data = $this->mestRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mestRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->mestRoomName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_medi_stock_id_' . $this->params->mediStockId . '_room_id_' . $this->params->roomId . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->mestRoomRepository->applyJoins();
                $data = $this->mestRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->mestRoomRepository->applyRoomIdFilter($data, $this->params->roomId);
                $data = $this->mestRoomRepository->applyMediStockIdFilter($data, $this->params->mediStockId);
                $count = $data->count();
                $data = $this->mestRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->mestRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->mestRoomName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->mestRoomRepository->applyJoins()
                    ->where('his_mest_room.id', $id);
                $data = $this->mestRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_room'], $e);
        }
    }
    private function buildSyncData($request)
    {
        return [
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $this->params->time),
            'app_creator' => $this->params->appCreator,
            'app_modifier' => $this->params->appModifier,
        ];
    }
    public function createMestRoom($request)
    {
        try {
            if ($request->room_id != null) {
                $id = $request->room_id;
                $data = $this->roomRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->medi_stock_ids !== null) {
                        $medi_stock_ids_arr = explode(',', $request->medi_stock_ids);
                        foreach ($medi_stock_ids_arr as $key => $item) {
                            $medi_stock_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->medi_stocks()->sync($medi_stock_ids_arr_data);
                    } else {
                        $deleteIds = $this->mestRoomRepository->deleteByRoomId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mestRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mestRoomRepository->getByRoomIdAndMediStockIds($id, $medi_stock_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMestRoomIndex($item, $this->params->mestRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            if ($request->medi_stock_id != null) {
                $id = $request->medi_stock_id;
                $data = $this->mediStockRepository->getById($id);
                if ($data == null) {
                    return returnNotRecord($id);
                }
                // Start transaction
                DB::connection('oracle_his')->beginTransaction();
                try {
                    if ($request->room_ids !== null) {
                        $room_ids_arr = explode(',', $request->room_ids);
                        foreach ($room_ids_arr as $key => $item) {
                            $room_ids_arr_data[$item] =  $this->buildSyncData($request);
                        }
                        $data->rooms()->sync($room_ids_arr_data);
                    } else {
                        $deleteIds = $this->mestRoomRepository->deleteByMediStockId($data->id);
                        event(new DeleteIndex($deleteIds, $this->params->mestRoomName));
                    }
                    DB::connection('oracle_his')->commit();
                    //Cập nhật trong elastic
                    $records = $this->mestRoomRepository->getByMediStockIdAndRoomIds($id, $room_ids_arr ?? []);
                    foreach ($records as $key => $item) {
                        event(new InsertMestRoomIndex($item, $this->params->mestRoomName));
                    }
                } catch (\Throwable $e) {
                    DB::connection('oracle_his')->rollBack();
                    return  writeAndThrowError(config('params')['db_service']['error']['transaction'], $e);
                }
            }
            event(new DeleteCache($this->params->mestRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['mest_room'], $e);
        }
    }
}