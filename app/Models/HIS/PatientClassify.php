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
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    protected $appends = [
        'bhyt_whitelists',
        'militarry_ranks'
    ];
    public function getBHYTWhitelistsAttribute()
    {
        $data = BHYTWhitelist::whereIn('id', explode(',', $this->bhyt_whitelist_ids))->get();
        return $data;
    }
    public function getMilitarryRanksAttribute()
    {
        $data = MilitaryRank::whereIn('id', explode(',', $this->military_rank_ids))->get();
        return $data;
    }

    public function patient_type()
    {
        return $this->belongsTo(PatientType::class, 'patient_type_id', 'id');
    }

    public function other_pay_source()
    {
        return $this->belongsTo(OtherPaySource::class);
    }

    public function BHYT_whitelists()
    {
        return BHYTWhitelist::whereIn('id', explode(',', $this->BHYT_whitelist_ids))->get();
    }

    public function militarry_ranks()
    {
        return MilitaryRank::whereIn('id', explode(',', $this->militarry_rank_ids))->get();
    }
}
