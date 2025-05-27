<?php

namespace App\Models\View;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BangKeVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_bang_ke';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function getJsonPatientTypeAlterAttribute($value)
    {
        if (is_string($value)) {
                return json_decode($value);
            }
        return $value;
    }
}
