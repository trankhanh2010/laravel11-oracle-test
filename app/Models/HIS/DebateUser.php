<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebateUser extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    
    protected $connection = 'oracle_his'; 
    protected $table = 'his_debate_user';
    protected $fillable = [

    ];
    public function execute_role()
    {
        return $this->belongsTo(ExecuteRole::class, 'execute_role_id');
    }
}
