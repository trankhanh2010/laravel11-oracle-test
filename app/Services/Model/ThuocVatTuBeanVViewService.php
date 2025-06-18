<?php

namespace App\Services\Model;

use App\DTOs\ThuocVatTuBeanVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ThuocVatTuBeanVView\InsertThuocVatTuBeanVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use App\Repositories\ThuocVatTuBeanVViewRepository;

class ThuocVatTuBeanVViewService
{
    protected $thuocVatTuBeanVViewRepository;
    protected $params;
    public function __construct(ThuocVatTuBeanVViewRepository $thuocVatTuBeanVViewRepository)
    {
        $this->thuocVatTuBeanVViewRepository = $thuocVatTuBeanVViewRepository;
    }
    public function withParams(ThuocVatTuBeanVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            if ($this->params->tab == 'keDonThuocPhongKham') {
                $data = $this->thuocVatTuBeanVViewRepository->applyJoinsKeDonThuocPhongKham();
                $data = $this->thuocVatTuBeanVViewRepository->applyMediStockIdsFilter($data, $this->params->mediStockIds);
                $data = $this->thuocVatTuBeanVViewRepository->applyTypeKeDonThuocPhongKhamFilter($data, $this->params->type);
            } else {
                $data = $this->thuocVatTuBeanVViewRepository->applyJoins();
            }
            $data = $this->thuocVatTuBeanVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->thuocVatTuBeanVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $count = null;
            if ($this->params->tab == 'keDonThuocPhongKham') {
                $orderBy = [
                    'service_type_code' => 'desc',
                    'm_parent_name' => 'asc',
                ];
                $orderByJoin = ['parent_name'];
                $data = $this->thuocVatTuBeanVViewRepository->applyOrdering($data, $orderBy, $orderByJoin);
            } else {
                $data = $this->thuocVatTuBeanVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            }
            $data = $this->thuocVatTuBeanVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            if ($this->params->tab == 'keDonThuocPhongKham') {
                $groupBy = [
                    'mParentName',
                    'mTypeName',
                    'mediStockName',
                ];
                $data = $this->thuocVatTuBeanVViewRepository->applyGroupByField($data, $groupBy);
            }
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['thuoc_vat_tu_bean_v_view'], $e);
        }
    }
    private function getAllDataFromDatabase()
    {
        if ($this->params->tab == 'keDonThuocPhongKham') {
            $data = $this->thuocVatTuBeanVViewRepository->applyJoinsKeDonThuocPhongKham();
            $data = $this->thuocVatTuBeanVViewRepository->applyMediStockIdsFilter($data, $this->params->mediStockIds);
            $data = $this->thuocVatTuBeanVViewRepository->applyKeDonThuocPhongKhamFilter($data, $this->params->intructionTime);
            $data = $this->thuocVatTuBeanVViewRepository->applyTypeKeDonThuocPhongKhamFilter($data, $this->params->type);
        } else {
            $data = $this->thuocVatTuBeanVViewRepository->applyJoins();
        }
        $data = $this->thuocVatTuBeanVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $count = null;
        if ($this->params->tab == 'keDonThuocPhongKham') {
            $orderBy = [
                'service_type_code' => 'desc',
                'm_parent_name' => 'asc',
            ];
            $orderByJoin = ['parent_name'];
            $data = $this->thuocVatTuBeanVViewRepository->applyOrdering($data, $orderBy, $orderByJoin);
        } else {
            $data = $this->thuocVatTuBeanVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        }
        $data = $this->thuocVatTuBeanVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        if ($this->params->tab == 'keDonThuocPhongKham') {
            $groupBy = [
                'mParentName',
                'mTypeName',
                'mediStockName',
            ];
            $data = $this->thuocVatTuBeanVViewRepository->applyGroupByField($data, $groupBy);
        }
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->thuocVatTuBeanVViewRepository->applyJoins()
            ->where('xa_v_his_thuoc_vat_tu_bean.id', $id);
        $data = $this->thuocVatTuBeanVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->thuocVatTuBeanVViewRepository->applyIsDeleteFilter($data, $this->params->isDelete);
        $data = $data->first();
        return $data;
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['thuoc_vat_tu_bean_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['thuoc_vat_tu_bean_v_view'], $e);
        }
    }

    // public function createThuocVatTuBeanVView($request)
    // {
    //     try {
    //         $data = $this->thuocVatTuBeanVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->thuocVatTuBeanVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertThuocVatTuBeanVViewIndex($data, $this->params->thuocVatTuBeanVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['thuoc_vat_tu_bean_v_view'], $e);
    //     }
    // }

    // public function updateThuocVatTuBeanVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->thuocVatTuBeanVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->thuocVatTuBeanVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->thuocVatTuBeanVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertThuocVatTuBeanVViewIndex($data, $this->params->thuocVatTuBeanVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['thuoc_vat_tu_bean_v_view'], $e);
    //     }
    // }

    // public function deleteThuocVatTuBeanVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->thuocVatTuBeanVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->thuocVatTuBeanVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->thuocVatTuBeanVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->thuocVatTuBeanVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['thuoc_vat_tu_bean_v_view'], $e);
    //     }
    // }
}
