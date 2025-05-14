<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ExpMest;
use Illuminate\Support\Facades\DB;

class ExpMestRepository
{
    protected $expMest;
    public function __construct(ExpMest $expMest)
    {
        $this->expMest = $expMest;
    }

    public function applyJoins()
    {
        return $this->expMest
            ->select(
                'ID',
                'CREATE_TIME',
                'MODIFY_TIME',
                'CREATOR',
                'MODIFIER',
                'APP_CREATOR',
                'APP_MODIFIER',
                'IS_ACTIVE',
                'IS_DELETE',
                'EXP_MEST_CODE',
                'EXP_MEST_TYPE_ID',
                'EXP_MEST_STT_ID',
                'MEDI_STOCK_ID',
                'REQ_LOGINNAME',
                'REQ_USERNAME',
                'REQ_ROOM_ID',
                'REQ_DEPARTMENT_ID',
                'CREATE_DATE',
                'LAST_EXP_LOGINNAME',
                'LAST_EXP_USERNAME',
                'LAST_EXP_TIME',
                'FINISH_TIME',
                'FINISH_DATE',
                'SERVICE_REQ_ID',
                'TDL_TOTAL_PRICE',
                'TDL_SERVICE_REQ_CODE',
                'TDL_INTRUCTION_TIME',
                'TDL_INTRUCTION_DATE',
                'TDL_TREATMENT_ID',
                'TDL_TREATMENT_CODE',
                'IS_EXPORT_EQUAL_APPROVE',
                'TDL_PATIENT_ID',
                'TDL_PATIENT_CODE',
                'TDL_PATIENT_NAME',
                'TDL_PATIENT_FIRST_NAME',
                'TDL_PATIENT_LAST_NAME',
                'TDL_PATIENT_DOB',
                'TDL_PATIENT_IS_HAS_NOT_DAY_DOB',
                'TDL_PATIENT_ADDRESS',
                'TDL_PATIENT_GENDER_ID',
                'TDL_PATIENT_GENDER_NAME',
                'TDL_PATIENT_TYPE_ID',
                'TDL_HEIN_CARD_NUMBER',
                'EXP_MEST_SUB_CODE',
                'LAST_APPROVAL_LOGINNAME',
                'LAST_APPROVAL_USERNAME',
                'LAST_APPROVAL_TIME',
                'LAST_APPROVAL_DATE',
                'TDL_PATIENT_NATIONAL_NAME',
                'VIR_CREATE_MONTH',
                'ICD_CODE',
                'ICD_NAME',
                'EXP_MEST_SUB_CODE_2',
                'VIR_CREATE_YEAR',
                'VIR_HEIN_CARD_PREFIX',
                'PRIORITY',
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'exp_blty_services',
            'exp_mest_bloods',
            'exp_mest_blty_reqs',
            'exp_mest_materials',
            'exp_mest_maty_reqs',
            'exp_mest_medicines',
            'exp_mest_mety_reqs',
            'exp_mest_users',
            'sere_serv_teins',
            'transaction_exps',
            'vitamin_as'
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTdlTreatmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest.tdl_treatment_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_exp_mest.' . $key, $item);
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
        return $this->expMest->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->expMest::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'exp_mest_code' => $request->exp_mest_code,
    //         'exp_mest_name' => $request->exp_mest_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'exp_mest_code' => $request->exp_mest_code,
    //         'exp_mest_name' => $request->exp_mest_name,
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
            $data = $this->applyJoins()->where('his_exp_mest.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_exp_mest.id');
            $maxId = $this->applyJoins()->max('his_exp_mest.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('exp_mest', 'his_exp_mest', $startId, $endId, $batchSize);
            }
        }
    }
}