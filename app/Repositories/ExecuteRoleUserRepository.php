<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ExecuteRoleUser;
use Illuminate\Support\Facades\DB;

class ExecuteRoleUserRepository
{
    protected $executeRoleUser;
    public function __construct(ExecuteRoleUser $executeRoleUser)
    {
        $this->executeRoleUser = $executeRoleUser;
    }

    public function applyJoins()
    {
        return $this->executeRoleUser
            ->leftJoin('his_employee as employee', 'employee.loginname', '=', 'his_execute_role_user.loginname')
            ->leftJoin('his_department as department', 'department.id', '=', 'employee.department_id')
            ->leftJoin('his_execute_role as execute_role', 'execute_role.id', '=', 'his_execute_role_user.execute_role_id')
            ->select(
                'his_execute_role_user.*',
                'employee.tdl_username',
                'employee.diploma',
                'employee.tdl_email',
                'employee.tdl_mobile',
                'employee.DOB',
                'department.department_code',
                'department.department_name',
                'execute_role.execute_role_code',
                'execute_role.execute_role_name'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.is_active'), $isActive);
        }
        return $query;
    }
    public function applyLoginnameFilter($query, $loginname)
    {
        if ($loginname !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.loginname'), $loginname);
        }
        return $query;
    }
    public function applyExecuteRoleIdFilter($query, $executeRoleId)
    {
        if ($executeRoleId !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_execute_role_user.execute_role_id'), $executeRoleId);
        }

        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['DOB', 'tdl_mobile', 'tdl_email', 'diploma', 'tdl_username'])) {
                        $query->orderBy('employee.' . $key, $item);
                    }
                    if (in_array($key, ['department_code', 'department_name'])) {
                        $query->orderBy('department.' . $key, $item);
                    }
                    if (in_array($key, ['execute_role_code', 'execute_role_name'])) {
                        $query->orderBy('execute_role.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_execute_role_user.' . $key, $item);
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
        return $this->executeRoleUser->find($id);
    }
    public function getByLoginnameAndExecuteRoleIds($loginname, $executeRoles)
    {
        return $this->executeRoleUser->where('loginname', $loginname)->whereIn('execute_role_id',$executeRoles)->get();
    }
    public function getByExecuteRoleIdAndLoginnames($executeRole, $loginnames)
    {
        return $this->executeRoleUser->whereIn('loginname', $loginnames)->where('execute_role_id',$executeRole)->get();
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function deleteByLoginname($id){
        $ids = $this->executeRoleUser->where('loginname', $id)->pluck('id')->toArray();
        $this->executeRoleUser->where('loginname', $id)->delete();
        return $ids;
    }
    public function deleteByExecuteRoleId($id){
        $ids = $this->executeRoleUser->where('execute_role_id', $id)->pluck('id')->toArray();
        $this->executeRoleUser->where('execute_role_id', $id)->delete();
        return $ids;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_execute_role_user.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_execute_role_user.id');
            $maxId = $this->applyJoins()->max('his_execute_role_user.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('execute_role_user', 'his_execute_role_user', $startId, $endId, $batchSize);
            }
        }
    }
}