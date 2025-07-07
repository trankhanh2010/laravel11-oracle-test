<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class TextLib extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_text_lib';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    // Accessor để tự động base64_encode content khi get
    public function getContentAttribute($value)
    {
        return base64_encode($value);
    }
}
