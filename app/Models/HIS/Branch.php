<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Branch extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_branch';

    protected $guarded = [
        'id',
    ];
    public $timestamps = false;
    public function department()
    {
        return $this->hasOne(Department::class);
    }
}
