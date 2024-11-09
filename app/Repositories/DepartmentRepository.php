<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Department;
use Illuminate\Support\Facades\DB;

class DepartmentRepository
{
    protected $department;
    public function __construct(Department $department)
    {
        $this->department = $department;
    }

    public function applyJoins()
    {
        return $this->department
            ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_department.branch_id')
            ->leftJoin('his_treatment_type as req_surg_treatment_type', 'req_surg_treatment_type.id', '=', 'his_department.req_surg_treatment_type_id')
            ->leftJoin('his_patient_type as default_instr_patient_type', 'default_instr_patient_type.id', '=', 'his_department.default_instr_patient_type_id')
            ->select(
                'his_department.*',
                'branch.branch_code',
                'branch.branch_name',
                'req_surg_treatment_type.treatment_type_code',
                'req_surg_treatment_type.treatment_type_name',
                'default_instr_patient_type.patient_type_code',
                'default_instr_patient_type.patient_type_name',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_department.department_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_department.department_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_department.is_active'), $isActive);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['branch_code', 'branch_name'])) {
                        $query->orderBy('branch.' . $key, $item);
                    }
                    if (in_array($key, ['treatment_type_code', 'treatment_type_name'])) {
                        $query->orderBy('req_surg_treatment_type.' . $key, $item);
                    }
                    if (in_array($key, ['patient_type_code', 'patient_type_name'])) {
                        $query->orderBy('default_instr_patient_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_department.' . $key, $item);
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
        return $this->department->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->department::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'department_code' => $request->department_code,
            'department_name' => $request->department_name,
            'g_code' => $request->g_code,
            'bhyt_code' => $request->bhyt_code,
            'branch_id' => $request->branch_id,
            'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
            'num_order' => $request->num_order,
            'allow_treatment_type_ids' => $request->allow_treatment_type_ids,
            'theory_patient_count' => $request->theory_patient_count,
            'reality_patient_count' => $request->reality_patient_count,
            'req_surg_treatment_type_id' => $request->req_surg_treatment_type_id,
            'phone' => $request->phone,
            'head_loginname' => $request->head_loginname,
            'head_username' => $request->head_username,
            'accepted_icd_codes' => $request->accepted_icd_codes,
            'is_exam' => $request->is_exam,
            'is_clinical' => $request->is_clinical,
            'allow_assign_package_price' => $request->allow_assign_package_price,
            'auto_bed_assign_option' => $request->auto_bed_assign_option,
            'is_emergency' => $request->is_emergency,
            'is_auto_receive_patient' => $request->is_auto_receive_patient,
            'allow_assign_surgery_price' => $request->allow_assign_surgery_price,
            'is_in_dep_stock_moba' => $request->is_in_dep_stock_moba,
            'warning_when_is_no_surg' => $request->warning_when_is_no_surg,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'department_name' => $request->department_name,
            'g_code' => $request->g_code,
            'bhyt_code' => $request->bhyt_code,
            'default_instr_patient_type_id' => $request->default_instr_patient_type_id,
            'num_order' => $request->num_order,
            'allow_treatment_type_ids' => $request->allow_treatment_type_ids,
            'theory_patient_count' => $request->theory_patient_count,
            'reality_patient_count' => $request->reality_patient_count,
            'req_surg_treatment_type_id' => $request->req_surg_treatment_type_id,
            'phone' => $request->phone,
            'head_loginname' => $request->head_loginname,
            'head_username' => $request->head_username,
            'accepted_icd_codes' => $request->accepted_icd_codes,
            'is_exam' => $request->is_exam,
            'is_clinical' => $request->is_clinical,
            'allow_assign_package_price' => $request->allow_assign_package_price,
            'auto_bed_assign_option' => $request->auto_bed_assign_option,
            'is_emergency' => $request->is_emergency,
            'is_auto_receive_patient' => $request->is_auto_receive_patient,
            'allow_assign_surgery_price' => $request->allow_assign_surgery_price,
            'is_in_dep_stock_moba' => $request->is_in_dep_stock_moba,
            'warning_when_is_no_surg' => $request->warning_when_is_no_surg,
            'is_active' => $request->is_active,
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
            $data = $this->applyJoins()->where('his_department.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_department.id');
            $maxId = $this->applyJoins()->max('his_department.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('department', 'his_department', $startId, $endId, $batchSize);
            }
        }
    }
}