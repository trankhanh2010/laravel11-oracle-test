<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\YeuCauKhamClsPtttVView;
use Illuminate\Support\Facades\DB;

class YeuCauKhamClsPtttVViewRepository
{
    protected $yeuCauKhamClsPtttVView;
    public function __construct(YeuCauKhamClsPtttVView $yeuCauKhamClsPtttVView)
    {
        $this->yeuCauKhamClsPtttVView = $yeuCauKhamClsPtttVView;
    }

    public function applyJoins()
    {
        return $this->yeuCauKhamClsPtttVView
            ->select(
                '*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('yeu_cau_kham_cls_pttt_code'), 'like', '%' . $keyword . '%')
                ->orWhere(('lower(yeu_cau_kham_cls_pttt_name)'), 'like', '%' . strtolower($keyword) . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(('is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(('is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyIntructionTimeFromFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('intruction_time', '>=', $param);
            });
        }
        return $query;
    }
    public function applyIntructionTimeToFilter($query, $param)
    {
        if ($param != null) {
            return $query->where(function ($query) use ($param) {
                $query->where('intruction_time', '<=', $param);
            });
        }
        return $query;
    }
    public function applyExecuteRoomIdFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('execute_room_id'), $param);
        }
        return $query;
    }
    public function applyTreatmentTypeIdsFilter($query, $param)
    {
        if ($param != null) {
            $query->whereIn(('tdl_treatment_type_id'), $param);
        }
        return $query;
    }
    public function applyServiceReqCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('service_req_code'), $param);
        }
        return $query;
    }
    public function applyBedCodeFilter($query, $param)
    {
        if ($param != null) {
            $query->where(('bed_code'), $param);
        }
        return $query;
    }
    public function applyTrangThaiFilter($query, $param)
    {
        switch ($param) {
            case 'tatCa':
                return $query;
            case 'chuaKetThuc':
                return $query->whereNull('finish_time');
            case 'chuaXuLy':
                return $query->where('service_req_stt_code', '01');
            case 'dangXuLy':
                return $query->where('service_req_stt_code', '02');
            case 'ketThuc':
                return $query->whereNotNull('finish_time');
            case 'goiNho':
                return $query->where('call_count', '>=', 1);
            default:
                return $query;
        }
    }
    public function applyTrangThaiVienPhiFilter($query, $param)
    {
        switch ($param) {
            case 'tatCa':
                return $query;
            case 'dangNoVienPhi':
                return $query->where(function ($q) {
                    $q->where('is_not_in_debt', 0)
                    ->orWhereNull('is_not_in_debt');
                });
            case 'khongNoVienPhi':
                return $query->where('is_not_in_debt', 1);
            default:
                return $query;
        }
    }

    public function applyTrangThaiKeThuocFilter($query, $param)
    {
        switch ($param) {
            case 'tatCa':
                return $query;
            case 'chuaKeDuThuocVTTH':
                return $query->where(function ($q) {
                    $q->where('is_enough_subclinical_pres', 0)
                    ->orWhereNull('is_enough_subclinical_pres');
                });
            case 'daKeDuThuocVTTH':
                return $query->where('is_enough_subclinical_pres', 1);
            default:
                return $query;
        }
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->yeuCauKhamClsPtttVView->find($id);
    }
}
