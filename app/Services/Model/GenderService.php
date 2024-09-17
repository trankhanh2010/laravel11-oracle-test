<?php

namespace App\Services\Model;

use App\DTOs\GenderDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Gender\InsertGenderIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\GenderRepository;

class GenderService 
{
    protected $genderRepository;
    protected $params;
    public function __construct(GenderRepository $genderRepository)
    {
        $this->genderRepository = $genderRepository;
    }
    public function withParams(GenderDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->genderRepository->applyJoins();
            $data = $this->genderRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->genderRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->genderRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->genderRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->genderName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->genderRepository->applyJoins();
                $data = $this->genderRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->genderRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->genderRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->genderName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->genderRepository->applyJoins()
                    ->where('his_gender.id', $id);
                $data = $this->genderRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
    public function deleteGender($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->genderRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->genderRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->genderName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->genderName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['gender'], $e);
        }
    }
}
