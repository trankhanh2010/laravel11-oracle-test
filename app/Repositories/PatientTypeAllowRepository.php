<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PatientTypeAllow;
use Illuminate\Support\Facades\DB;

class PatientTypeAllowRepository
{
    protected $patientTypeAllow;
    public function __construct(PatientTypeAllow $patientTypeAllow)
    {
        $this->patientTypeAllow = $patientTypeAllow;
    }

    public function applyJoins()
    {
        return $this->patientTypeAllow
        ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_type_allow.patient_type_id')
        ->leftJoin('his_patient_type as patient_type_allow', 'patient_type_allow.id', '=', 'his_patient_type_allow.patient_type_allow_id')
            ->select(
                'his_patient_type_allow.*',
                'patient_type.patient_type_code as patient_type_code',
                'patient_type.patient_type_name as patient_type_name',
                'patient_type_allow.patient_type_code as patient_type_allow_code',
                'patient_type_allow.patient_type_name as patient_type_allow_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('patient_type_allow_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('patient_type_allow_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.is_active'), $isActive);
        }
        return $query;
    }
    public function applyPatientTypeIdFilter($query, $id)
    {
        if ($id != null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.patient_type_id'), $id);
        }
        return $query;
    }
    public function applyPatientTypeAllowIdFilter($query, $id)
    {
        if ($id !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_type_allow.is_active'), $id);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['patient_type_code', 'patient_type_name', 'patient_type_allow_code', 'patient_type_allow_name'])) {
                        $query->orderBy($key, $item);
                    }
                } else {
                    $query->orderBy('his_patient_type_allow.' . $key, $item);
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
        return $this->patientTypeAllow->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->patientTypeAllow::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'patient_type_id' => $request->patient_type_id,
            'patient_type_allow_id' => $request->patient_type_allow_id,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'patient_type_id' => $request->patient_type_id,
            'patient_type_allow_id' => $request->patient_type_allow_id,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_patient_type_allow.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_patient_type_allow.id');
            $maxId = $this->applyJoins()->max('his_patient_type_allow.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('patient_type_allow', 'his_patient_type_allow', $startId, $endId, $batchSize);
            }
        }
    }
}