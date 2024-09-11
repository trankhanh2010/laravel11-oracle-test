<?php

namespace App\Services\Model;

use App\DTOs\BodyPartDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\BodyPart\InsertBodyPartIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\BodyPartRepository;

class BodyPartService 
{
    protected $bodyPartRepository;
    protected $params;
    public function __construct(BodyPartRepository $bodyPartRepository)
    {
        $this->bodyPartRepository = $bodyPartRepository;
    }
    public function withParams(BodyPartDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->bodyPartRepository->applyJoins();
            $data = $this->bodyPartRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->bodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->bodyPartRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->bodyPartRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->bodyPartName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->bodyPartRepository->applyJoins();
                $data = $this->bodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->bodyPartRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->bodyPartRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->bodyPartName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->bodyPartRepository->applyJoins()
                    ->where('his_body_part.id', $id);
                $data = $this->bodyPartRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }

    public function createBodyPart($request)
    {
        try {
            $data = $this->bodyPartRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bodyPartName));
            // Gọi event để thêm index vào elastic
            event(new InsertBodyPartIndex($data, $this->params->bodyPartName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }

    public function updateBodyPart($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bodyPartRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bodyPartName));
            // Gọi event để thêm index vào elastic
            event(new InsertBodyPartIndex($data, $this->params->bodyPartName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }

    public function deleteBodyPart($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->bodyPartRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->bodyPartRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->bodyPartName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->bodyPartName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['body_part'], $e);
        }
    }
}
