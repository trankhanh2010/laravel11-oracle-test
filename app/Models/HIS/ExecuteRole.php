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
}
