<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BhytWhitelist extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_bhyt_whitelist';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id', 'id');
    }
}
