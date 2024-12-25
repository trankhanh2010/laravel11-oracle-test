<?php 
namespace App\Repositories;

use App\Models\HIS\TreatmentMoMoPayments;

class TreatmentMoMoPaymentsRepository
{
    protected $treatmentMoMoPayments;
    public function __construct(TreatmentMoMoPayments $treatmentMoMoPayments)
    {
        $this->treatmentMoMoPayments = $treatmentMoMoPayments;
    }
    public function check($treatmentCode, $requestType){
        $data = $this->treatmentMoMoPayments
        ->where('treatment_code', $treatmentCode)
        ->where('request_type', $requestType)
        ->where('result_code', '1000')
        ->first();
        return $data;
    }
    public function checkNofityMoMo($param){
        $data = $this->treatmentMoMoPayments
        ->where('order_id', $param['orderId'])
        ->where('request_id', $param['requestId'])
        ->where('amount', $param['amount'])
        ->exists();
        return $data;
    }
    public function create($treatmentCode, $orderId, $requestId, $amount, $resultCode, $deeplink, $payUrl, $requestType, $qrCodeUrl){
        $data = $this->treatmentMoMoPayments::create([
            'treatment_code' =>  $treatmentCode,           
            'order_id' =>  $orderId,           
            'request_id' => $requestId,           
            'amount' => $amount,           
            'result_code' => $resultCode,        
            'deeplink' =>  $deeplink,    
            'pay_url' =>  $payUrl,
            'request_type' => $requestType,
            'qr_code_url' => $qrCodeUrl,
        ]);
        return $data;
    }
    public function update($orderId, $resultCode){
        $data = $this->treatmentMoMoPayments->where('order_id', $orderId)->first();
        $data->update([
            'result_code' => $resultCode,
        ]);
        return $data;
    }
}