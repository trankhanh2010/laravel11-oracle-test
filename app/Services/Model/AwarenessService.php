<?php

namespace App\Services\Model;

use App\DTOs\AwarenessDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Awareness\InsertAwarenessIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\AwarenessRepository;

class AwarenessService 
{
    protected $awarenessRepository;
    protected $params;
    public function __construct(AwarenessRepository $awarenessRepository)
    {
        $this->awarenessRepository = $awarenessRepository;
    }
    public function withParams(AwarenessDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->awarenessRepository->applyJoins();
            $data = $this->awarenessRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->awarenessRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->awarenessRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->awarenessRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->awarenessName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function () {
                $data = $this->awarenessRepository->applyJoins();
                $data = $this->awarenessRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->awarenessRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->awarenessRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->awarenessName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id) {
                $data = $this->awarenessRepository->applyJoins()
                    ->where('his_awareness.id', $id);
                $data = $this->awarenessRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function createAwareness($request)
    {
        try {
            $data = $this->awarenessRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->awarenessName));
            // Gọi event để thêm index vào elastic
            event(new InsertAwarenessIndex($data, $this->params->awarenessName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function updateAwareness($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->awarenessRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->awarenessRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->awarenessName));
            // Gọi event để thêm index vào elastic
            event(new InsertAwarenessIndex($data, $this->params->awarenessName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }

    public function deleteAwareness($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->awarenessRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->awarenessRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->awarenessName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->awarenessName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['awareness'], $e);
        }
    }
}
