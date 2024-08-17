<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServiceUnit extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Service_Unit';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];

    public function convert()
    {
        return $this->belongsTo(ServiceUnit::class, 'convert_id', 'id');
    }
}
