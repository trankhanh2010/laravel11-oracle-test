<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Treatment extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his'; // Kết nối CSDL mặc định
    protected $table = 'HIS_Treatment';
    protected $fillable = [

    ];
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
}
