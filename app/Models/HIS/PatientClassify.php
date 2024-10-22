<?php

namespace App\Models\HIS;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class PatientClassify extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'his_patient_classify';
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
        if($this->bhyt_whitelist_ids != ""){
            return Cache::remember('bhyt_whitelist_ids_' . $this->bhyt_whitelist_ids, $this->time, function () {
                return BHYTWhitelist::whereIn('id', explode(',', $this->bhyt_whitelist_ids))->get();
            });
        }
        return null;
    }
    public function getMilitarryRanksAttribute()
    {
        if($this->military_rank_ids != ""){
            return Cache::remember('military_rank_ids_' . $this->military_rank_ids, $this->time, function () {
                return MilitaryRank::whereIn('id', explode(',', $this->military_rank_ids))->get();
            });
        }
        return null;
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
