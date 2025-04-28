<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Branch;
use Illuminate\Support\Facades\DB;

class BranchRepository
{
    protected $branch;
    public function __construct(Branch $branch)
    {
        $this->branch = $branch;
    }

    public function applyJoins()
    {
        return $this->branch
            ->select(
                'his_branch.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_branch.branch_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_branch.branch_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_branch.is_active'), $isActive);
        }

        return $query;
    }
    public function applyBranchCodeFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_branch.branch_code'), $param);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_branch.' . $key, $item);
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
        return $this->branch->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->branch::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'branch_code' => $request->branch_code,
            'branch_name' => $request->branch_name,
            'hein_medi_org_code' => $request->hein_medi_org_code,
            'accept_hein_medi_org_code' => $request->accept_hein_medi_org_code,
            'sys_medi_org_code' => $request->sys_medi_org_code,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'address' => $request->address,
            'parent_organization_name' => $request->parent_organization_name,
            'hein_province_code' => $request->hein_province_code,
            'hein_level_code' => $request->hein_level_code,
            'do_not_allow_hein_level_code' => $request->do_not_allow_hein_level_code,
            'tax_code' => $request->tax_code,
            'account_number' => $request->account_number,
            'phone' => $request->phone,
            'representative' => $request->representative,
            'position' => $request->position,
            'representative_hein_code' => $request->representative_hein_code,
            'auth_letter_issue_date' => $request->auth_letter_issue_date,
            'auth_letter_num' => $request->auth_letter_num,
            'bank_info' => $request->bank_info,
            'the_branch_code' => $request->the_branch_code,
            'director_loginname' => $request->director_loginname,
            'director_username' => $request->director_username,
            'venture' => $request->venture,
            'type' => $request->type,
            'form' => $request->form,
            'bed_approved' => $request->bed_approved,
            'bed_actual' => $request->bed_actual,
            'bed_resuscitation' => $request->bed_resuscitation,
            'bed_resuscitation_emg' => $request->bed_resuscitation_emg,
            'is_use_branch_time' => $request->is_use_branch_time
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
           'branch_name' => $request->branch_name,
            'hein_medi_org_code' => $request->hein_medi_org_code,
            'accept_hein_medi_org_code' => $request->accept_hein_medi_org_code,
            'sys_medi_org_code' => $request->sys_medi_org_code,
            'province_code' => $request->province_code,
            'province_name' => $request->province_name,
            'district_code' => $request->district_code,
            'district_name' => $request->district_name,
            'commune_code' => $request->commune_code,
            'commune_name' => $request->commune_name,
            'address' => $request->address,
            'parent_organization_name' => $request->parent_organization_name,
            'hein_province_code' => $request->hein_province_code,
            'hein_level_code' => $request->hein_level_code,
            'do_not_allow_hein_level_code' => $request->do_not_allow_hein_level_code,
            'tax_code' => $request->tax_code,
            'account_number' => $request->account_number,
            'phone' => $request->phone,
            'representative' => $request->representative,
            'position' => $request->position,
            'representative_hein_code' => $request->representative_hein_code,
            'auth_letter_issue_date' => $request->auth_letter_issue_date,
            'auth_letter_num' => $request->auth_letter_num,
            'bank_info' => $request->bank_info,
            'the_branch_code' => $request->the_branch_code,
            'director_loginname' => $request->director_loginname,
            'director_username' => $request->director_username,
            'venture' => $request->venture,
            'type' => $request->type,
            'form' => $request->form,
            'bed_approved' => $request->bed_approved,
            'bed_actual' => $request->bed_actual,
            'bed_resuscitation' => $request->bed_resuscitation,
            'bed_resuscitation_emg' => $request->bed_resuscitation_emg,
            'is_use_branch_time' => $request->is_use_branch_time,
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
            $data = $this->applyJoins()->where('his_branch.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_branch.id');
            $maxId = $this->applyJoins()->max('his_branch.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('branch', 'his_branch', $startId, $endId, $batchSize);
            }
        }
    }
}