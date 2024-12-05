<?php

namespace App\Services\Model;

use App\DTOs\BidDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Bid\InsertBidIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BidRepository;

class BidService 
{
    protected $bidRepository;
    protected $params;
    public function __construct(BidRepository $bidRepository)
    {
        $this->bidRepository = $bidRepository;
    }
    public function withParams(BidDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bidRepository->applyJoins();
            $data = $this->bidRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bidRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bidRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bidRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->bidName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->bidRepository->applyJoins();
                $data = $this->bidRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bidRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bidRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bidName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bidRepository->applyJoins()
                    ->where('his_bid.id', $id);
                $data = $this->bidRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid'], $e);
        }
    }

    public function createBid($request)
    {
        try {
            $data = $this->bidRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBidIndex($data, $this->params->bidName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bidName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid'], $e);
        }
    }

    public function updateBid($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bidRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bidRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertBidIndex($data, $this->params->bidName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bidName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid'], $e);
        }
    }

    public function deleteBid($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bidRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bidRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bidName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bidName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['bid'], $e);
        }
    }
}
