<?php 
namespace App\Repositories;

use App\Models\HIS\TreatmentMoMoPayments;
use Illuminate\Support\Facades\Log;

class TreatmentMoMoPaymentsRepository
{
    protected $treatmentMoMoPayments;
    public function __construct(TreatmentMoMoPayments $treatmentMoMoPayments)
    {
        $this->treatmentMoMoPayments = $treatmentMoMoPayments;
    }
    public function check($treatmentCode, $requestType, $amount){
        $data = $this->treatmentMoMoPayments
        ->where('treatment_code', $treatmentCode)
        ->where('request_type', $requestType)
        ->where('amount', $amount)
        ->where('result_code', '1000')
        ->first();
        return $data;
    }
    public function checkTT($treatmentCode, $requestType, $amount){
        $data = $this->treatmentMoMoPayments
        ->where('treatment_code', $treatmentCode)
        ->where('request_type', $requestType)
        ->where('transaction_type_code', 'TT')
        ->where('amount', $amount)
        ->where('result_code', '1000')
        ->first();
        return $data;
    }
    public function checkTU($treatmentCode, $requestType, $amount){
        $data = $this->treatmentMoMoPayments
        ->where('treatment_code', $treatmentCode)
        ->where('request_type', $requestType)
        ->where('transaction_type_code', 'TU')
        ->where('amount', $amount)
        ->where('result_code', '1000')
        ->first();
        return $data;
    }

    public function checkDepositReq($depositReqCode, $requestType, $amount){
        $data = $this->treatmentMoMoPayments
        ->where('deposit_req_code', $depositReqCode)
        ->where('request_type', $requestType)
        ->where('transaction_type_code', 'TU')
        ->where('amount', $amount)
        ->where('result_code', '1000')
        ->whereNull('deposit_id')
        ->first();
        return $data;
    }
    public function getByOrderId($orderId){
        $data = $this->treatmentMoMoPayments
        ->where('order_id', $orderId)
        ->first();
        return $data;
    }
    public function getTreatmentByOrderId($orderId){
        $data = $this->treatmentMoMoPayments
        ->select([
            'id',
            'treatment_id',
            'treatment_code'
        ])
        ->where('order_id', $orderId)
        ->first();
        return $data;
    }
    public function checkNotifyMoMo($param){
        $data = $this->treatmentMoMoPayments
        ->where('order_id', $param['orderId'])
        ->where('request_id', $param['requestId'])
        ->where('amount', $param['amount'])
        ->where('result_code', 1000) // Trạng thái đang chờ người dùng
        ->exists();
        return $data;
    }
    public function setResultCode1005($treatmentCode){
        $data = $this->treatmentMoMoPayments
        ->where('treatment_code', $treatmentCode)
        ->where('result_code', 1000)
        ->update([
            'modify_time' => now()->format('Ymdhis'),
            'result_code' => 1005,
        ]);
        return $data;
    }
    public function create($data, $appCreator, $appModifier){
        $data = $this->treatmentMoMoPayments::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            // 'creator' => get_loginname_with_token($request->bearerToken(), $time),
            // 'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            
            'treatment_code' =>  $data['treatmentCode'], 
            'treatment_id' => $data['treatmentId'],         
            'order_id' =>  $data['orderId'],           
            'request_id' => $data['requestId'],           
            'amount' => $data['amount'],           
            'result_code' => $data['resultCode'],        
            'deeplink' =>  $data['deeplink'],    
            'pay_url' =>  $data['payUrl'],
            'request_type' => $data['requestType'],
            'qr_code_url' => $data['qrCodeUrl'],
            'transaction_type_code' => $data['transactionTypeCode'],

        ]);
        return $data;
    }
    public function createDepositReq($data, $appCreator, $appModifier){
        $data = $this->treatmentMoMoPayments::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            // 'creator' => get_loginname_with_token($request->bearerToken(), $time),
            // 'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            
            'treatment_code' =>  $data['treatmentCode'], 
            'treatment_id' => $data['treatmentId'],         
            'order_id' =>  $data['orderId'],           
            'request_id' => $data['requestId'],           
            'amount' => $data['amount'],           
            'result_code' => $data['resultCode'],        
            'deeplink' =>  $data['deeplink'],    
            'pay_url' =>  $data['payUrl'],
            'request_type' => $data['requestType'],
            'qr_code_url' => $data['qrCodeUrl'],
            'transaction_type_code' => $data['transactionTypeCode'],
            'deposit_req_code' => $data['depositReqCode'],

        ]);
        return $data;
    }
    public function update($data){
        $dataDB = $this->treatmentMoMoPayments->where('order_id', $data['orderId'])->first();
        $dataDB->update([
            'modify_time' => now()->format('Ymdhis'),
            'result_code' => $data['resultCode'],
            'trans_id' => $data['transId'] ?? '',
        ]);
        return $dataDB;
    }
    public function updateBill($data, $billId){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'bill_id' => $billId,
        ]);
        return $data;
    }
}