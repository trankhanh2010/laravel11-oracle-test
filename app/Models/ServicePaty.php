<?php

namespace App\Models;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ServicePaty extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Service_Paty';
    protected $fillable = [
        'service_id',
        'patient_type_id',
        'branch_id',
        'request_room_ids',
        'execute_room_ids',
        'request_deparment_ids',
        'package_id',
        'service_condition_id',
        'patient_classify_id',
        'ration_time_id'
    ];

    public function service()
    {
        return $this->belongsTo(Service::class, 'service_id');
    }

    public function patient_type()
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id');
    }

    public function branch()
    {
        return $this->belongsTo(Branch::class, 'branch_id');
    }

    public function request_rooms()
    {
        return Room::whereIn('id', explode(',', $this->request_room_ids))->get();
    }

    public function execute_rooms()
    {
        return ExecuteRoom::whereIn('room_id', explode(',', $this->execute_room_ids))->get();
    }

    public function request_deparments()
    {
        return Department::whereIn('id', explode(',', $this->request_deparment_ids))->get();
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function service_condition()
    {
        return $this->belongsTo(ServiceCondition::class, 'service_condition_id');
    }

    public function patient_classify()
    {
        return $this->belongsTo(PatientClassify::class, 'patient_classify_id');
    }

    public function ration_time()
    {
        return $this->belongsTo(RationTime::class, 'ration_time_id');
    }
}
