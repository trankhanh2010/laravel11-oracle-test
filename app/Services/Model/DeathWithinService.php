<?php

namespace App\Services\Model;

use App\DTOs\DeathWithinDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DeathWithin\InsertDeathWithinIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\DeathWithinRepository;

class DeathWithinService 
{
    protected $deathWithinRepository;
    protected $params;
    public function __construct(DeathWithinRepository $deathWithinRepository)
    {
        $this->deathWithinRepository = $deathWithinRepository;
    }
    public function withParams(DeathWithinDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->deathWithinRepository->applyJoins();
            $data = $this->deathWithinRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->deathWithinRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->deathWithinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->deathWithinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->deathWithinName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->deathWithinRepository->applyJoins();
                $data = $this->deathWithinRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->deathWithinRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->deathWithinRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->deathWithinName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->deathWithinRepository->applyJoins()
                    ->where('his_death_within.id', $id);
                $data = $this->deathWithinRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }

    public function createDeathWithin($request)
    {
        try {
            $data = $this->deathWithinRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDeathWithinIndex($data, $this->params->deathWithinName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->deathWithinName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }

    public function updateDeathWithin($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->deathWithinRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->deathWithinRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertDeathWithinIndex($data, $this->params->deathWithinName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->deathWithinName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }

    public function deleteDeathWithin($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->deathWithinRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->deathWithinRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->deathWithinName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->deathWithinName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['death_within'], $e);
        }
    }
}
