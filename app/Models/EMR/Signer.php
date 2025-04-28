<?php

namespace App\Models\EMR;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Signer extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_emr'; 
    protected $table = 'emr_signer';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    // Mã hóa Base64 trước khi trả về frontend
    public function getSignImageAttribute($value)
    {
        return base64_encode($value);
    }
}

