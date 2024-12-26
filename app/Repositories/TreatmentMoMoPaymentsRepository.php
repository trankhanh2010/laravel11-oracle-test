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
    public function check($treatmentCode, $requestType){
        $data = $this->treatmentMoMoPayments
        ->where('treatment_code', $treatmentCode)
        ->where('request_type', $requestType)
        ->where('result_code', '1000')
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
            'treatment_id',
            'treatment_code'
        ])
        ->where('order_id', $orderId)
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
    public function create($data){
        $data = $this->treatmentMoMoPayments::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
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
}