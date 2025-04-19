<?php

namespace App\Models\View;

use App\Models\HIS\Bed;
use App\Models\HIS\DepartmentTran;
use App\Models\HIS\Dhst;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ServiceReqType;
use App\Models\HIS\TreatmentBedRoom;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MedicalCaseCoverListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'xa_v_his_medi_case_cover_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public $serviceReqTypeKHId;
    public function department_trans()
    {
        return $this->hasMany(DepartmentTran::class, 'treatment_id', 'treatment_id');
    }
    public function service_req_KH()
    {
        return $this->hasMany(ServiceReq::class, 'treatment_id', 'treatment_id')->where('service_req_type_id', 1);
    }
    public function dhsts()
    {
        $this->serviceReqTypeKHId = Cache::remember('service_req_type_KH_id', now()->addDay(7), function () {
            // Logic để lấy dữ liệu nếu cache không tồn tại
            return ServiceReqType::where('service_req_type_code', 'KH')->firstOrFail()->id;
        });
        return $this->hasMany(Dhst::class, 'treatment_id', 'treatment_id');
    }
    public function beds()
    {
        return $this->hasManyThrough(
            Bed::class, // Mô hình cuối cùng
            TreatmentBedRoom::class, // Mô hình trung gian
            'treatment_id', // Khóa ngoại trong bảng treatment_bed_room trỏ tới medi_case_cover_list
            'id', // Khóa chính trong bảng beds
            'treatment_id', // Khóa chính trong bảng medi_case_cover_list
            'bed_id' // Khóa ngoại trong bảng treatment_bed_room trỏ tới beds
        );
    }
}
