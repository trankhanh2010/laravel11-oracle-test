<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ExpMestMedicine;
use Illuminate\Support\Facades\DB;

class ExpMestMedicineRepository
{
    protected $expMestMedicine;
    public function __construct(ExpMestMedicine $expMestMedicine)
    {
        $this->expMestMedicine = $expMestMedicine;
    }
    public function applyJoins()
    {
        return $this->expMestMedicine
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
                'EXP_MEST_ID',
                'MEDICINE_ID',
                'TDL_MEDI_STOCK_ID',
                'TDL_MEDICINE_TYPE_ID',
                'IS_EXPORT',
                'AMOUNT',
                'PRICE',
                'VAT_RATIO',
                'NUM_ORDER',
                'APPROVAL_LOGINNAME',
                'APPROVAL_USERNAME',
                'APPROVAL_TIME',
                'APPROVAL_DATE',
                'EXP_LOGINNAME',
                'EXP_USERNAME',
                'EXP_TIME',
                'EXP_DATE',
                'PATIENT_TYPE_ID',
                'USE_TIME_TO',
                'TUTORIAL',
                'TDL_SERVICE_REQ_ID',
                'TDL_TREATMENT_ID',
                'VIR_PRICE',
                'MORNING',
                'EVENING',
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'bcs_mety_req_dts',
            'imp_mest_medi_reqs',
            'medicine_beans'
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_medicine.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_medicine.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_medicine.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyTdlTreatmentIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_exp_mest_medicine.tdl_treatment_id'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_exp_mest_medicine.' . $key, $item);
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
        return $this->expMestMedicine->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->expMestMedicine::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'exp_mest_medicine_code' => $request->exp_mest_medicine_code,
    //         'exp_mest_medicine_name' => $request->exp_mest_medicine_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'exp_mest_medicine_code' => $request->exp_mest_medicine_code,
    //         'exp_mest_medicine_name' => $request->exp_mest_medicine_name,
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
            $data = $this->applyJoins()->where('his_exp_mest_medicine.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_exp_mest_medicine.id');
            $maxId = $this->applyJoins()->max('his_exp_mest_medicine.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('exp_mest_medicine', 'his_exp_mest_medicine', $startId, $endId, $batchSize);
            }
        }
    }
}