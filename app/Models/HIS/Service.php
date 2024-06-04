<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Service';
    protected $fillable = [
        'service_type_id',
        'parent_id',
        'service_unit_id',
        'hein_service_type_id',
        'bill_patient_type_id',
        'pttt_group_id',
        'pttt_method_id',
        'icd_cm_id',
        'revenue_department_id',
        'package_id',
        'exe_service_module_id',
        'gender_id',
        'ration_group_id',
        'diim_type_id',
        'fuex_type_id',
        'test_type_id',
        'other_pay_source_id',
        'body_part_ids',
        'film_size_id',
        'applied_patient_type_ids',
        'default_patient_type_id',
        'applied_patient_classify_ids',
        'min_proc_time_except_paty_ids',
        'max_proc_time_except_paty_ids',
        'total_time_except_paty_ids',
        'service_code',
    ];

    public function patient_types()
    {
        return $this->belongsToMany(PatientType::class, ServicePaty::class, 'service_id', 'patient_type_id')
        ->withPivot('price','vat_ratio');
    }

    public function machines()
    {
        return $this->belongsToMany(Machine::class, ServiceMachine::class, 'service_id', 'machine_id');
    }

    public function execute_rooms()
    {
        return $this->belongsToMany(ExecuteRoom::class, ServiceRoom::class, 'service_id', 'room_id', 'id', 'room_id');
    }

    public function follows()
    {
        return $this->belongsToMany(Service::class, ServiceFollow::class, 'service_id', 'follow_id');
    }

    public function services()
    {
        return $this->belongsToMany(Service::class, ServiceFollow::class, 'follow_id', 'service_id');
    }

    public function beds()
    {
        return $this->belongsToMany(Bed::class, BedBsty::class, 'bed_service_type_id', 'bed_id');
    }
    public function service_type()
    {
        return $this->belongsTo(ServiceType::class, 'service_type_id');
    }

    public function parent()
    {
        return $this->belongsTo(Service::class, 'parent_id');
    }

    public function service_unit()
    {
        return $this->belongsTo(ServiceUnit::class, 'service_unit_id');
    }

    public function hein_service_type()
    {
        return $this->belongsTo(HeinServiceType::class, 'hein_service_type_id');
    }

    public function bill_patient_type()
    {
        return $this->belongsTo(PatientType::class, 'bill_patient_type_id');
    }

    public function pttt_group()
    {
        return $this->belongsTo(PtttGroup::class, 'pttt_group_id');
    }

    public function pttt_method()
    {
        return $this->belongsTo(PtttMethod::class, 'pttt_method_id');
    }

    public function icd_cm()
    {
        return $this->belongsTo(IcdCm::class, 'icd_cm_id');
    }

    public function revenue_department()
    {
        return $this->belongsTo(Department::class, 'revenue_department_id');
    }

    public function package()
    {
        return $this->belongsTo(Package::class, 'package_id');
    }

    public function exe_service_module()
    {
        return $this->belongsTo(ExeServiceModule::class, 'exe_service_module_id');
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class, 'gender_id');
    }

    public function ration_group()
    {
        return $this->belongsTo(RationGroup::class, 'ration_group_id');
    }

    public function diim_type()
    {
        return $this->belongsTo(DiimType::class, 'diim_type_id');
    }

    public function fuex_type()
    {
        return $this->belongsTo(FuexType::class, 'fuex_type_id');
    }

    public function test_type()
    {
        return $this->belongsTo(TestType::class, 'test_type_id');
    }

    public function other_pay_source()
    {
        return $this->belongsTo(OtherPaySource::class, 'other_pay_source_id');
    }

    public function body_parts()
    {
        return BodyPart::whereIn('id', explode(',', $this->body_part_ids))->get();
    }

    public function film_size()
    {
        return $this->belongsTo(FilmSize::class, 'film_size_id');
    }

    public function applied_patient_types()
    {
        return PatientType::whereIn('id', explode(',', $this->applied_patient_type_ids))->get();
    }

    public function default_patient_type()
    {
        return $this->belongsTo(PatientType::class, 'default_patient_type_id');
    }

    public function applied_patient_classifys()
    {
        return PatientClassify::whereIn('id', explode(',', $this->applied_patient_classify_ids))->get();
    }

    public function min_proc_time_except_patys()
    {
        return PatientType::whereIn('id', explode(',', $this->min_proc_time_except_paty_ids))->get();
    }

    public function max_proc_time_except_patys()
    {
        return PatientType::whereIn('id', explode(',', $this->max_proc_time_except_paty_ids))->get();
    }

    public function total_time_except_patys()
    {
        return PatientType::whereIn('id', explode(',', $this->min_proc_time_except_paty_ids))->get();
    }
}
