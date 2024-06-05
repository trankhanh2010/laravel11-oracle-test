<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientType extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; 
    protected $table = 'HIS_patient_type ';
    protected $fillable = [
        'base_patient_type_id',
        'inherit_patient_type_ids',
        'other_pay_source_ids',
        'other_paySource_id',
        'treatment_type_ids',
    ];

    public function services()
    {
        return $this->belongsToMany(Service::class, ServicePaty::class, 'patient_type_id', 'service_id')
        ->withPivot('price','vat_ratio');
    }

    public function medi_stocks()
    {
        return $this->belongsToMany(MediStock::class, MestPatientType::class, 'patient_type_id', 'medi_stock_id');
    }
    public function reception_rooms()
    {
        return $this->hasMany(ReceptionRoom::class, 'patient_type_ids', 'id');
    }

    public function base_patient_type()
    {
        return $this->belongsTo(PatientType::class, 'base_patient_type_id', 'id');
    }

    public function treatment_types()
    {
        return TreatmentType::whereIn('id', explode(',', $this->treatment_type_ids))->get();
    }

    public function other_pay_sources()
    {
        return OtherPaySource::whereIn('id', explode(',', $this->other_pay_source_ids))->get();
    }

    public function rooms()
    {
        return $this->belongsToMany(Room::class, PatientTypeRoom::class, 'patient_type_id', 'room_id');
    }

    public function inherit_patient_types()
    {
        return PatientType::whereIn('id', explode(',', $this->inheritPatientTypeIds))->get();
    }

    public function other_pay_source()
    {
        return $this->belongsTo(OtherPaySource::class, 'other_pay_source_id', 'id');
    }
}
