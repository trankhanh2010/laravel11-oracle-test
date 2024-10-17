<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\SereServ;
use Illuminate\Support\Facades\DB;

class SereServRepository
{
    protected $sereServ;
    public function __construct(SereServ $sereServ)
    {
        $this->sereServ = $sereServ;
    }

    public function applyJoins()
    {
        return $this->sereServ
            ->select(
                "his_sere_serv.ID",
                "his_sere_serv.CREATE_TIME",
                "his_sere_serv.MODIFY_TIME",
                "his_sere_serv.CREATOR",
                "his_sere_serv.MODIFIER",
                "his_sere_serv.APP_CREATOR",
                "his_sere_serv.APP_MODIFIER",
                "his_sere_serv.IS_ACTIVE",
                "his_sere_serv.IS_DELETE",
                "his_sere_serv.SERVICE_ID",
                "his_sere_serv.SERVICE_REQ_ID",
                "his_sere_serv.PATIENT_TYPE_ID",
                "his_sere_serv.PRIMARY_PRICE",
                "his_sere_serv.AMOUNT",
                "his_sere_serv.PRICE",
                "his_sere_serv.ORIGINAL_PRICE",
                "his_sere_serv.VAT_RATIO",
                "his_sere_serv.MEDICINE_ID",
                "his_sere_serv.EXP_MEST_MEDICINE_ID",
                "his_sere_serv.TDL_INTRUCTION_TIME",
                "his_sere_serv.TDL_INTRUCTION_DATE",
                "his_sere_serv.TDL_PATIENT_ID",
                "his_sere_serv.TDL_TREATMENT_ID",
                "his_sere_serv.TDL_TREATMENT_CODE",
                "his_sere_serv.TDL_SERVICE_CODE",
                "his_sere_serv.TDL_SERVICE_NAME",
                "his_sere_serv.TDL_HEIN_SERVICE_BHYT_NAME",
                "his_sere_serv.TDL_SERVICE_TYPE_ID",
                "his_sere_serv.TDL_SERVICE_UNIT_ID",
                "his_sere_serv.TDL_HEIN_SERVICE_TYPE_ID",
                "his_sere_serv.TDL_ACTIVE_INGR_BHYT_CODE",
                "his_sere_serv.TDL_ACTIVE_INGR_BHYT_NAME",
                "his_sere_serv.TDL_MEDICINE_CONCENTRA",
                "his_sere_serv.TDL_MEDICINE_REGISTER_NUMBER",
                "his_sere_serv.TDL_MEDICINE_PACKAGE_NUMBER",
                "his_sere_serv.TDL_SERVICE_REQ_CODE",
                "his_sere_serv.TDL_REQUEST_ROOM_ID",
                "his_sere_serv.TDL_REQUEST_DEPARTMENT_ID",
                "his_sere_serv.TDL_REQUEST_LOGINNAME",
                "his_sere_serv.TDL_REQUEST_USERNAME",
                "his_sere_serv.TDL_EXECUTE_ROOM_ID",
                "his_sere_serv.TDL_EXECUTE_DEPARTMENT_ID",
                "his_sere_serv.TDL_EXECUTE_BRANCH_ID",
                "his_sere_serv.TDL_SERVICE_REQ_TYPE_ID",
                "his_sere_serv.TDL_HST_BHYT_CODE",
                "his_sere_serv.VIR_PRICE",
                "his_sere_serv.VIR_PRICE_NO_ADD_PRICE",
                "his_sere_serv.VIR_PRICE_NO_EXPEND",
                "his_sere_serv.VIR_HEIN_PRICE",
                "his_sere_serv.VIR_PATIENT_PRICE",
                "his_sere_serv.VIR_PATIENT_PRICE_BHYT",
                "his_sere_serv.VIR_TOTAL_PRICE",
                "his_sere_serv.VIR_TOTAL_PRICE_NO_ADD_PRICE",
                "his_sere_serv.VIR_TOTAL_PRICE_NO_EXPEND",
                "his_sere_serv.VIR_TOTAL_HEIN_PRICE",
                "his_sere_serv.VIR_TOTAL_PATIENT_PRICE",
                "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_BHYT",
                "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_NO_DC",
                "his_sere_serv.VIR_TOTAL_PATIENT_PRICE_TEMP",
            );
    }
    public function applyWith($query){
        return $query->with($this->paramWith());
    }
    public function paramWith(){
        return [
            'exp_mest_bloods',
            'exp_mest_materials',
            'exp_mest_medicines',
            'sere_serv_bills',
            'sere_serv_debts',
            'sere_serv_deposits',
            'sere_serv_files',
            'sere_serv_matys',
            'sere_serv_pttts',
            'sere_serv_rehas',
            'sere_serv_suins',
            'sere_serv_teins',
            'service_change_reqs',
            'sese_depo_repays',
            'sese_trans_reqs',
        ];
    }
    public function view()
    {
        return $this->sereServ
            ->select(
                'his_sere_serv.*',
                );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv.tdl_treatment_code'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyDebateIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_sere_serv.debate_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_sere_serv.' . $key, $item);
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
        return $this->sereServ->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->sereServ::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'sere_serv_code' => $request->sere_serv_code,
    //         'sere_serv_name' => $request->sere_serv_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'sere_serv_code' => $request->sere_serv_code,
    //         'sere_serv_name' => $request->sere_serv_name,
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
            $data = $this->applyJoins()->where('his_sere_serv.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_sere_serv.id');
            $maxId = $this->applyJoins()->max('his_sere_serv.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('sere_serv', 'his_sere_serv', $startId, $endId, $batchSize, $this->paramWith());
            }
        }
    }
}