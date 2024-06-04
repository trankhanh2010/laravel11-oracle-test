<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientClassify extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'HIS_Patient_Classify';
    protected $fillable = [
        'patient_type_id',
        'other_pay_source_id',
        'BHYT_whitelist_ids'
    ];

    public function patient_type()
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id', 'id');
    }

    public function orther_pay_source()
    {
        return $this->belongsTo(OtherPaySource::class, 'other_pay_source_id', 'id');
    }

    public function BHYT_whitelists()
    {
        return BHYTWhitelist::whereIn('id', explode(',', $this->BHYT_whitelist_ids))->get();
    }
}
