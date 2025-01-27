<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PayForm;
use App\Models\HIS\Transaction;
use App\Models\HIS\TransactionType;
use App\Models\HIS\Treatment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionRepository
{
    protected $transaction;
    protected $transactionType;
    protected $treatment;
    protected $payForm;
    protected $transactionTypeTTId;
    protected $transactionTypeTUId;
    protected $payFormMoMoId;
    protected $payForm03Id;
    protected $payForm06Id;

    public function __construct(
        Transaction $transaction,
        TransactionType $transactionType,
        Treatment $treatment,
        PayForm $payForm,
        )
    {
        $this->transaction = $transaction;
        $this->transactionType = $transactionType;
        $this->treatment = $treatment;
        $this->payForm = $payForm;

        $this->transactionTypeTTId = Cache::remember('transaction_type_TT_id', now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'TT')->get();
            return $data->value('id');
        });
        $this->transactionTypeTUId = Cache::remember('transaction_type_TU_id', now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'TU')->get();
            return $data->value('id');
        });
        $this->payFormMoMoId = Cache::remember('pay_form_momo_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '09')->get();
            return $data->value('id');
        });
        $this->payForm06Id = Cache::remember('pay_form_06_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '06')->get();
            return $data->value('id');
        });
        $this->payForm03Id = Cache::remember('pay_form_03_id', now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '03')->get();
            return $data->value('id');
        });
    }

    public function applyJoins()
    {
        return $this->transaction
            ->select(
                'his_transaction.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_transaction.transaction_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_transaction.transaction_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_transaction.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_transaction.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->transaction->find($id);
    }
    public function createTransactionPaymentMoMoThanhToan($payment, $data, $appCreator, $appModifier){
        $treatmentData = $this->treatment->where('id', $payment->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            // 'transaction_code' => $data['orderId'],          
            'transaction_type_id' =>  $this->transactionTypeTTId,
            'transaction_time' => now()->format('Ymdhis'),
            'transaction_date' => now()->format('Ymdhis'),
            'amount' => $data['amount'],  
            'num_order' => $data['transId'],
            'account_book_id' => 32,      
            'pay_form_id' => $this->payFormMoMoId,
            'cashier_room_id' => 1,
            'treatment_id' => $payment->treatment_id,
            'tdl_treatment_code' => $payment->treatment_code,
            'sere_serv_amount' => $data['amount'],

            // Dữ liệu dư thừa
            'tdl_patient_id' => $treatmentData->patient_id,
            'tdl_patient_code' => $treatmentData->tdl_patient_code,
            'tdl_patient_name' => $treatmentData->tdl_patient_name,
            'tdl_patient_first_name' => $treatmentData->tdl_patient_first_name,
            'tdl_patient_last_name' => $treatmentData->tdl_patient_last_name,
            'tdl_patient_dob' => $treatmentData->tdl_patient_dob,
            'tdl_patient_is_has_not_day_dob' => $treatmentData->tdl_patient_is_has_not_day_dob,
            'tdl_patient_address' => $treatmentData->tdl_patient_address,
            'tdl_patient_gender_id'  => $treatmentData->tdl_patient_gender_id,        
            'tdl_patient_gender_name'  => $treatmentData->tdl_patient_gender_name,  
            'tdl_patient_career_name'  => $treatmentData->tdl_patient_career_name,  
            'tdl_patient_work_place'  => $treatmentData->tdl_patient_work_place,  
            'tdl_patient_work_place_name'  => $treatmentData->tdl_patient_work_place_name,     
            'tdl_patient_district_code'  => $treatmentData->tdl_patient_district_code,      
            'tdl_patient_province_code' => $treatmentData->tdl_patient_province_code,  
            'tdl_patient_commune_code'  => $treatmentData->tdl_patient_commune_code,  
            'tdl_patient_military_rank_name'  => $treatmentData->tdl_patient_military_rank_name,   
            'tdl_patient_national_name'  => $treatmentData->tdl_patient_national_name,  
            'tdl_patient_relative_type' => $treatmentData->tdl_patient_relative_type,  
            'tdl_patient_relative_name'  => $treatmentData->tdl_patient_relative_name,  
            'tdl_patient_account_number'  => $treatmentData->tdl_patient_account_number,  
            'tdl_patient_tax_code'  => $treatmentData->tdl_patient_tax_code,  
        ]);
        return $data;
    }
    public function createTransactionPaymentMoMoTamUng($payment, $data, $appCreator, $appModifier){
        $treatmentData = $this->treatment->where('id', $payment->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            // 'transaction_code' => $data['orderId'],          
            'transaction_type_id' =>  $this->transactionTypeTUId,
            'transaction_time' => now()->format('Ymdhis'),
            'transaction_date' => now()->format('Ymdhis'),
            'amount' => $data['amount'],  
            'num_order' => $data['transId'],
            'account_book_id' => 32,      
            'pay_form_id' => $this->payFormMoMoId,
            'cashier_room_id' => 1,
            'treatment_id' => $payment->treatment_id,
            'tdl_treatment_code' => $payment->treatment_code,

            // Dữ liệu dư thừa
            'tdl_patient_id' => $treatmentData->patient_id,
            'tdl_patient_code' => $treatmentData->tdl_patient_code,
            'tdl_patient_name' => $treatmentData->tdl_patient_name,
            'tdl_patient_first_name' => $treatmentData->tdl_patient_first_name,
            'tdl_patient_last_name' => $treatmentData->tdl_patient_last_name,
            'tdl_patient_dob' => $treatmentData->tdl_patient_dob,
            'tdl_patient_is_has_not_day_dob' => $treatmentData->tdl_patient_is_has_not_day_dob,
            'tdl_patient_address' => $treatmentData->tdl_patient_address,
            'tdl_patient_gender_id'  => $treatmentData->tdl_patient_gender_id,        
            'tdl_patient_gender_name'  => $treatmentData->tdl_patient_gender_name,  
            'tdl_patient_career_name'  => $treatmentData->tdl_patient_career_name,  
            'tdl_patient_work_place'  => $treatmentData->tdl_patient_work_place,  
            'tdl_patient_work_place_name'  => $treatmentData->tdl_patient_work_place_name,     
            'tdl_patient_district_code'  => $treatmentData->tdl_patient_district_code,      
            'tdl_patient_province_code' => $treatmentData->tdl_patient_province_code,  
            'tdl_patient_commune_code'  => $treatmentData->tdl_patient_commune_code,  
            'tdl_patient_military_rank_name'  => $treatmentData->tdl_patient_military_rank_name,   
            'tdl_patient_national_name'  => $treatmentData->tdl_patient_national_name,  
            'tdl_patient_relative_type' => $treatmentData->tdl_patient_relative_type,  
            'tdl_patient_relative_name'  => $treatmentData->tdl_patient_relative_name,  
            'tdl_patient_account_number'  => $treatmentData->tdl_patient_account_number,  
            'tdl_patient_tax_code'  => $treatmentData->tdl_patient_tax_code,  
        ]);
        return $data;
    }
    public function createTransactionRefundSuccess($payment, $data, $appCreator, $appModifier){
        $treatmentData = $this->treatment->where('id', $payment->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            // 'transaction_code' => $data['orderId'],          
            'transaction_type_id' =>  $this->transactionTypeTUId,
            'transaction_time' => now()->format('Ymdhis'),
            'transaction_date' => now()->format('Ymdhis'),
            'amount' => $data['amount'],  
            'num_order' => $data['transId'],
            'account_book_id' => 32,      
            'pay_form_id' => $this->payFormMoMoId,
            'cashier_room_id' => 1,
            'treatment_id' => $payment->treatment_id,
            'tdl_treatment_code' => $payment->treatment_code,

            // lý do is_cancel=1 
            'is_cancel' => 1,
            'cancel_reason' => 'Hoàn tiền thành công, do thanh toán sau khi viện phí được khóa (do link thanh toán tồn tại đến sau khi viện phí được khóa)',
            'cancel_time' => now()->format('Ymdhis'),
            'cancel_loginname' =>$appModifier,
            'cancel_username' =>$appModifier,
            // Dữ liệu dư thừa
            'tdl_patient_id' => $treatmentData->patient_id,
            'tdl_patient_code' => $treatmentData->tdl_patient_code,
            'tdl_patient_name' => $treatmentData->tdl_patient_name,
            'tdl_patient_first_name' => $treatmentData->tdl_patient_first_name,
            'tdl_patient_last_name' => $treatmentData->tdl_patient_last_name,
            'tdl_patient_dob' => $treatmentData->tdl_patient_dob,
            'tdl_patient_is_has_not_day_dob' => $treatmentData->tdl_patient_is_has_not_day_dob,
            'tdl_patient_address' => $treatmentData->tdl_patient_address,
            'tdl_patient_gender_id'  => $treatmentData->tdl_patient_gender_id,        
            'tdl_patient_gender_name'  => $treatmentData->tdl_patient_gender_name,  
            'tdl_patient_career_name'  => $treatmentData->tdl_patient_career_name,  
            'tdl_patient_work_place'  => $treatmentData->tdl_patient_work_place,  
            'tdl_patient_work_place_name'  => $treatmentData->tdl_patient_work_place_name,     
            'tdl_patient_district_code'  => $treatmentData->tdl_patient_district_code,      
            'tdl_patient_province_code' => $treatmentData->tdl_patient_province_code,  
            'tdl_patient_commune_code'  => $treatmentData->tdl_patient_commune_code,  
            'tdl_patient_military_rank_name'  => $treatmentData->tdl_patient_military_rank_name,   
            'tdl_patient_national_name'  => $treatmentData->tdl_patient_national_name,  
            'tdl_patient_relative_type' => $treatmentData->tdl_patient_relative_type,  
            'tdl_patient_relative_name'  => $treatmentData->tdl_patient_relative_name,  
            'tdl_patient_account_number'  => $treatmentData->tdl_patient_account_number,  
            'tdl_patient_tax_code'  => $treatmentData->tdl_patient_tax_code,  
        ]);
        return $data;
    }
    public function createTransactionTamUng($request, $time, $appCreator, $appModifier){
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'transaction_type_id' =>  $this->transactionTypeTUId,
            'transaction_time' => $request->transaction_time,
            'amount' => $request->amount,  
            'transfer_amount' => $request->pay_form_id == $this->payForm03Id ? $request->transfer_amount : 0, // Nếu đúng hình thức tiền mặt/chuyển khoản
            'swipe_amount' => $request->pay_form_id == $this->payForm06Id ? $request->swipe_amount : 0, //Nếu đúng hình thức tiền mặt/quẹt thẻ
            'account_book_id' => $request->account_book_id,      
            'pay_form_id' => $request->pay_form_id,
            'cashier_room_id' => $request->cashier_room_id,
            'treatment_id' => $request->treatment_id,
            'description' => $request->description,
            // Dữ liệu dư thừa
            'buyer_name' => $request->buyer_name,
            'buyer_tax_code' => $request->buyer_tax_code,
            'buyer_account_number' => $request->buyer_account_number,
            'buyer_organization' => $request->buyer_organization,
            'buyer_address' => $request->buyer_address,
            'buyer_phone' => $request->buyer_phone,

            'tdl_treatment_code' => $treatmentData->treatment_code,
            'tdl_patient_id' => $treatmentData->patient_id,
            'tdl_patient_code' => $treatmentData->tdl_patient_code,
            'tdl_patient_name' => $treatmentData->tdl_patient_name,
            'tdl_patient_first_name' => $treatmentData->tdl_patient_first_name,
            'tdl_patient_last_name' => $treatmentData->tdl_patient_last_name,
            'tdl_patient_dob' => $treatmentData->tdl_patient_dob,
            'tdl_patient_is_has_not_day_dob' => $treatmentData->tdl_patient_is_has_not_day_dob,
            'tdl_patient_address' => $treatmentData->tdl_patient_address,
            'tdl_patient_gender_id'  => $treatmentData->tdl_patient_gender_id,        
            'tdl_patient_gender_name'  => $treatmentData->tdl_patient_gender_name,  
            'tdl_patient_career_name'  => $treatmentData->tdl_patient_career_name,  
            'tdl_patient_work_place'  => $treatmentData->tdl_patient_work_place,  
            'tdl_patient_work_place_name'  => $treatmentData->tdl_patient_work_place_name,     
            'tdl_patient_district_code'  => $treatmentData->tdl_patient_district_code,      
            'tdl_patient_province_code' => $treatmentData->tdl_patient_province_code,  
            'tdl_patient_commune_code'  => $treatmentData->tdl_patient_commune_code,  
            'tdl_patient_military_rank_name'  => $treatmentData->tdl_patient_military_rank_name,   
            'tdl_patient_national_name'  => $treatmentData->tdl_patient_national_name,  
            'tdl_patient_relative_type' => $treatmentData->tdl_patient_relative_type,  
            'tdl_patient_relative_name'  => $treatmentData->tdl_patient_relative_name,  
            'tdl_patient_account_number'  => $treatmentData->tdl_patient_account_number,  
            'tdl_patient_tax_code'  => $treatmentData->tdl_patient_tax_code,  
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'amount' => $request->amount,  
            'transfer_amount' => $request->pay_form_id == $this->payForm03Id ? $request->transfer_amount : 0, // Nếu đúng hình thức tiền mặt/chuyển khoản
            'swipe_amount' => $request->pay_form_id == $this->payForm06Id ? $request->swipe_amount : 0, //Nếu đúng hình thức tiền mặt/quẹt thẻ
            'pay_form_id' => $request->pay_form_id,
            'replace_reason' => $request->replace_reason,

            'buyer_name' => $request->buyer_name,
            'buyer_tax_code' => $request->buyer_tax_code,
            'buyer_account_number' => $request->buyer_account_number,
            'buyer_organization' => $request->buyer_organization,
            'buyer_address' => $request->buyer_address,
            'buyer_phone' => $request->buyer_phone,
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
}