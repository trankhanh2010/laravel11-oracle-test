<?php 
namespace App\Repositories;

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
    public function delete($data){
        $data->delete();
        return $data;
    }

    public function getDataFromDbToElastic($id = null){
        $data = $this->applyJoins();
        if($id != null){
            $data = $data->where('his_execute_role_user.id','=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
            }
        } else {
            $data = $data->get();
            $data = $data->map(function ($item) {
                return $item->getAttributes(); 
            })->toArray(); 
        }
        return $data;
    }
}