<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PtttMethod extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_pttt_method';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function pttt_group()
    {
        return $this->belongsTo(PtttGroup::class, 'pttt_group_id');
    }
}
