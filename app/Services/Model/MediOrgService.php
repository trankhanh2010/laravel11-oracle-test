<?php

namespace App\Services\Model;

use App\DTOs\MediOrgDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediOrg\InsertMediOrgIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\MediOrgRepository;

class MediOrgService 
{
    protected $mediOrgRepository;
    protected $params;
    public function __construct(MediOrgRepository $mediOrgRepository)
    {
        $this->mediOrgRepository = $mediOrgRepository;
    }
    public function withParams(MediOrgDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->mediOrgRepository->applyJoins();
            $data = $this->mediOrgRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->mediOrgRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->mediOrgRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->mediOrgRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_org'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->mediOrgName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->mediOrgRepository->applyJoins();
                $data = $this->mediOrgRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->mediOrgRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->mediOrgRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_org'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->mediOrgName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->mediOrgRepository->applyJoins()
                    ->where('his_medi_org.id', $id);
                $data = $this->mediOrgRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_org'], $e);
        }
    }

    public function createMediOrg($request)
    {
        try {
            $data = $this->mediOrgRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediOrgName));
            // Gọi event để thêm index vào elastic
            event(new InsertMediOrgIndex($data, $this->params->mediOrgName));
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_org'], $e);
        }
    }

    public function updateMediOrg($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediOrgRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediOrgRepository->update($request, $data, $this->params->time, $this->params->appModifier);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediOrgName));
            // Gọi event để thêm index vào elastic
            event(new InsertMediOrgIndex($data, $this->params->mediOrgName));
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_org'], $e);
        }
    }

    public function deleteMediOrg($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->mediOrgRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->mediOrgRepository->delete($data);
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->mediOrgName));
            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->mediOrgName));
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['medi_org'], $e);
        }
    }
}
