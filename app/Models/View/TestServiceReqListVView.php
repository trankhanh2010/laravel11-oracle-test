<?php

namespace App\Models\View;

use App\Models\HIS\SereServ;
use App\Models\HIS\TransactionType;
use App\Models\HIS\Treatment;
use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class TestServiceReqListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'v_his_test_service_req_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    protected $casts = [
        'test_service_type_list' => 'array',
    ];
    // protected $appends = [
    //     'total_vir_total_patient_price',
    //     'total_treatment_bill_amount',
    // ];
    public function testServiceTypeList()
    {
        return $this->hasMany(SereServ::class, 'service_req_id');
    }
    public function treatment()
    {
        return $this->belongsTo(Treatment::class);
    }
    // public function getTotalVirTotalPatientPriceAttribute()
    // {
    //     return $this->testServiceTypeList()
    //         ->where('is_delete', '0')
    //         ->whereNull('is_no_execute')
    //         ->sum('vir_total_patient_price');
    // }
    // public function getTotalTreatmentBillAmountAttribute()
    // {
    //     $listSubtractId = Cache::remember('transaction_type_NO_HU_list_id', now()->addMinutes(10080), function () {
    //         $data =  TransactionType::whereIn('transaction_type_code', ['NO', 'HU'])->pluck( 'id')->toArray();
    //         return $data;
    //     });
    //     if ($this->treatment) {
    //         $subtract = $this->treatment->transactions()
    //         ->where('is_delete', '0')
    //         ->whereIn('transaction_type_id', $listSubtractId)
    //         ->sum('treatment_bill_amount');
    //         $total = $this->treatment->transactions()
    //             ->where('is_delete', '0')
    //             ->sum('treatment_bill_amount');
    //         return $total - $subtract;    
    //     }
    //     return 0;
    // }
}
