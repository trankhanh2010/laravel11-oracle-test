<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BHYTWhitelist extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_BHYT_Whitelist';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function career()
    {
        return $this->belongsTo(Career::class, 'career_id', 'id');
    }
}
