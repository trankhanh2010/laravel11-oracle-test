<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Treatment;
use Illuminate\Support\Facades\DB;

class TreatmentRepository
{
    protected $treatment;
    public function __construct(Treatment $treatment)
    {
        $this->treatment = $treatment;
    }

    public function applyJoins()
    {
        return $this->treatment
            ->select(
                'his_treatment.*'
            );
    }
    public function applyWith($query)
    {
        return $query->with($this->paramWith());
    }
    public function paramWith()
    {
        return [
            'accident_hurts',
            'adrs',
            'allergy_cards',
            'antibiotic_requests',
            'appointment_servs',
            'babys',
            'cares',
            'care_sums',
            'carer_card_borrows',
            'debates',
            'department_trans',
            'deposit_reqs',
            'dhsts',
            'exp_mest_maty_reqs',
            'exp_mest_mety_reqs',
            'hein_approvals',
            'hiv_treatments',
            'hold_returns',
            'imp_mest_mate_reqs',
            'imp_mest_medi_reqs',
            'infusion_sums',
            'medi_react_sums',
            'medical_assessments',
            'medicine_interactives',
            'mr_check_summarys',
            'obey_contraindis',
            'patient_type_alters',
            'prepares',
            'reha_sums',
            'sere_servs',
            'service_reqs',
            'severe_illness_infos',
            'trackings',
            'trans_reqs',
            'transactions',
            'transfusion_sums',
            'treatment_bed_rooms',
            'treatment_borrows',
            'treatment_files',
            'treatment_loggings',
            'treatment_unlimits',
            'tuberculosis_treats',
        ];
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_treatment.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_treatment.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_treatment.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_treatment.' . $key, $item);
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
        return $this->treatment->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->treatment::create([
    //         'create_time' => now()->format('Ymdhis'),
    //         'modify_time' => now()->format('Ymdhis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'treatment_code' => $request->treatment_code,
    //         'treatment_name' => $request->treatment_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('Ymdhis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'treatment_code' => $request->treatment_code,
    //         'treatment_name' => $request->treatment_name,
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
            $data = $this->applyJoins()->where('his_treatment.id', '=', $id)->first();
            if ($data) {
                $data = $data->toArray();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_treatment.id');
            $maxId = $this->applyJoins()->max('his_treatment.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('treatment', 'his_treatment', $startId, $endId, $batchSize);
            }
        }
    }
}