<?php

namespace App\Models\HIS;

use App\Models\ACS\User;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecuteRole extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Execute_Role';

    public function employees()
    {
        return $this->belongsToMany(Employee::class, ExecuteRoleUser::class, 'execute_role_id', 'loginname','id','loginname');
    }

    public function debate_ekip_users()
    {
        return $this->hasMany(DebateEkipUser::class, 'execute_role_id');
    }
    public function debate_invite_users()
    {
        return $this->hasMany(DebateInviteUser::class, 'execute_role_id');
    }
    public function debate_users()
    {
        return $this->hasMany(DebateUser::class, 'execute_role_id');
    }
    public function ekip_plan_users()
    {
        return $this->hasMany(EkipPlanUser::class, 'execute_role_id');
    }
    public function ekip_temp_users()
    {
        return $this->hasMany(EkipTempUser::class, 'execute_role_id');
    }
    public function execute_role_users()
    {
        return $this->hasMany(ExecuteRoleUser::class, 'execute_role_id');
    }
    public function exp_mest_users()
    {
        return $this->hasMany(ExpMestUser::class, 'execute_role_id');
    }
    public function imp_mest_users()
    {
        return $this->hasMany(ImpMestUser::class, 'execute_role_id');
    }
    public function imp_user_temp_dts()
    {
        return $this->hasMany(ImpUserTempDt::class, 'execute_role_id');
    }
    public function mest_inve_users()
    {
        return $this->hasMany(MestInveUser::class, 'execute_role_id');
    }
    public function remunerations()
    {
        return $this->hasMany(Remuneration::class, 'execute_role_id');
    }
    public function surg_remu_details()
    {
        return $this->hasMany(SurgRemuDetail::class, 'execute_role_id');
    }
    public function user_group_temp_dts()
    {
        return $this->hasMany(UserGroupTempDt::class, 'execute_role_id');
    }
}
