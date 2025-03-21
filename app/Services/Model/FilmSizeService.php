<?php

namespace App\Services\Model;

use App\DTOs\FilmSizeDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\FilmSize\InsertFilmSizeIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\FilmSizeRepository;

class FilmSizeService 
{
    protected $filmSizeRepository;
    protected $params;
    public function __construct(FilmSizeRepository $filmSizeRepository)
    {
        $this->filmSizeRepository = $filmSizeRepository;
    }
    public function withParams(FilmSizeDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->filmSizeRepository->applyJoins();
            $data = $this->filmSizeRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->filmSizeRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->filmSizeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->filmSizeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->filmSizeName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->filmSizeRepository->applyJoins();
                $data = $this->filmSizeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->filmSizeRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->filmSizeRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->filmSizeName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->filmSizeRepository->applyJoins()
                    ->where('his_film_size.id', $id);
                $data = $this->filmSizeRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
    public function createFilmSize($request)
    {
        try {
            $data = $this->filmSizeRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertFilmSizeIndex($data, $this->params->filmSizeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->filmSizeName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }

    public function updateFilmSize($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->filmSizeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->filmSizeRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            
            // Gọi event để thêm index vào elastic
            event(new InsertFilmSizeIndex($data, $this->params->filmSizeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->filmSizeName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }

    public function deleteFilmSize($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->filmSizeRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->filmSizeRepository->delete($data);
            
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->filmSizeName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->filmSizeName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['film_size'], $e);
        }
    }
}
