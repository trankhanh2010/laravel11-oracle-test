<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TreatmentFeeDetailVView;
use Illuminate\Support\Facades\DB;

class TreatmentFeeDetailVViewRepository
{
    protected $treatmentFeeDetailVView;
    public function __construct(TreatmentFeeDetailVView $treatmentFeeDetailVView)
    {
        $this->treatmentFeeDetailVView = $treatmentFeeDetailVView;
    }

    public function applyJoins()
    {
        return $this->treatmentFeeDetailVView
            ->select(
                'xa_v_his_treatment_fee_detail.*'
            )
            ->addSelect(DB::connection('oracle_his')->raw('(total_deposit_amount - total_repay_amount - total_bill_transfer_amount - total_bill_fund - total_bill_exemption + total_bill_amount + locking_amount) as da_thu')) // Đã thu là tiền thực thu từ bệnh nhân nên mới trừ quỹ ra, trừ ra chiết khấu
            ->addSelect(DB::connection('oracle_his')->raw('(total_deposit_amount - total_service_deposit_amount) as tam_ung'))
            ->addSelect(DB::connection('oracle_his')->raw('(total_patient_price - (total_deposit_amount - total_repay_amount - total_bill_transfer_amount + total_bill_amount + locking_amount)) as fee'))
            ->addSelect(DB::connection('oracle_his')->raw('((total_deposit_amount - total_repay_amount - total_bill_transfer_amount - total_bill_fund - total_bill_exemption + total_bill_amount + locking_amount) - total_patient_price - locking_amount - total_bill_fund + total_bill_exemption) as tien_hoan_ung_toi_da')) // Tiền tối đa hiện trên form lúc hoàn ứng max tối đa = Tiền đã thu - Tiền bệnh nhân phải thanh toán - Tiền đã nộp (tạm khóa) + Tiền thu quỹ + Tiền chiết khấu
            ->addSelect(DB::connection('oracle_his')->raw('((total_deposit_amount - total_service_deposit_amount) + total_service_deposit_amount - total_repay_amount) as hien_du')) // hiện dư = (tạm ứng + tạm ứng dv - hoàn ứng)
            ;
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(('loginname'), 'like', $keyword . '%');
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
    public function applyTreatmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(('id'), $id);
        }
        return $query;
    }
    public function applyTreatmentCodeFilter($query, $code)
    {
        if ($code !== null) {
            $query->where(('treatment_code'), $code);
        }
        return $query;
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
    public function themMucHuongBHYT($item): object
    {
        // Kiểm tra nếu $item là đối tượng và có các trường cần thiết
        $maThe = isset($item->tdl_hein_card_number) ? $item->tdl_hein_card_number : null;
        $tongChiPhi = isset($item->total_price) ? $item->total_price : null;
    
        // Cập nhật trường mucHuongBhyt
        $item->mucHuongBhyt = getMucHuongBHYT($maThe, $tongChiPhi);
    
        return $item;
    }
    
    public function getById($id)
    {
        return $this->treatmentFeeDetailVView->find($id);
    }

}