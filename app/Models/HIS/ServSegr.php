<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServSegr extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_serv_segr';
    protected $fillable = [
        'service_group_id',
        'service_id',
        'room_id',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function service_group()
    {
        return $this->belongsTo(ServiceGroup::class, 'service_group_id');
    }

    public function pttt_groups()
    {
        return $this->belongsToMany(PtttGroup::class, SereServPttt::class, 'sere_serv_id', 'pttt_group_id');
    }

}
