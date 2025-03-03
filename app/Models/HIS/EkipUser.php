<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkipUser extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_ekip_user';
    protected $fillable = [

    ];
    public function department()
    {
        return $this->belongsTo(Department::class);
    }
    public function execute_role()
    {
        return $this->belongsTo(ExecuteRole::class);
    }
}
