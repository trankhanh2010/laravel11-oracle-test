<?php

namespace App\Services\Model;

use App\DTOs\PtttGroupDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PtttGroup\InsertPtttGroupIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PtttGroupRepository;

class PtttGroupService 
{
    protected $ptttGroupRepository;
    protected $params;
    public function __construct(PtttGroupRepository $ptttGroupRepository)
    {
        $this->ptttGroupRepository = $ptttGroupRepository;
    }
    public function withParams(PtttGroupDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->ptttGroupRepository->applyJoins();
            $data = $this->ptttGroupRepository->applyWith($data);
            $data = $this->ptttGroupRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ptttGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->ptttGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ptttGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_group'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->ptttGroupName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->ptttGroupRepository->applyJoins();
                $data = $this->ptttGroupRepository->applyWith($data);
                $data = $this->ptttGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->ptttGroupRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->ptttGroupRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_group'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->ptttGroupName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->ptttGroupRepository->applyJoins()
                    ->where('his_pttt_group.id', $id);
                $data = $this->ptttGroupRepository->applyWith($data);
                $data = $this->ptttGroupRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_group'], $e);
        }
    }

    public function createPtttGroup($request)
    {
        try {
            $data = $this->ptttGroupRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttGroupName));
            // Gọi event để thêm index vào elastic
            event(new InsertPtttGroupIndex($data, $this->params->ptttGroupName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_group'], $e);
        }
    }

    public function updatePtttGroup($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttGroupRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttGroupName));
            // Gọi event để thêm index vào elastic
            event(new InsertPtttGroupIndex($data, $this->params->ptttGroupName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_group'], $e);
        }
    }

    public function deletePtttGroup($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttGroupRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttGroupRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttGroupName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->ptttGroupName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_group'], $e);
        }
    }
}
