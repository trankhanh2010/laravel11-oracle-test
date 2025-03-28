<?php

namespace App\Services\Model;

use App\DTOs\SereServTeinChartsVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServTeinChartsVView\InsertSereServTeinChartsVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServTeinChartsVViewRepository;
use Illuminate\Support\Facades\Redis;

class SereServTeinChartsVViewService
{
    protected $sereServTeinChartsVViewRepository;
    protected $params;
    public function __construct(SereServTeinChartsVViewRepository $sereServTeinChartsVViewRepository)
    {
        $this->sereServTeinChartsVViewRepository = $sereServTeinChartsVViewRepository;
    }
    public function withParams(SereServTeinChartsVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseGetAll()
    {
        try {
            $cacheKey = $this->params->sereServTeinChartsVViewName .'_'. $this->params->param;
            $cacheKeySet = "cache_keys:" . $this->params->sereServTeinChartsVViewName; // Set để lưu danh sách key
            $data = Cache::remember($cacheKey, 3600, function () {
                $data = $this->sereServTeinChartsVViewRepository->applyJoins();
                $data = $this->sereServTeinChartsVViewRepository->applyWithParam($data);
                $data = $this->sereServTeinChartsVViewRepository->applyIsActiveFilter($data, 1);
                $data = $this->sereServTeinChartsVViewRepository->applyIsDeleteFilter($data, 0);
                $data = $this->sereServTeinChartsVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
                $data = $this->sereServTeinChartsVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
                $data = $this->sereServTeinChartsVViewRepository->applyServiceCodesFilter($data, $this->params->serviceCodes);
                $data = $this->sereServTeinChartsVViewRepository->applyReportTypeCodeFilter($data, $this->params->reportTypeCode);
                $data = $this->sereServTeinChartsVViewRepository->applyIntructionTimeFilter($data, $this->params->intructionTimeFrom, $this->params->intructionTimeTo);
                $data = $this->sereServTeinChartsVViewRepository->applyTabFilter($data, $this->params->tab);

                // $count = $data->count();
                $count = null;
                $data = $this->sereServTeinChartsVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
                $data = $this->sereServTeinChartsVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
                // Group theo field
                $data = $this->sereServTeinChartsVViewRepository->applyGroupByField($data,$this->params->groupBy);
                return ['data' => $data, 'count' => $count];
            });
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_charts_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            $data = $this->sereServTeinChartsVViewRepository->applyJoins()
                ->where('id', $id);
            $data = $this->sereServTeinChartsVViewRepository->applyIsActiveFilter($data, 1);
            $data = $this->sereServTeinChartsVViewRepository->applyIsDeleteFilter($data, 0);
            $data = $data->first();
            return $data;
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_charts_v_view'], $e);
        }
    }

    // public function createSereServTeinChartsVView($request)
    // {
    //     try {
    //         $data = $this->sereServTeinChartsVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinChartsVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinChartsVViewIndex($data, $this->params->sereServTeinChartsVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_charts_v_view'], $e);
    //     }
    // }

    // public function updateSereServTeinChartsVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinChartsVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinChartsVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinChartsVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertSereServTeinChartsVViewIndex($data, $this->params->sereServTeinChartsVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_charts_v_view'], $e);
    //     }
    // }

    // public function deleteSereServTeinChartsVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->sereServTeinChartsVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->sereServTeinChartsVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->sereServTeinChartsVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->sereServTeinChartsVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['sere_serv_tein_charts_v_view'], $e);
    //     }
    // }
}
