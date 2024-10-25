<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'his_treatment';
    protected $fillable = [

    ];
    // protected $appends = [
    //     'server_time',
    //     'patienttype',
    //     'primary_patient_type_id',
    //     'patient_type_code',
    //     'treatment_type_code',
    //     'right_route_type_code',
    //     'level_code',
    //     'right_route_code',
    //     'hein_card_address'
    // ];

    // public function getServerTimeAttribute()
    // {
    //     return now()->format('Ymdhis');
    // }
    // public function getPatientTypeAttribute()
    // {
    //     $treatment_type_code =  $this->treatment_type()->value('treatment_type_code') ?? '';
    //     $patient_type_code = $this->patient_type()->value('patient_type_code') ?? '';
    //     $hein_medi_ord_code = $this->tdl_hein_medi_org_code ?? '';
    //     $right_route_type_code = $this->hein_approvals()->value('right_route_type_code') ?? '';
    //     $hein_card_number = $this->hein_card_number ?? '';
    //     $level_code = $this->hein_approvals()->value('level_code') ?? '';
    //     $right_route_code = $this->hein_approvals()->value('right_route_code') ?? '';
    //     $hein_card_from_time = $this->tdl_hein_card_from_time ?? '';
    //     $hein_card_to_time = $this->tdl_hein_card_to_time ?? '';
    //     $hein_card_address = $this->hein_approvals()->value('address') ?? '';
    //     $primary_patient_type_id = $this->patient_type_alters()->value('primary_patient_type_id') ?? '';
    //     $patient_type = 
    //         $treatment_type_code.'|'.
    //         $patient_type_code.'|'.
    //         $hein_medi_ord_code.'|'.
    //         $right_route_type_code.'|'.
    //         $hein_card_number.'|'.
    //         $level_code.'|'.
    //         $right_route_code.'|'.
    //         $hein_card_from_time.'|'.
    //         $hein_card_to_time.'|'.
    //         $hein_card_address.'|'.
    //         $primary_patient_type_id;
    //     return  $patient_type;
    // }
    // public function getPrimaryPatientTypeIdAttribute()
    // {
    //     $primary_patient_type_id = $this->patient_type_alters()->value('primary_patient_type_id') ?? '0';
    //     return $primary_patient_type_id;
    // }
    // public function getPatientTypeCodeAttribute()
    // {
    //     $patient_type_code = $this->patient_type()->value('patient_type_code') ?? '';
    //     return $patient_type_code;
    // }
    // public function getTreatmentTypeCodeAttribute()
    // {
    //     $treatment_type_code = $this->treatment_type()->value('treatment_type_code') ?? '';
    //     return $treatment_type_code;
    // }
    // public function getRightRouteTypeCodeAttribute()
    // {
    //     $right_route_type_code = $this->hein_approvals()->value('right_route_type_code') ?? '';
    //     return $right_route_type_code;
    // }
    // public function getLevelCodeAttribute()
    // {
    //     $level_code = $this->hein_approvals()->value('level_code') ?? '';
    //     return $level_code;
    // }
    // public function getRightRouteCodeAttribute()
    // {
    //     $right_route_code = $this->hein_approvals()->value('right_route_code') ?? '';
    //     return $right_route_code;
    // }
    // public function getHeinCardAddressAttribute()
    // {
    //     $address = $this->hein_approvals()->value('address') ?? '';
    //     return $address;
    // }
    // Phương thức để trả về giá trị của current_time
    public function accident_hurts()
    {
        return $this->hasMany(AccdientHurt::class);
    }
    public function adrs()
    {
        return $this->hasMany(Adr::class);
    }
    public function allergy_cards()
    {
        return $this->hasMany(AllergyCard::class);
    }
    public function antibiotic_requests()
    {
        return $this->hasMany(AntibioticRequest::class);
    }
    public function appointment_servs()
    {
        return $this->hasMany(AppointmentServ::class);
    }
    public function babys()
    {
        return $this->hasMany(Baby::class);
    }
    public function cares()
    {
        return $this->hasMany(Care::class);
    }
    public function care_sums()
    {
        return $this->hasMany(CareSum::class);
    }
    public function carer_card_borrows()
    {
        return $this->hasMany(CarerCardBorrow::class);
    }
    public function debates()
    {
        return $this->hasMany(Debate::class);
    }
    public function department_trans()
    {
        return $this->hasMany(DepartmentTran::class);
    }
    public function deposit_reqs()
    {
        return $this->hasMany(DepositReq::class);
    }
    public function dhsts()
    {
        return $this->hasMany(DHST::class);
    }
    public function exp_mest_maty_reqs()
    {
        return $this->hasMany(ExpMestMatyReq::class);
    }
    public function exp_mest_mety_reqs()
    {
        return $this->hasMany(ExpMestMetyReq::class);
    }
    public function hein_approvals()
    {
        return $this->hasMany(HeinApproval::class);
    }
    public function hiv_treatments()
    {
        return $this->hasMany(HivTreatment::class);
    }
    public function hold_returns()
    {
        return $this->hasMany(HoldReturn::class);
    }
    public function imp_mest_mate_reqs()
    {
        return $this->hasMany(ImpMestMateReq::class);
    }
    public function imp_mest_medi_reqs()
    {
        return $this->hasMany(ImpMestMediReq::class);
    }
    public function infusion_sums()
    {
        return $this->hasMany(InfusionSum::class);
    }
    public function medi_react_sums()
    {
        return $this->hasMany(MediReactSum::class);
    }
    public function medical_assessments()
    {
        return $this->hasMany(MedicalAssessment::class);
    }
    public function medicine_interactives()
    {
        return $this->hasMany(MedicineInteractive::class);
    }
    public function mr_check_summarys()
    {
        return $this->hasMany(MrCheckSummary::class);
    }
    public function obey_contraindis()
    {
        return $this->hasMany(ObeyContraindi::class);
    }
    public function patient_type_alters()
    {
        return $this->hasMany(PatientTypeAlter::class);
    }
    public function prepares()
    {
        return $this->hasMany(Prepare::class);
    }
    public function reha_sums()
    {
        return $this->hasMany(RehaSum::class);
    }
    public function sere_servs()
    {
        return $this->hasMany(SereServ::class, 'tdl_treatment_id');
    }
    public function severe_illness_infos()
    {
        return $this->hasMany(SevereIllnessInfo::class);
    }
    public function trackings()
    {
        return $this->hasMany(Tracking::class);
    }
    public function trans_reqs()
    {
        return $this->hasMany(TransReq::class);
    }
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }
    public function transfusion_sums()
    {
        return $this->hasMany(TransfusionSum::class);
    }
    public function treatment_bed_rooms()
    {
        return $this->hasMany(TreatmentBedRoom::class);
    }
    public function treatment_borrows()
    {
        return $this->hasMany(TreatmentBorrow::class);
    }
    public function treatment_files()
    {
        return $this->hasMany(TreatmentFile::class);
    }
    public function treatment_loggings()
    {
        return $this->hasMany(TreatmentLogging::class);
    }
    public function treatment_unlimits()
    {
        return $this->hasMany(TreatmentUnlimit::class);
    }
    public function tuberculosis_treats()
    {
        return $this->hasMany(TuberculosisTreat::class);
    }
    public function service_reqs()
    {
        return $this->hasMany(ServiceReq::class);
    }
    public function exp_mests()
    {
        return $this->hasMany(ExpMest::class);
    }
    public function imp_mests()
    {
        return $this->hasMany(ImpMest::class, 'TDL_treatment_id');
    }
    public function imp_mest_medicines()
    {
        return $this->hasManyThrough(ImpMestMedicine::class, ImpMest::class, 'tdl_treatment_id', 'imp_mest_id', 'id', 'id' );
    }
    public function imp_mest_materials()
    {
        return $this->hasManyThrough(ImpMestMaterial::class, ImpMest::class, 'tdl_treatment_id', 'imp_mest_id', 'id', 'id' );
    }
    public function imp_mest_bloods()
    {
        return $this->hasManyThrough(ImpMestBlood::class, ImpMest::class, 'tdl_treatment_id', 'imp_mest_id', 'id', 'id' );
    }
    public function service_req_metys()
    {
        return $this->hasMany(ServiceReqMety::class, 'TDL_treatment_id');
    }
    public function service_req_matys()
    {
        return $this->hasMany(ServiceReqMaty::class, 'TDL_treatment_id');
    }
    public function sere_serv_rations()
    {
        return $this->hasManyThrough(SereServRation::class, SereServ::class, 'tdl_treatment_id', 'service_req_id');
    }
    public function exp_mest_blty_reqs()
    {
        return $this->hasManyThrough(ExpMestBltyReq::class, ExpMest::class, 'tdl_treatment_id', 'exp_mest_id');
    }
    public function care_details()
    {
        return $this->hasManyThrough(CareDetail::class, Care::class);
    }
    public function patient_type(){
        return $this->belongsTo(PatientType::class, 'tdl_patient_type_id');
    }
    public function treatment_type(){
        return $this->belongsTo(TreatmentType::class, 'tdl_treatment_type_id');
    }
    public function last_department(){
        return $this->belongsTo(Department::class, 'last_department_id');
    }
    public function patient(){
        return $this->belongsTo(Patient::class, 'patient_id');
    }
}
