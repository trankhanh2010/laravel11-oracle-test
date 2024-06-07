<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebateEkipUser extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Debate_Ekip_User';
    protected $fillable = [

    ];
    public function execute_role()
    {
        return $this->belongsTo(ExecuteRole::class, 'execute_role_id');
    }

    public function department()
    {
        return $this->belongsTo(Department::class, 'department_id');
    }
}
