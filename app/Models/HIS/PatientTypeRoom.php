<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientTypeRoom extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'HIS_Patient_Type_Room';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }

    public function patient_type()
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id');
    }
}
