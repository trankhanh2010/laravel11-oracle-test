<?php

namespace App\Services\Model;

use App\DTOs\PtttTableDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\PtttTable\InsertPtttTableIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\PtttTableRepository;

class PtttTableService 
{
    protected $ptttTableRepository;
    protected $params;
    public function __construct(PtttTableRepository $ptttTableRepository)
    {
        $this->ptttTableRepository = $ptttTableRepository;
    }
    public function withParams(PtttTableDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->ptttTableRepository->applyJoins();
            $data = $this->ptttTableRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->ptttTableRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->ptttTableRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->ptttTableRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_table'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->ptttTableName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->ptttTableRepository->applyJoins();
                $data = $this->ptttTableRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->ptttTableRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->ptttTableRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_table'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->ptttTableName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->ptttTableRepository->applyJoins()
                    ->where('his_pttt_table.id', $id);
                $data = $this->ptttTableRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_table'], $e);
        }
    }

    public function createPtttTable($request)
    {
        try {
            $data = $this->ptttTableRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttTableName));
            // Gọi event để thêm index vào elastic
            event(new InsertPtttTableIndex($data, $this->params->ptttTableName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_table'], $e);
        }
    }

    public function updatePtttTable($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttTableRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttTableRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttTableName));
            // Gọi event để thêm index vào elastic
            event(new InsertPtttTableIndex($data, $this->params->ptttTableName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_table'], $e);
        }
    }

    public function deletePtttTable($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->ptttTableRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->ptttTableRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->ptttTableName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->ptttTableName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['pttt_table'], $e);
        }
    }
}
