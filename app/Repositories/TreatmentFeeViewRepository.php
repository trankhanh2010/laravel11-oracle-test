<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TreatmentFeeView;
use Illuminate\Support\Facades\DB;

class TreatmentFeeViewRepository
{
    protected $treatmentFeeView;
    public function __construct(TreatmentFeeView $treatmentFeeView)
    {
        $this->treatmentFeeView = $treatmentFeeView;
    }

    public function applyJoins()
    {
        return $this->treatmentFeeView
            ->select(
            'V_HIS_TREATMENT_FEE.ID',
            'V_HIS_TREATMENT_FEE.CREATE_TIME',
            'V_HIS_TREATMENT_FEE.MODIFY_TIME',
            'V_HIS_TREATMENT_FEE.CREATOR',
            'V_HIS_TREATMENT_FEE.MODIFIER',
            'V_HIS_TREATMENT_FEE.APP_CREATOR',
            'V_HIS_TREATMENT_FEE.APP_MODIFIER',
            'V_HIS_TREATMENT_FEE.IS_ACTIVE',
            'V_HIS_TREATMENT_FEE.IS_DELETE',
            'V_HIS_TREATMENT_FEE.TREATMENT_CODE',
            'V_HIS_TREATMENT_FEE.PATIENT_ID',
            'V_HIS_TREATMENT_FEE.BRANCH_ID',
            'V_HIS_TREATMENT_FEE.IS_PAUSE',
            'V_HIS_TREATMENT_FEE.IS_LOCK_HEIN',
            'V_HIS_TREATMENT_FEE.ICD_CODE',
            'V_HIS_TREATMENT_FEE.ICD_NAME',
            'V_HIS_TREATMENT_FEE.ICD_SUB_CODE',
            'V_HIS_TREATMENT_FEE.ICD_TEXT',
            'V_HIS_TREATMENT_FEE.FEE_LOCK_TIME',
            'V_HIS_TREATMENT_FEE.FEE_LOCK_ORDER',
            'V_HIS_TREATMENT_FEE.FEE_LOCK_ROOM_ID',
            'V_HIS_TREATMENT_FEE.FEE_LOCK_DEPARTMENT_ID',
            'V_HIS_TREATMENT_FEE.IN_TIME',
            'V_HIS_TREATMENT_FEE.IN_DATE',
            'V_HIS_TREATMENT_FEE.OUT_TIME',
            'V_HIS_TREATMENT_FEE.HOSPITALIZATION_REASON',
            'V_HIS_TREATMENT_FEE.DOCTOR_LOGINNAME',
            'V_HIS_TREATMENT_FEE.DOCTOR_USERNAME',
            'V_HIS_TREATMENT_FEE.END_LOGINNAME',
            'V_HIS_TREATMENT_FEE.END_USERNAME',
            'V_HIS_TREATMENT_FEE.END_ROOM_ID',
            'V_HIS_TREATMENT_FEE.END_DEPARTMENT_ID',
            'V_HIS_TREATMENT_FEE.END_CODE',
            'V_HIS_TREATMENT_FEE.TREATMENT_DAY_COUNT',
            'V_HIS_TREATMENT_FEE.TREATMENT_RESULT_ID',
            'V_HIS_TREATMENT_FEE.TREATMENT_END_TYPE_ID',
            'V_HIS_TREATMENT_FEE.APPOINTMENT_TIME',
            'V_HIS_TREATMENT_FEE.OUT_DATE',
            'V_HIS_TREATMENT_FEE.TDL_HEIN_CARD_NUMBER',
            'V_HIS_TREATMENT_FEE.CLINICAL_NOTE',
            'V_HIS_TREATMENT_FEE.TDL_FIRST_EXAM_ROOM_ID',
            'V_HIS_TREATMENT_FEE.TDL_TREATMENT_TYPE_ID',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_TYPE_ID',
            'V_HIS_TREATMENT_FEE.TDL_HEIN_MEDI_ORG_CODE',
            'V_HIS_TREATMENT_FEE.TDL_HEIN_MEDI_ORG_NAME',
            'V_HIS_TREATMENT_FEE.XML4210_URL',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_CODE',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_FIRST_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_LAST_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_DOB',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_IS_HAS_NOT_DAY_DOB',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_AVATAR_URL',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_ADDRESS',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_GENDER_ID',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_GENDER_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_CAREER_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_DISTRICT_CODE',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_PROVINCE_CODE',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_COMMUNE_CODE',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_NATIONAL_NAME',
            'V_HIS_TREATMENT_FEE.APPOINTMENT_EXAM_ROOM_IDS',
            'V_HIS_TREATMENT_FEE.DEPARTMENT_IDS',
            'V_HIS_TREATMENT_FEE.LAST_DEPARTMENT_ID',
            'V_HIS_TREATMENT_FEE.PROVISIONAL_DIAGNOSIS',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_PHONE',
            'V_HIS_TREATMENT_FEE.IS_SYNC_EMR',
            'V_HIS_TREATMENT_FEE.XML4210_RESULT',
            'V_HIS_TREATMENT_FEE.COLLINEAR_XML4210_URL',
            'V_HIS_TREATMENT_FEE.COLLINEAR_XML4210_RESULT',
            'V_HIS_TREATMENT_FEE.TDL_HEIN_CARD_FROM_TIME',
            'V_HIS_TREATMENT_FEE.TDL_HEIN_CARD_TO_TIME',
            'V_HIS_TREATMENT_FEE.APPOINTMENT_DATE',
            'V_HIS_TREATMENT_FEE.VIR_IN_MONTH',
            'V_HIS_TREATMENT_FEE.VIR_OUT_MONTH',
            'V_HIS_TREATMENT_FEE.VIR_IN_YEAR',
            'V_HIS_TREATMENT_FEE.VIR_OUT_YEAR',
            'V_HIS_TREATMENT_FEE.FEE_LOCK_LOGINNAME',
            'V_HIS_TREATMENT_FEE.FEE_LOCK_USERNAME',
            'V_HIS_TREATMENT_FEE.APPOINTMENT_EXAM_SERVICE_ID',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_CCCD_NUMBER',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_CCCD_DATE',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_RELATIVE_MOBILE',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_NATIONAL_CODE',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_PROVINCE_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_DISTRICT_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_COMMUNE_NAME',
            'V_HIS_TREATMENT_FEE.IS_BHYT_HOLDED',
            'V_HIS_TREATMENT_FEE.SHOW_ICD_CODE',
            'V_HIS_TREATMENT_FEE.SHOW_ICD_NAME',
            'V_HIS_TREATMENT_FEE.SHOW_ICD_SUB_CODE',
            'V_HIS_TREATMENT_FEE.SHOW_ICD_TEXT',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_UNSIGNED_NAME',
            'V_HIS_TREATMENT_FEE.TDL_PATIENT_ETHNIC_NAME',
            'V_HIS_TREATMENT_FEE.IS_TUBERCULOSIS',
            'V_HIS_TREATMENT_FEE.STORE_BORDEREAU_CODE',
            'V_HIS_TREATMENT_FEE.HEIN_LOCK_TIME',
            'V_HIS_TREATMENT_FEE.RECEPTION_FORM',
            'V_HIS_TREATMENT_FEE.HOSPITAL_DIRECTOR_LOGINNAME',
            'V_HIS_TREATMENT_FEE.HOSPITAL_DIRECTOR_USERNAME',
            'V_HIS_TREATMENT_FEE.HAS_CARD',
            'V_HIS_TREATMENT_FEE.TOTAL_BILL_AMOUNT',
            'V_HIS_TREATMENT_FEE.TOTAL_BILL_OTHER_AMOUNT',
            'V_HIS_TREATMENT_FEE.TOTAL_BILL_TRANSFER_AMOUNT',
            'V_HIS_TREATMENT_FEE.TOTAL_BILL_EXEMPTION',
            'V_HIS_TREATMENT_FEE.TOTAL_BILL_FUND',
            'V_HIS_TREATMENT_FEE.TOTAL_DEPOSIT_AMOUNT',
            'V_HIS_TREATMENT_FEE.TOTAL_REPAY_AMOUNT',
            'V_HIS_TREATMENT_FEE.TOTAL_PRICE',
            'V_HIS_TREATMENT_FEE.TOTAL_HEIN_PRICE',
            'V_HIS_TREATMENT_FEE.TOTAL_OTHER_COPAID_PRICE',
            'V_HIS_TREATMENT_FEE.TOTAL_PATIENT_PRICE',
            'V_HIS_TREATMENT_FEE.TOTAL_DISCOUNT',
            'V_HIS_TREATMENT_FEE.TOTAL_PRICE_EXPEND',
            'V_HIS_TREATMENT_FEE.COUNT_HEIN_APPROVAL',
            'V_HIS_TREATMENT_FEE.TOTAL_DEBT_AMOUNT',
            'V_HIS_TREATMENT_FEE.TOTAL_PATIENT_PRICE_BHYT',
            'V_HIS_TREATMENT_FEE.TOTAL_OTHER_SOURCE_PRICE',
            'V_HIS_TREATMENT_FEE.LAST_DEPOSIT_TIME',
            'V_HIS_TREATMENT_FEE.TOTAL_SERVICE_DEPOSIT_AMOUNT',
            'V_HIS_TREATMENT_FEE.TDL_TREATMENT_TYPE_CODE',
            'V_HIS_TREATMENT_FEE.TDL_TREATMENT_TYPE_NAME',
            'V_HIS_TREATMENT_FEE.HEIN_TREATMENT_TYPE_CODE',
            'V_HIS_TREATMENT_FEE.LOCKING_AMOUNT',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_fee.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_fee.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_fee.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTdlTreatmentTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_treatment_fee.tdl_treatment_type_id'), $ids);
        }
        return $query;
    }
    public function applyTdlPatientTypeIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('v_his_treatment_fee.tdl_patient_type_id'), $ids);
        }
        return $query;
    }
    public function applyBranchIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_fee.branch_id'), $id);
        }
        return $query;
    }
    public function applyInDateFromFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_fee.in_date'), '>=', $param);
        }
        return $query;
    }
    public function applyInDateToFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('v_his_treatment_fee.in_date'), '<=', $param);
        }
        return $query;
    }
    public function applyIsApproveStoreFilter($query, $param)
    {
        if($param !== null){
            if ($param) {
                $query->whereNotNull(DB::connection('oracle_his')->raw('v_his_treatment_fee.approval_store_stt_id'));
            }else {
                $query->whereNull(DB::connection('oracle_his')->raw('v_his_treatment_fee.approval_store_stt_id'));
            }
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('v_his_treatment_fee.' . $key, $item);
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
        return $this->treatmentFeeView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->treatmentFeeView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'treatment_fee_view_code' => $request->treatment_fee_view_code,
    //         'treatment_fee_view_name' => $request->treatment_fee_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'treatment_fee_view_code' => $request->treatment_fee_view_code,
    //         'treatment_fee_view_name' => $request->treatment_fee_view_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('v_his_treatment_fee.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('v_his_treatment_fee.id');
            $maxId = $this->applyJoins()->max('v_his_treatment_fee.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('treatment_fee_view', 'v_his_treatment_fee', $startId, $endId, $batchSize);
            }
        }
    }
}