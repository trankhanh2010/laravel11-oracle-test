<?php

namespace App\Models\View;

use App\Models\HIS\DebateEkipUser;
use App\Models\HIS\DebateInviteUser;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebateDetailVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_debate_detail';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    
    public function debate_ekip_users()
    {
        return $this->hasMany(DebateEkipUser::class, 'debate_id', 'id');
    }
    public function debate_invite_users()
    {
        return $this->hasMany(DebateInviteUser::class, 'debate_id', 'id');
    }
}
