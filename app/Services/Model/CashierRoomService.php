<?php

namespace App\Services\Model;

use App\DTOs\CashierRoomDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\CashierRoom\InsertCashierRoomIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\CashierRoomRepository;

class CashierRoomService 
{
    protected $cashierRoomRepository;
    protected $params;
    public function __construct(CashierRoomRepository $cashierRoomRepository)
    {
        $this->cashierRoomRepository = $cashierRoomRepository;
    }
    public function withParams(CashierRoomDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->cashierRoomRepository->applyJoins();
            $data = $this->cashierRoomRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->cashierRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->cashierRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->cashierRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cashier_room'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->cashierRoomName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->cashierRoomRepository->applyJoins();
                $data = $this->cashierRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->cashierRoomRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->cashierRoomRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cashier_room'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->cashierRoomName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->cashierRoomRepository->applyJoins()
                    ->where('his_cashier_room.id', $id);
                $data = $this->cashierRoomRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cashier_room'], $e);
        }
    }

    public function createCashierRoom($request)
    {
        try {
            $data = $this->cashierRoomRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->cashierRoomName));
            // Gọi event để thêm index vào elastic
            event(new InsertCashierRoomIndex($data, $this->params->cashierRoomName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cashier_room'], $e);
        }
    }

    public function updateCashierRoom($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->cashierRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->cashierRoomRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->cashierRoomName));
            // Gọi event để thêm index vào elastic
            event(new InsertCashierRoomIndex($data, $this->params->cashierRoomName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cashier_room'], $e);
        }
    }

    public function deleteCashierRoom($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->cashierRoomRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->cashierRoomRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->cashierRoomName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->cashierRoomName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['cashier_room'], $e);
        }
    }
}