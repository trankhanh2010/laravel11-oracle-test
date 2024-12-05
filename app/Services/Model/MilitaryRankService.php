<?php

namespace App\Services\Model;

use App\DTOs\MilitaryRankDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MilitaryRank\InsertMilitaryRankIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MilitaryRankRepository;

class MilitaryRankService 
{
    protected $militaryRankRepository;
    protected $params;
    public function __construct(MilitaryRankRepository $militaryRankRepository)
    {
        $this->militaryRankRepository = $militaryRankRepository;
    }
    public function withParams(MilitaryRankDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->militaryRankRepository->applyJoins();
            $data = $this->militaryRankRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->militaryRankRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->militaryRankRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->militaryRankRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['military_rank'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->militaryRankName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->militaryRankRepository->applyJoins();
                $data = $this->militaryRankRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->militaryRankRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->militaryRankRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['military_rank'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->militaryRankName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->militaryRankRepository->applyJoins()
                    ->where('his_military_rank.id', $id);
                $data = $this->militaryRankRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['military_rank'], $e);
        }
    }
    public function createMilitaryRank($request)
    {
        try {
            $data = $this->militaryRankRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMilitaryRankIndex($data, $this->params->militaryRankName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->militaryRankName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['military_rank'], $e);
        }
    }

    public function updateMilitaryRank($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->militaryRankRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->militaryRankRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertMilitaryRankIndex($data, $this->params->militaryRankName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->militaryRankName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['military_rank'], $e);
        }
    }

    public function deleteMilitaryRank($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->militaryRankRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->militaryRankRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->militaryRankName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->militaryRankName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['military_rank'], $e);
        }
    }
}
