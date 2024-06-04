<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExecuteRoleUser extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Execute_Role_User';
    protected $fillable = [
        'execute_role_id',
        'loginname'
    ];

    public function execute_role()
    {
        return $this->belongsTo(ExecuteRole::class,'execute_role_id');
    }
}
