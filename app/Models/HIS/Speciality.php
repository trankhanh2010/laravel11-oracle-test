<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Speciality extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'his_speciality';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function room()
    {
        return $this->hasOne(Room::class);
    }
}
