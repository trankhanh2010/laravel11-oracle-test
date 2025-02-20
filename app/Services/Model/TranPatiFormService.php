<?php

namespace App\Services\Model;

use App\DTOs\TranPatiFormDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TranPatiForm\InsertTranPatiFormIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\TranPatiFormRepository;

class TranPatiFormService 
{
    protected $tranPatiFormRepository;
    protected $params;
    public function __construct(TranPatiFormRepository $tranPatiFormRepository)
    {
        $this->tranPatiFormRepository = $tranPatiFormRepository;
    }
    public function withParams(TranPatiFormDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->tranPatiFormRepository->applyJoins();
            $data = $this->tranPatiFormRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->tranPatiFormRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = $data->count();
            $data = $this->tranPatiFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->tranPatiFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_form'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->tranPatiFormName . '_start_' . $this->params->start . '_limit_' . $this->params->limit . $this->params->orderByString . '_is_active_' . $this->params->isActive . '_get_all_' . $this->params->getAll, $this->params->time, function (){
                $data = $this->tranPatiFormRepository->applyJoins();
                $data = $this->tranPatiFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $count = $data->count();
                $data = $this->tranPatiFormRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->tranPatiFormRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                return ['data' => $data, 'count' => $count];
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_form'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = Cache::remember($this->params->tranPatiFormName . '_' . $id . '_is_active_' . $this->params->isActive, $this->params->time, function () use ($id){
                $data = $this->tranPatiFormRepository->applyJoins()
                    ->where('his_tran_pati_form.id', $id);
                $data = $this->tranPatiFormRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $data->first();
                return $data;
            });
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_form'], $e);
        }
    }

    public function createTranPatiForm($request)
    {
        try {
            $data = $this->tranPatiFormRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTranPatiFormIndex($data, $this->params->tranPatiFormName));
             // Gọi event để xóa cache
             event(new DeleteCache($this->params->tranPatiFormName));           
            return returnDataCreateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_form'], $e);
        }
    }

    public function updateTranPatiForm($id, $request)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->tranPatiFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->tranPatiFormRepository->update($request, $data, $this->params->time, $this->params->appModifier);

            // Gọi event để thêm index vào elastic
            event(new InsertTranPatiFormIndex($data, $this->params->tranPatiFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->tranPatiFormName));            
            return returnDataUpdateSuccess($data);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_form'], $e);
        }
    }

    public function deleteTranPatiForm($id)
    {
        if (!is_numeric($id)) {
            return returnIdError($id);
        }
        $data = $this->tranPatiFormRepository->getById($id);
        if ($data == null) {
            return returnNotRecord($id);
        }
        try {
            $data = $this->tranPatiFormRepository->delete($data);

            // Gọi event để xóa index trong elastic
            event(new DeleteIndex($data, $this->params->tranPatiFormName));
            // Gọi event để xóa cache
            event(new DeleteCache($this->params->tranPatiFormName));            
            return returnDataDeleteSuccess();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['tran_pati_form'], $e);
        }
    }
}
