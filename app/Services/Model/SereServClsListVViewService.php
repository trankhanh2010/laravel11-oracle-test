<?php

namespace App\Services\Model;

use App\DTOs\SereServClsListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServClsListVView\InsertSereServClsListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServClsListVViewRepository;

class SereServClsListVViewService
{
    protected $sereServClsListVViewRepository;
    protected $params;
    public function __construct(SereServClsListVViewRepository $sereServClsListVViewRepository)
    {
        $this->sereServClsListVViewRepository = $sereServClsListVViewRepository;
    }
    public function withParams(SereServClsListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseGetAll()
    {
        try {
            $data = Cache::remember($this->params->sereServClsListVViewName .$this->params->param, 3600, function () {
                $data = $this->sereServClsListVViewRepository->applyJoins();
                $data = $this->sereServClsListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
                $data = $this->sereServClsListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
                $data = $this->sereServClsListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
                $data = $this->sereServClsListVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
                $data = $this->sereServClsListVViewRepository->applyReportTypeCodeFilter($data, $this->params->reportTypeCode);
                $data = $this->sereServClsListVViewRepository->applyIntructionTimeFilter($data, $this->params->intructionTimeFrom, $this->params->intructionTimeTo);
                $data = $this->sereServClsListVViewRepository->applyTabFilter($data, $this->params->tab);

                $count = $data->count();
                $data = $this->sereServClsListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->sereServClsListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                // Group theo field
                $data = $this->sereServClsListVViewRepository->applyGroupByField($data, $this->params->groupBy, $this->params->intructionTimeFrom, $this->params->intructionTimeTo, $this->params->reportTypeCode);
                return ['data' => $data, 'count' => $count];
            });

            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_cls_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServClsListVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->sereServClsListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->sereServClsListVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_cls_list_v_view'], $e);
        }
    }

    // public function createSereServClsListVView($request)
    // {
    //     try {
    //         $data = $this->sereServClsListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServClsListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServClsListVViewIndex($data, $this->params->sereServClsListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_cls_list_v_view'], $e);
    //     }
    // }

    // public function updateSereServClsListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServClsListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServClsListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServClsListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServClsListVViewIndex($data, $this->params->sereServClsListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_cls_list_v_view'], $e);
    //     }
    // }

    // public function deleteSereServClsListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServClsListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServClsListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServClsListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServClsListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_cls_list_v_view'], $e);
    //     }
    // }
}
