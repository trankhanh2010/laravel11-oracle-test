<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtttGroup extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_Pttt_Group';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function serv_segrs()
    {
        return $this->belongsToMany(ServSegr::class, SereServPttt::class, 'pttt_group_id', 'sere_serv_id');
    }

    public function pttt_group()
    {
        return $this->belongsTo(PtttGroup::class, 'pttt_group_id');
    }

    public function bed_services()
    {
        return $this->belongsToMany(Service::class, PtttGroupBest::class, 'pttt_group_id', 'bed_service_type_id');
    }
}
