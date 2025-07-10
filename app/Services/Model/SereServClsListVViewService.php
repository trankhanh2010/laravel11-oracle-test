<?php

namespace App\Services\Model;

use App\DTOs\SereServClsListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\SereServClsListVView\InsertSereServClsListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\SereServClsListVViewRepository;
use Illuminate\Support\Facades\Redis;

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
    private function getAllDataFromDatabase()
    {
        $data = $this->sereServClsListVViewRepository->applyJoins($this->params->reportTypeCode);
        $data = $this->sereServClsListVViewRepository->applyWithParam($data, $this->params->tab, $this->params->serviceCodes, $this->params->groupBy);
        $data = $this->sereServClsListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServClsListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServClsListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServClsListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServClsListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $this->sereServClsListVViewRepository->applyServiceTypeCodesFilter($data, $this->params->serviceTypeCodes);
        $data = $this->sereServClsListVViewRepository->applyServiceCodesFilter($data, $this->params->serviceCodes);
        $data = $this->sereServClsListVViewRepository->applyReportTypeCodeFilter($data, $this->params->reportTypeCode);
        $data = $this->sereServClsListVViewRepository->applyIntructionTimeFilter($data, $this->params->intructionTimeFrom, $this->params->intructionTimeTo);
        $data = $this->sereServClsListVViewRepository->applyTabFilter($data, $this->params->tab);

        if ($this->params->tab == 'CLS') {
            $count = null;
        } else {
            $count = $data->count();
        }
        $data = $this->sereServClsListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServClsListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);

        // Group theo field
        $data = $this->sereServClsListVViewRepository->applyGroupByField(
            $data,
            $this->params->groupBy,
            $this->params->intructionTimeFrom,
            $this->params->intructionTimeTo,
            $this->params->reportTypeCode,
            $this->params->tab,
            $this->params->serviceCodes,
        );

        // **Nén dữ liệu trước khi lưu cache**
        return ['data' => $data, 'count' => $count];
    }
    private function getAllDataResultClsFromDatabase()
    {
        $data = $this->sereServClsListVViewRepository->applyJoins($this->params->reportTypeCode, $this->params->tab);
        $data = $this->sereServClsListVViewRepository->applyWithParam($data, $this->params->tab, $this->params->serviceCodes, $this->params->groupBy);
        $data = $this->sereServClsListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServClsListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServClsListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServClsListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $this->sereServClsListVViewRepository->applyPatientCodeFilter($data, $this->params->patientCode);
        $data = $this->sereServClsListVViewRepository->applyTabFilter($data, $this->params->tab);

        $count = $data->count();
        $data = $this->sereServClsListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->sereServClsListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);

        // Group theo field
        $data = $this->sereServClsListVViewRepository->applyGroupByFieldResultCls($data,$this->params->groupBy,);

        // **Nén dữ liệu trước khi lưu cache**
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->sereServClsListVViewRepository->applyJoins($this->params->reportTypeCode)
            ->where('id', $id);
        $data = $this->sereServClsListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->sereServClsListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $this->sereServClsListVViewRepository->applyIsNoExecuteFilter($data);
        $data = $this->sereServClsListVViewRepository->applyServiceReqIsNoExecuteFilter($data);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataFromDatabase();
            } else {
                $cacheKey = $this->params->sereServClsListVViewName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->sereServClsListVViewName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, 3600, function () {
                    // **Nén dữ liệu trước khi lưu cache**
                    return base64_encode(gzcompress(serialize($this->getAllDataFromDatabase())));
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                // **Giải nén khi lấy dữ liệu từ cache**
                if ($data && is_string($data)) {
                    $decompressedData = @gzuncompress(base64_decode($data));
                    $data = $decompressedData !== false ? unserialize($decompressedData) : ['data' => [], 'count' => 0];
                }

                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_cls_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAllResultCls()
    {
        try {
            // Nếu không lưu cache
            if ($this->params->noCache) {
                return $this->getAllDataResultClsFromDatabase();
            } else {
                $cacheKey = $this->params->sereServClsListVViewName . '_' . $this->params->param;
                $cacheKeySet = "cache_keys:" . $this->params->sereServClsListVViewName; // Set để lưu danh sách key

                $data = Cache::remember($cacheKey, 60, function () {
                    // **Nén dữ liệu trước khi lưu cache**
                    return base64_encode(gzcompress(serialize($this->getAllDataResultClsFromDatabase())));
                });
                // Lưu key vào Redis Set để dễ xóa sau này
                Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
                // **Giải nén khi lấy dữ liệu từ cache**
                if ($data && is_string($data)) {
                    $decompressedData = @gzuncompress(base64_decode($data));
                    $data = $decompressedData !== false ? unserialize($decompressedData) : ['data' => [], 'count' => 0];
                }

                return $data;
            }
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['sere_serv_cls_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
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
