<?php

namespace App\Services\Model;

use App\DTOs\EmotionlessMethodDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\EmotionlessMethod\InsertEmotionlessMethodIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\EmotionlessMethodRepository;

class EmotionlessMethodService 
{
    protected $emotionlessMethodRepository;
    protected $params;
    public function __construct(EmotionlessMethodRepository $emotionlessMethodRepository)
    {
        $this->emotionlessMethodRepository = $emotionlessMethodRepository;
    }
    public function withParams(EmotionlessMethodDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->emotionlessMethodRepository->applyJoins();
            $data = $this->emotionlessMethodRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->emotionlessMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->emotionlessMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->emotionlessMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->emotionlessMethodName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->emotionlessMethodRepository->applyJoins();
                $data = $this->emotionlessMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->emotionlessMethodRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->emotionlessMethodRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->emotionlessMethodName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->emotionlessMethodRepository->applyJoins()
                    ->where('his_emotionless_method.id', $id);
                $data = $this->emotionlessMethodRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }

    public function createEmotionlessMethod($request)
    {
        try {
            $data = $this->emotionlessMethodRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertEmotionlessMethodIndex($data, $this->params->emotionlessMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emotionlessMethodName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }

    public function updateEmotionlessMethod($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emotionlessMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emotionlessMethodRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertEmotionlessMethodIndex($data, $this->params->emotionlessMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emotionlessMethodName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }

    public function deleteEmotionlessMethod($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->emotionlessMethodRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->emotionlessMethodRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->emotionlessMethodName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->emotionlessMethodName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['emotionless_method'], $e);
        }
    }
}
