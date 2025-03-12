<?php

namespace App\Models\View;

use App\Traits\dinh_dang_ten_truong;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TreatmentFeeListVView extends Model
{
    use HasFactory, dinh_dang_ten_truong;
    protected $connection = 'oracle_his';
    protected $table = 'xa_v_his_treatment_fee_list';
    public $timestamps = false;
    protected $guarded = [
        'id',
    ];
    public function services()
    {
        return $this->hasMany(TestServiceTypeListVView::class, 'tdl_treatment_id', 'id');
    }
    public function deposit_req_list_is_deposit()
    {
        return $this->hasMany(DepositReqListVView::class, 'treatment_id', 'id')
            ->whereNotNull('deposit_id')
            ->where(function ($subQuery) {
                $subQuery->orWhere('transaction_is_cancel', 0)
                    ->orWhereNull('transaction_is_cancel');
            });
    }
    public function deposit_req_list_is_not_deposit()
    {
        return $this->hasMany(DepositReqListVView::class, 'treatment_id', 'id')
            ->whereNull('deposit_id')
            ->orWhere('transaction_is_cancel', 1);
    }
    public function treatment_fee_detail()
    {
        return $this->belongsTo(TreatmentFeeDetailVView::class, 'id', 'id');
    }
}
