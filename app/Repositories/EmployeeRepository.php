<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Employee;
use Illuminate\Support\Facades\DB;

class EmployeeRepository
{
    protected $employee;
    public function __construct(Employee $employee)
    {
        $this->employee = $employee;
    }

    public function applyJoins($tab = null)
    {
        if($tab == 'selectIsDoctor' || $tab == 'selectIsNurse' || $tab == 'selectPositionTruongKhoa')
        return $this->employee->select([
            'id',
            'loginname',
            'tdl_username',
        ]);
        return $this->employee
            ->leftJoin('his_department as department', 'department.id', '=', 'his_employee.department_id')
            ->leftJoin('his_gender as gender', 'gender.id', '=', 'his_employee.gender_id')
            ->leftJoin('his_career_title as career_title', 'career_title.id', '=', 'his_employee.career_title_id')
            ->select(
                'his_employee.*',
                'department.department_name',
                'department.department_code',
                'gender.gender_name',
                'gender.gender_code',
                'career_title.career_title_name',
                'career_title.career_title_code',
            );
    }
    public function getInfoUser($id)
    {
        return $this->employee
            ->leftJoin('his_department as department', 'department.id', '=', 'his_employee.department_id')
            ->leftJoin('his_gender as gender', 'gender.id', '=', 'his_employee.gender_id')
            ->leftJoin('his_branch as branch', 'branch.id', '=', 'his_employee.branch_id')
            ->leftJoin('his_career_title as career_title', 'career_title.id', '=', 'his_employee.career_title_id')

            ->select(
                'his_employee.*',
                'department.department_name',
                'department.department_code',
                'gender.gender_name',
                'gender.gender_code',
                'branch.branch_name',
                'branch.branch_code',
                'career_title.career_title_name',
                'career_title.career_title_code',
            )
            ->where('his_employee.id', $id)
            ->first();
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_employee.loginname'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_employee.tdl_username'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_employee.is_active'), $isActive);
        }

        return $query;
    }
    public function applyIsDeleteFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_employee.is_delete'), $param);
        }

        return $query;
    }
    public function applyTabFilter($query, $param)
    {
        if ($param != null) {
            switch ($param) {
                case 'selectIsDoctor':
                    $query->where(DB::connection('oracle_his')->raw('his_employee.is_doctor'), '1');
                    return $query;
                case 'selectIsNurse':
                    $query->where(DB::connection('oracle_his')->raw('his_employee.is_nurse'), '1');
                    return $query;
                case 'selectPositionTruongKhoa':
                    $query->where(DB::connection('oracle_his')->raw('his_employee.position'), '2')
                    ->orWhere(DB::connection('oracle_his')->raw('his_employee.position'), '3');
                    return $query;
                default:
                    return $query;
            }
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['department_name', 'department_code'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['gender_name', 'gender_code'])) {
                        $query->orderBy('gender.' . $key, $item);
                    }
                    if (in_array($key, ['career_title_name', 'career_title_code'])) {
                        $query->orderBy('career_title.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_employee.' . $key, $item);
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
        return $this->employee->find($id);
    }
    public function getByLoginname($id)
    {
        return $this->employee->where('loginname', $id)->first();
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->employee::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,

            'loginname' => $request->loginname,
            'tdl_username' => $request->tdl_username,
            'dob' => $request->dob,
            'gender_id' => $request->gender_id,
            'ethnic_code' => $request->ethnic_code,

            'tdl_email' => $request->tdl_email,
            'tdl_mobile' => $request->tdl_mobile,
            'diploma' => $request->diploma,
            'diploma_date' => $request->diploma_date,
            'diploma_place' => $request->diploma_place,
            'title' => $request->title,

            'medicine_type_rank' => $request->medicine_type_rank,
            'max_bhyt_service_req_per_day' => $request->max_bhyt_service_req_per_day,
            'max_service_req_per_day' => $request->max_service_req_per_day,
            'is_service_req_exam' => $request->is_service_req_exam,
            'account_number' => $request->account_number,
            'bank' => $request->bank,

            'department_id' => $request->department_id,
            'default_medi_stock_ids' => $request->default_medi_stock_ids,
            'erx_loginname' => $request->erx_loginname,
            'erx_password' => $request->erx_password,
            'identification_number' => $request->identification_number,
            'social_insurance_number' => $request->social_insurance_number,

            'career_title_id' => $request->career_title_id,
            'position' => $request->position,
            'speciality_codes' => $request->speciality_codes,
            'type_of_time' => $request->type_of_time,
            'branch_id' => $request->branch_id,
            'medi_org_codes' => $request->medi_org_codes,

            'is_doctor' => $request->is_doctor,
            'is_nurse' => $request->is_nurse,
            'is_admin' => $request->is_admin,
            'allow_update_other_sclinical' => $request->allow_update_other_sclinical,
            'do_not_allow_simultaneity' => $request->do_not_allow_simultaneity,
            'is_limit_schedule' => $request->is_limit_schedule,

            'is_need_sign_instead' => $request->is_need_sign_instead,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'tdl_username' => $request->tdl_username,
            'dob' => $request->dob,
            'gender_id' => $request->gender_id,
            'ethnic_code' => $request->ethnic_code,

            'tdl_email' => $request->tdl_email,
            'tdl_mobile' => $request->tdl_mobile,
            'diploma' => $request->diploma,
            'diploma_date' => $request->diploma_date,
            'diploma_place' => $request->diploma_place,
            'title' => $request->title,

            'medicine_type_rank' => $request->medicine_type_rank,
            'max_bhyt_service_req_per_day' => $request->max_bhyt_service_req_per_day,
            'max_service_req_per_day' => $request->max_service_req_per_day,
            'is_service_req_exam' => $request->is_service_req_exam,
            'account_number' => $request->account_number,
            'bank' => $request->bank,

            'department_id' => $request->department_id,
            'default_medi_stock_ids' => $request->default_medi_stock_ids,
            'erx_loginname' => $request->erx_loginname,
            'erx_password' => $request->erx_password,
            'identification_number' => $request->identification_number,
            'social_insurance_number' => $request->social_insurance_number,

            'career_title_id' => $request->career_title_id,
            'position' => $request->position,
            'speciality_codes' => $request->speciality_codes,
            'type_of_time' => $request->type_of_time,
            'branch_id' => $request->branch_id,
            'medi_org_codes' => $request->medi_org_codes,

            'is_doctor' => $request->is_doctor,
            'is_nurse' => $request->is_nurse,
            'is_admin' => $request->is_admin,
            'allow_update_other_sclinical' => $request->allow_update_other_sclinical,
            'do_not_allow_simultaneity' => $request->do_not_allow_simultaneity,
            'is_limit_schedule' => $request->is_limit_schedule,

            'is_need_sign_instead' => $request->is_need_sign_instead,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function updateInfoUser($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'tdl_username' => $request->tdl_username,
            'dob' => $request->dob,
            'tdl_email' => $request->tdl_email,
            'tdl_mobile' => $request->tdl_mobile,
            'diploma' => $request->diploma,
            'title' => $request->title,

            'account_number' => $request->account_number,
            'bank' => $request->bank,
            'department_id' => $request->department_id,
            'default_medi_stock_ids' => $request->default_medi_stock_ids,
            'social_insurance_number' => $request->social_insurance_number,
            'erx_loginname' => $request->erx_loginname,
            'erx_password' => $request->erx_password,

            'is_active' => $request->is_active,
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_employee.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_employee.id');
            $maxId = $this->applyJoins()->max('his_employee.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('employee', 'his_employee', $startId, $endId, $batchSize);
            }
        }
    }
}
