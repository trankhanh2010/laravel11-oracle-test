<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\AccountBook;
use App\Models\HIS\CashierRoom;
use App\Models\HIS\DepositReq;
use App\Models\HIS\PayForm;
use App\Models\HIS\Transaction;
use App\Models\HIS\TransactionType;
use App\Models\HIS\Treatment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class TransactionRepository
{
    protected $transaction;
    protected $transactionType;
    protected $treatment;
    protected $payForm;
    protected $accountBook;
    protected $depositReq;
    protected $billFundRepository;
    protected $sereServBillRepository;
    protected $sereServDepositRepository;
    protected $seseDepoRepayRepository;
    protected $cashierRoom;
    protected $transactionTypeTTId;
    protected $transactionTypeTUId;
    protected $transactionTypeHUId;
    protected $payFormMoMoId;
    protected $payFormQrVietinBankId;
    protected $payForm03Id;
    protected $payForm06Id;
    protected $accountBookQrVietinbankId;
    protected $roomThuNganId;
    public function __construct(
        Transaction $transaction,
        TransactionType $transactionType,
        Treatment $treatment,
        PayForm $payForm,
        AccountBook $accountBook,
        DepositReq $depositReq,
        BillFundRepository $billFundRepository,
        SereServBillRepository $sereServBillRepository,
        SereServDepositRepository $sereServDepositRepository,
        SeseDepoRepayRepository $seseDepoRepayRepository,
        CashierRoom $cashierRoom,
    ) {
        $this->transaction = $transaction;
        $this->transactionType = $transactionType;
        $this->treatment = $treatment;
        $this->payForm = $payForm;
        $this->accountBook = $accountBook;
        $this->depositReq = $depositReq;
        $this->billFundRepository = $billFundRepository;
        $this->sereServBillRepository = $sereServBillRepository;
        $this->sereServDepositRepository = $sereServDepositRepository;
        $this->seseDepoRepayRepository = $seseDepoRepayRepository;
        $this->cashierRoom = $cashierRoom;

        $cacheKey = 'transaction_type_TT_id';
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $this->transactionTypeTTId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'TT')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'transaction_type_TU_id';
        $this->transactionTypeTUId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'TU')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'transaction_type_HU_id';
        $this->transactionTypeHUId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->transactionType->where('transaction_type_code', 'HU')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'pay_form_momo_id';
        $this->payFormMoMoId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '08')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'pay_form_qr_vietin_bank_id';
        $this->payFormQrVietinBankId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '08')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'pay_form_06_id';
        $this->payForm06Id = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '06')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'pay_form_03_id';
        $this->payForm03Id = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->payForm->where('pay_form_code', '03')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'account_book_qr_vietinbank_id';
        $this->accountBookQrVietinbankId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->accountBook->where('account_book_code', 'QRVTB')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        $cacheKey = 'cashier_room_TCKT_TN_id';
        $this->roomThuNganId = Cache::remember($cacheKey, now()->addMinutes(10080), function () {
            $data =  $this->cashierRoom->where('cashier_room_code', 'TCKT_TN')->get();
            return $data->value('id');
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
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
    public function createTransactionPaymentMoMoThanhToan($payment, $data, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $payment->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            // 'transaction_code' => $data['orderId'],          
            'transaction_type_id' =>  $this->transactionTypeTTId,
            'transaction_time' => now()->format('YmdHis'),
            'transaction_date' => now()->format('YmdHis'),
            'amount' => $data['amount'],
            'num_order' => $data['transId'],
            'account_book_id' => 32,
            'pay_form_id' => $this->payFormMoMoId,
            'cashier_room_id' => $this->roomThuNganId,
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
    public function createTransactionPaymentMoMoTamUng($payment, $data, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $payment->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            // 'transaction_code' => $data['orderId'],          
            'transaction_type_id' =>  $this->transactionTypeTUId,
            'transaction_time' => now()->format('YmdHis'),
            'transaction_date' => now()->format('YmdHis'),
            'amount' => $data['amount'],
            'num_order' => $data['transId'],
            'account_book_id' => 32,
            'pay_form_id' => $this->payFormMoMoId,
            'cashier_room_id' => $this->roomThuNganId,
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
    public function createTransactionRefundSuccess($payment, $data, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $payment->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => $appCreator,
            'modifier' => $appModifier,
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            // 'transaction_code' => $data['orderId'],          
            'transaction_type_id' =>  $this->transactionTypeTUId,
            'transaction_time' => now()->format('YmdHis'),
            'transaction_date' => now()->format('YmdHis'),
            'amount' => $data['amount'],
            'num_order' => $data['transId'],
            'account_book_id' => 32,
            'pay_form_id' => $this->payFormMoMoId,
            'cashier_room_id' => $this->roomThuNganId,
            'treatment_id' => $payment->treatment_id,
            'tdl_treatment_code' => $payment->treatment_code,

            // lý do is_cancel=1 
            'is_cancel' => 1,
            'cancel_reason' => 'Hoàn tiền thành công, do thanh toán sau khi viện phí được khóa (do link thanh toán tồn tại đến sau khi viện phí được khóa)',
            'cancel_time' => now()->format('YmdHis'),
            'cancel_loginname' => $appModifier,
            'cancel_username' => $appModifier,
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
    public function createTransactionTamUng($request, $time, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = DB::connection('oracle_his')->transaction(function () use ($request, $time, $appCreator, $appModifier, $treatmentData) {
            $data = $this->transaction::create([
                'create_time' => now()->format('YmdHis'),
                'modify_time' => now()->format('YmdHis'),
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
                'cashier_room_id' => $this->roomThuNganId,
                'cashier_loginname' => get_loginname_with_token($request->bearerToken(), $time)??'MOS_v2',
                'cashier_username' => get_username_with_token($request->bearerToken(), $time)??'MOS_v2',
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
            if($request->deposit_req_id != null){
                $recordDepositReq = $this->depositReq->find($request->deposit_req_id);
                $recordDepositReq->update([
                    'deposit_id' => $data->id,
                ]);
            }

            return $data;
        });
        return $data;
    }

    public function createTransactionHoanUng($request, $time, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        // if(!$treatmentData) return;
        $data = $this->transaction::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'transaction_type_id' =>  $this->transactionTypeHUId,
            'transaction_time' => $request->transaction_time,
            'amount' => $request->amount,
            'transfer_amount' => $request->pay_form_id == $this->payForm03Id ? $request->transfer_amount : 0, // Nếu đúng hình thức tiền mặt/chuyển khoản
            'swipe_amount' => $request->pay_form_id == $this->payForm06Id ? $request->swipe_amount : 0, //Nếu đúng hình thức tiền mặt/quẹt thẻ
            'account_book_id' => $request->account_book_id,
            'pay_form_id' => $request->pay_form_id,
            'cashier_room_id' => $this->roomThuNganId,
            'cashier_loginname' => get_loginname_with_token($request->bearerToken(), $time)??'MOS_v2',
            'cashier_username' => get_username_with_token($request->bearerToken(), $time)??'MOS_v2',
            'repay_reason_id' => $request->repay_reason_id,
            'treatment_id' => $request->treatment_id,
            'description' => $request->description,
            // Dữ liệu dư thừa
            // 'buyer_name' => $request->buyer_name,
            // 'buyer_tax_code' => $request->buyer_tax_code,
            // 'buyer_account_number' => $request->buyer_account_number,
            // 'buyer_organization' => $request->buyer_organization,
            // 'buyer_address' => $request->buyer_address,
            // 'buyer_phone' => $request->buyer_phone,

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

    public function createTransactionThanhToan($request, $time, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        $totalAmountBillFund = $request->bill_funds?array_sum(array_column($request->bill_funds, 'amount')):null; // Tổng tiền quỹ hỗ trợ
        $sereServAmount = array_sum(array_column($request->sere_servs, 'amount')); // Tổng tiền các dịch vụ thanh toán
        $data = DB::connection('oracle_his')->transaction(function () use ($request, $time, $appCreator, $appModifier, $treatmentData, $totalAmountBillFund, $sereServAmount) {
            // if(!$treatmentData) return;
            $data = $this->transaction::create([
                'create_time' => now()->format('YmdHis'),
                'modify_time' => now()->format('YmdHis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                'app_creator' => $appCreator,
                'app_modifier' => $appModifier,
                'transaction_type_id' =>  $this->transactionTypeTTId,
                'transaction_time' => $request->transaction_time,
                'amount' => $request->amount,
                'transfer_amount' => $request->pay_form_id == $this->payForm03Id ? $request->transfer_amount : 0, // Nếu đúng hình thức tiền mặt/chuyển khoản
                'swipe_amount' => $request->pay_form_id == $this->payForm06Id ? $request->swipe_amount : 0, //Nếu đúng hình thức tiền mặt/quẹt thẻ
                'account_book_id' => $request->account_book_id,
                'pay_form_id' => $request->pay_form_id,
                'cashier_room_id' => $this->roomThuNganId,
                'cashier_loginname' => get_loginname_with_token($request->bearerToken(), $time)??'MOS_v2',
                'cashier_username' => get_username_with_token($request->bearerToken(), $time)??'MOS_v2',
                'treatment_id' => $request->treatment_id,
                'description' => $request->description,
                'tdl_bill_fund_amount' => $totalAmountBillFund, // Tổng tiền quỹ thanh toán
                'sere_serv_amount' => $sereServAmount, // Tổng tiền bệnh nhân phải trả của các dịch vụ
                'kc_amount' => $request->kc_amount, //            // Kiểm tra tiền kết chuyển có = tiền đã thu k
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

            // Tạo bản ghi fund
            foreach ($request->bill_funds as $key => $item) {
                $requestCreateBillFund = $request;
                $requestCreateBillFund['bill_id'] = $data->id;
                $requestCreateBillFund['fund_id'] = $item['fund_id'];
                $requestCreateBillFund['amount'] =  $item['amount'];

                $this->billFundRepository->create($requestCreateBillFund, $time, $appCreator, $appModifier,);
            }

            // Tạo bản ghi sere_serv_bill
            foreach ($request->sere_servs as $key => $item) {
                $this->sereServBillRepository->create($item['id'], $item['amount'], $data, $appCreator, $appModifier,);
            }
            return $data;
        });
        return $data;
    }

    public function createTransactionTamThuDichVu($request, $time, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        $data = DB::connection('oracle_his')->transaction(function () use ($request, $time, $appCreator, $appModifier, $treatmentData) {
            // if(!$treatmentData) return;
            $data = $this->transaction::create([
                'create_time' => now()->format('YmdHis'),
                'modify_time' => now()->format('YmdHis'),
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
                'cashier_room_id' => $this->roomThuNganId,
                'cashier_loginname' => get_loginname_with_token($request->bearerToken(), $time)??'MOS_v2',
                'cashier_username' => get_username_with_token($request->bearerToken(), $time)??'MOS_v2',
                'treatment_id' => $request->treatment_id,
                'description' => $request->description,
                'tdl_sere_serv_deposit_count' => count($request->sere_servs),

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

            // Tạo bản ghi sere_serv_deposit
            foreach ($request->sere_servs as $key => $item) {
                $this->sereServDepositRepository->create($item['id'], $item['amount'],  $data, $appCreator, $appModifier,);
            }
            return $data;
        });
        return $data;
    }
    public function createTransactionHoanUngDichVu($request, $time, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        $data = DB::connection('oracle_his')->transaction(function () use ($request, $time, $appCreator, $appModifier, $treatmentData) {
            // if(!$treatmentData) return;
            $data = $this->transaction::create([
                'create_time' => now()->format('YmdHis'),
                'modify_time' => now()->format('YmdHis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                'app_creator' => $appCreator,
                'app_modifier' => $appModifier,
                'transaction_type_id' =>  $this->transactionTypeHUId,
                'transaction_time' => $request->transaction_time,
                'amount' => $request->amount,
                'transfer_amount' => $request->pay_form_id == $this->payForm03Id ? $request->transfer_amount : 0, // Nếu đúng hình thức tiền mặt/chuyển khoản
                'swipe_amount' => $request->pay_form_id == $this->payForm06Id ? $request->swipe_amount : 0, //Nếu đúng hình thức tiền mặt/quẹt thẻ
                'account_book_id' => $request->account_book_id,
                'pay_form_id' => $request->pay_form_id,
                'cashier_room_id' => $this->roomThuNganId,
                'cashier_loginname' => get_loginname_with_token($request->bearerToken(), $time)??'MOS_v2',
                'cashier_username' => get_username_with_token($request->bearerToken(), $time)??'MOS_v2',
                'treatment_id' => $request->treatment_id,
                'description' => $request->description,
                'repay_reason_id' => $request->repay_reason_id,

                'tdl_sese_depo_repay_count' => count($request->sere_servs),

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

            // Tạo bản ghi sese_depo_repay
            foreach ($request->sere_servs as $key => $item) {
                $this->seseDepoRepayRepository->create($item['id'], $item['amount'], $item['sere_serv_deposit_id'], $data, $appCreator, $appModifier,);
            }
            return $data;
        });
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
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
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getOrCreateTransactionVietinBank($data, $depositReqCode = '', $cashierLoginame, $cashierUsername)
    {
        $cancelReason = 'Khoi tao data QR Code thanh toan VietinBank';
        if ($depositReqCode) {
            $cancelReason = 'Khoi tao data QR Code thanh toan VietinBank cho phieu yeu cau tam ung ' . $depositReqCode;
        }

        // Nếu mà đã có transaction cũ chưa thanh toán mà khác tiền thì cập nhật lại tiền
        $dataReturn =  $this->transaction->where('treatment_id', $data['treatment_id'])
            ->where('is_cancel', 1)
            ->whereNull('cancel_reason_id')
            ->where('account_book_id', $this->accountBookQrVietinbankId)
            ->where('transaction_type_id', $this->transactionTypeTUId) // Link tạm ứng, theo yêu cầu tạm ứng, tạm thu thì chọn type là TU
            ->where('cancel_reason', $cancelReason)
            ->first();
        if (!$dataReturn) {
            $treatmentData = $this->treatment->where('id', $data['treatment_id'])->first();
            // if(!$treatmentData) return;
            $dataReturn = $this->transaction::create([
                'create_time' => now()->format('YmdHis'),
                'modify_time' => now()->format('YmdHis'),
                'creator' => $cashierLoginame??'MOS_v2',
                'modifier' => $cashierLoginame??'MOS_v2',
                'app_creator' => 'MOS_v2',
                'app_modifier' => 'MOS_v2',
                'is_active' => '1',
                'is_delete' => '0',
                'transaction_type_id' =>  $this->transactionTypeTUId,
                // 'transaction_time' => $request->transaction_time,
                'amount' => $data['amount'],
                'account_book_id' => $this->accountBookQrVietinbankId,
                'pay_form_id' => $this->payFormQrVietinBankId,
                'cashier_room_id' => $this->roomThuNganId,
                'treatment_id' => $data['treatment_id'],
                'cashier_loginname' => $cashierLoginame,
                'cashier_username' => $cashierUsername,
                'transaction_time' => now()->format('YmdHis'),
                'description' => $cancelReason,
                'cancel_reason' => $cancelReason,
                'is_cancel' => 1,
                // Dữ liệu dư thừa

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
        } else {
            if ($dataReturn['amount'] != $data['amount']) {
                $dataReturn->update([
                    'create_time' => now()->format('YmdHis'),
                    'modify_time' => now()->format('YmdHis'),
                    'modifier' => 'MOS_v2',
                    'app_modifier' => 'MOS_v2',
                    'amount' => $data['amount'],
                    'transaction_time' => now()->format('YmdHis'),
                ]);
            } else {
                $dataReturn->update([
                    'create_time' => now()->format('YmdHis'),
                    'modify_time' => now()->format('YmdHis'),
                    'modifier' => 'MOS_v2',
                    'app_modifier' => 'MOS_v2',
                    'transaction_time' => now()->format('YmdHis'),
                ]);
            }
        }

        $dataReturn =  $this->transaction->where('treatment_id', $data['treatment_id'])
            ->where('amount', $data['amount'])
            ->where('is_cancel', 1)
            ->whereNull('cancel_reason_id')
            ->where('account_book_id', $this->accountBookQrVietinbankId)
            ->where('cancel_reason', $cancelReason)
            ->first();
        return $dataReturn;
    }
    public function createTransactionTamThuDichVuVietinBank($request, $time, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        $data = DB::connection('oracle_his')->transaction(function () use ($request, $time, $appCreator, $appModifier, $treatmentData) {
            $cancelReason = 'Khoi tao data QR Code thanh toan VietinBank';
            // if(!$treatmentData) return;
            $data = $this->transaction::create([
                'create_time' => now()->format('YmdHis'),
                'modify_time' => now()->format('YmdHis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                'app_creator' => $appCreator,
                'app_modifier' => $appModifier,
                'transaction_type_id' =>  $this->transactionTypeTUId,
                'transaction_time' => $request->transaction_time,
                'amount' => $request->amount,
                'transfer_amount' => $request->pay_form_id == $this->payForm03Id ? $request->transfer_amount : 0, // Nếu đúng hình thức tiền mặt/chuyển khoản
                'swipe_amount' => $request->pay_form_id == $this->payForm06Id ? $request->swipe_amount : 0, //Nếu đúng hình thức tiền mặt/quẹt thẻ
                'account_book_id' => $this->accountBookQrVietinbankId, // Nếu mà thanh toán QR thì mặc định là sổ này
                'pay_form_id' => $request->pay_form_id,
                'cashier_room_id' => $this->roomThuNganId,
                'cashier_loginname' => get_loginname_with_token($request->bearerToken(), $time)??'MOS_v2',
                'cashier_username' => get_username_with_token($request->bearerToken(), $time)??'MOS_v2',
                'treatment_id' => $request->treatment_id,
                'description' => $cancelReason,
                'cancel_reason' => $cancelReason,
                'is_cancel' => 1,
                'tdl_sere_serv_deposit_count' => count($request->sere_servs),

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

            // Tạo bản ghi sere_serv_deposit
            foreach ($request->sere_servs as $key => $item) {
                $isCancel = 1;
                $this->sereServDepositRepository->create($item['id'], $item['amount'],  $data, $appCreator, $appModifier, $isCancel); // Khi tạo data QR thì phải truyền isCancel = 1
            }
            $data = $this->transaction->find($data->id);
            return $data;
        });
        return $data;
    }

    public function createTransactionThanhToanVietinBank($request, $time, $appCreator, $appModifier)
    {
        $treatmentData = $this->treatment->where('id', $request->treatment_id)->first();
        $totalAmountBillFund = $request->bill_funds?array_sum(array_column($request->bill_funds, 'amount')):null; // Tổng tiền quỹ hỗ trợ
        $sereServAmount = array_sum(array_column($request->sere_servs, 'amount')); // Tổng tiền các dịch vụ thanh toán
        $data = DB::connection('oracle_his')->transaction(function () use ($request, $time, $appCreator, $appModifier, $treatmentData, $totalAmountBillFund, $sereServAmount) {
            $cancelReason = 'Khoi tao data QR Code thanh toan VietinBank';
            // if(!$treatmentData) return;
            $data = $this->transaction::create([
                'create_time' => now()->format('YmdHis'),
                'modify_time' => now()->format('YmdHis'),
                'creator' => get_loginname_with_token($request->bearerToken(), $time),
                'modifier' => get_loginname_with_token($request->bearerToken(), $time),
                'app_creator' => $appCreator,
                'app_modifier' => $appModifier,
                'transaction_type_id' =>  $this->transactionTypeTTId,
                'transaction_time' => $request->transaction_time,
                'amount' => $request->amount,
                'transfer_amount' => $request->pay_form_id == $this->payForm03Id ? $request->transfer_amount : 0, // Nếu đúng hình thức tiền mặt/chuyển khoản
                'swipe_amount' => $request->pay_form_id == $this->payForm06Id ? $request->swipe_amount : 0, //Nếu đúng hình thức tiền mặt/quẹt thẻ
                'account_book_id' => $this->accountBookQrVietinbankId, // Nếu mà thanh toán QR thì mặc định là sổ này
                'pay_form_id' => $request->pay_form_id,
                'cashier_room_id' => $this->roomThuNganId,
                'cashier_loginname' => get_loginname_with_token($request->bearerToken(), $time)??'MOS_v2',
                'cashier_username' => get_username_with_token($request->bearerToken(), $time)??'MOS_v2',
                'treatment_id' => $request->treatment_id,
                'description' => $cancelReason,
                'cancel_reason' => $cancelReason,
                'is_cancel' => 1,
                'tdl_bill_fund_amount' => $totalAmountBillFund, // Tổng tiền quỹ thanh toán
                'sere_serv_amount' => $sereServAmount, // Tổng tiền bệnh nhân phải trả của các dịch vụ
                'kc_amount' => $request->kc_amount, //            // Kiểm tra tiền kết chuyển có = tiền đã thu k

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

            // Tạo bản ghi fund
            foreach ($request->bill_funds as $key => $item) {
                $requestCreateBillFund = $request;
                $requestCreateBillFund['bill_id'] = $data->id;
                $requestCreateBillFund['fund_id'] = $item['fund_id'];
                $requestCreateBillFund['amount'] =  $item['amount'];

                $this->billFundRepository->create($requestCreateBillFund, $time, $appCreator, $appModifier,);
            }

            // Tạo bản ghi sere_serv_bill
            foreach ($request->sere_servs as $key => $item) {
                $isCancel = 1;
                $this->sereServBillRepository->create($item['id'], $item['amount'], $data, $appCreator, $appModifier, $isCancel);
            }
            $data = $this->transaction->find($data->id);
            return $data;
        });
        return $data;
    }
    public function getTransactionVietinBank($data)
    {
        $dataReturn =  $this->transaction->where('num_order', $data['orderId'])
            ->where('account_book_id', $this->accountBookQrVietinbankId)
            // ->where('amount', $data['amount'])
            // ->where('is_cancel', 1)
            // ->where('cancel_reason', 'Khoi tao data QR Code thanh toan VietinBank')
            ->first();
        return $dataReturn;
    }
    public function updateTransactionVietinBank($data)
    {
        DB::connection('oracle_his')->transaction(function () use ($data) {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => 'MOS_v2',
            'app_modifier' => 'MOS_v2',
            'is_cancel' => 0,

        ]);

        // Check thêm : 
        // + nếu là tạm thu dịch vụ thì phải cập nhật lại các bản ghi của sere_serv_deposit
        if($data->transaction_type_id = $this->transactionTypeTUId && $data->tdl_sere_serv_deposit_count > 0){
            $listSereServDeposit = $this->sereServDepositRepository->getByDepositId($data->id);
            foreach($listSereServDeposit as $key => $item){
                $this->sereServDepositRepository->updateTransactionVietinBank($item);
            }

        }
        // + nếu là thanh toán thì phải cập nhật lại các bản ghi của sere_serv_bill
        if($data->transaction_type_id = $this->transactionTypeTTId){
            $listSereServBill = $this->sereServBillRepository->getByBillId($data->id);
            foreach($listSereServBill as $key => $item){
                $this->sereServBillRepository->updateTransactionVietinBank($item);
            }
        }
        return $data;
        });
    }
    public function cancelTransaction($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'is_cancel' => 1,
            'cancel_reason' => $request->cancel_reason,
            'cancel_time' => $request->cancel_time, 
            'cancel_loginname' => get_loginname_with_token($request->bearerToken(), $time),
            'cancel_username' => get_username_with_token($request->bearerToken(), $time),
            'cancel_cashier_room_id' => $this->roomThuNganId,
            'cancel_reason_id' => $request->cancel_reason_id,
        ]);
        return $data;
    }
}
