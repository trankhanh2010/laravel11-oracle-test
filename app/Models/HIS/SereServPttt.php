<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SereServPttt extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_sere_serv_pttt';
    protected $fillable = [

    ];
    public function serv_segr()
    {
        return $this->belongsTo(ServSegr::class, 'sere_serv_id');
    }

    public function pttt_group()
    {
        return $this->belongsTo(PtttGroup::class, 'pttt_group_id');
    }
    public function pttt_method()
    {
        return $this->belongsTo(PtttMethod::class, 'pttt_method_id');
    }
    public function pttt_condition()
    {
        return $this->belongsTo(PtttCondition::class, 'pttt_condition_id');
    }
    public function pttt_catastrophe()
    {
        return $this->belongsTo(PtttCatastrophe::class, 'pttt_catastrophe_id');
    }
    public function pttt_high_tech()
    {
        return $this->belongsTo(PtttHighTech::class, 'pttt_high_tech_id');
    }
    public function pttt_priority()
    {
        return $this->belongsTo(PtttPriority::class, 'pttt_priority_id');
    }
    public function pttt_table()
    {
        return $this->belongsTo(PtttTable::class, 'pttt_table_id');
    }
}
