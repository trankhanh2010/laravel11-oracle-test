<?php

namespace App\Services\Transaction;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Exception;
use App\DTOs\TreatmentFeePaymentDTO;
use App\Repositories\DepositReqListVViewRepository;
use App\Repositories\DepositReqRepository;
use App\Repositories\SereServMomoPaymentsRepository;
use App\Repositories\TreatmentFeeListVViewRepository;
use App\Repositories\TestServiceTypeListVViewRepository;
use App\Repositories\TransactionRepository;
use App\Repositories\TreatmentFeeDetailVViewRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class TreatmentFeePaymentService
{
    protected $treatmentFeeListVViewRepository;
    protected $testServiceTypeListVViewRepository;
    protected $treatmentFeeDetailVViewRepository;
    protected $treatmentMoMoPaymentsRepository;
    protected $depositReqListVViewRepository;
    protected $transactionRepository;
    protected $depositReqRepository;
    protected $sereServMomoPayments;
    protected $params;
    protected $unit = ' VNĐ';
    protected $partnerCode;
    protected $accessKey;
    protected $secretKey;
    protected $endpointCreatePayment;
    protected $endpointCheckTransaction;
    protected $endpointRefundPayment;
    protected $returnUrl;
    protected $notifyUrl;
    public function __construct(
        TreatmentFeeListVViewRepository $treatmentFeeListVViewRepository,
        TestServiceTypeListVViewRepository $testServiceTypeListVViewRepository,
        TreatmentFeeDetailVViewRepository $treatmentFeeDetailVViewRepository,
        TreatmentMoMoPaymentsRepository $treatmentMoMoPaymentsRepository,
        SereServMomoPaymentsRepository $sereServMomoPayments,
        DepositReqListVViewRepository $depositReqListVViewRepository,
        TransactionRepository $transactionRepository,
        DepositReqRepository $depositReqRepository,
    ) {
        $this->treatmentFeeListVViewRepository = $treatmentFeeListVViewRepository;
        $this->testServiceTypeListVViewRepository = $testServiceTypeListVViewRepository;
        $this->treatmentFeeDetailVViewRepository = $treatmentFeeDetailVViewRepository;
        $this->treatmentMoMoPaymentsRepository = $treatmentMoMoPaymentsRepository;
        $this->sereServMomoPayments = $sereServMomoPayments;
        $this->depositReqListVViewRepository = $depositReqListVViewRepository;
        $this->transactionRepository = $transactionRepository;
        $this->depositReqRepository = $depositReqRepository;

        $this->partnerCode = config('database')['connections']['momo']['momo_partner_code'];
        $this->accessKey = config('database')['connections']['momo']['momo_access_key'];
        $this->secretKey = config('database')['connections']['momo']['momo_secret_key'];
        $this->endpointCreatePayment = config('database')['connections']['momo']['momo_endpoint_create_payment'];
        $this->endpointCheckTransaction = config('database')['connections']['momo']['momo_endpoint_check_transaction'];
        $this->endpointRefundPayment = config('database')['connections']['momo']['momo_endpoint_refund_payment'];
    }
    public function withParams(TreatmentFeePaymentDTO $params)
    {
        $this->params = $params;
        // Lấy URL IPN tương ứng với loại giao dịch
        if ($this->params->transactionTypeCode == 'TT') {
            $this->returnUrl = config('database')['connections']['momo']['momo_return_url_thanh_toan'];
            $this->notifyUrl = config('database')['connections']['momo']['momo_notify_url_thanh_toan'];
        }
        if ($this->params->transactionTypeCode == 'TU') {
            $this->returnUrl = config('database')['connections']['momo']['momo_return_url_tam_ung'];
            $this->notifyUrl = config('database')['connections']['momo']['momo_notify_url_tam_ung'];
        }
        return $this;
    }
    protected function getTreatmentFeeData()
    {
        $data = $this->treatmentFeeDetailVViewRepository->applyJoins();
        if ($this->params->treatmentCode) {
            $data = $this->treatmentFeeDetailVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        }
        return $data->first();
    }

    protected function getDepositReqData()
    {
        $data = $this->depositReqListVViewRepository->applyJoins();
        if ($this->params->depositReqCode) {
            $data = $this->depositReqListVViewRepository->applyDepositReqCodeFilter($data, $this->params->depositReqCode);
            $data = $this->depositReqListVViewRepository->applyIsDepositFilter($data, 0);
        }
        return $data->first();
    }

    protected function getDepositReqListData($treatment)
    {
        $data = $this->depositReqListVViewRepository->applyJoins();
        $data = $this->depositReqListVViewRepository->applyTreatmentIdFilter($data, $treatment->id);
        $data = $this->depositReqListVViewRepository->applyIsDepositFilter($data, 0);

        return $data->get();
    }
    // protected function calculateCosts($treatmentId)
    // {
    //     $listServiceType = $this->testServiceTypeListVViewRepository->applyJoins();
    //     $listServiceType = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($listServiceType, $treatmentId)->get();

    //     return [
    //         'totalVirPrice' => $listServiceType->sum('vir_total_price'),
    //         'totalHeinPrice' => $listServiceType->sum('vir_total_hein_price'),
    //         'totalPatientPrice' => $listServiceType->sum('vir_total_patient_price'),
    //     ];
    // }

    protected function generateTransactionInfo($data, $costs)
    {
        $orderId = Str::uuid();
        $requestId = Str::uuid();
        $orderInfo =
            "Thanh toan Tam Ung vien phi con thieu"
            . "; Ten BN: " . $data->tdl_patient_name
            . "; Ma dieu tri: " . $data->treatment_code
            . "; Tong chi phi: " . number_format($data->total_price) . $this->unit
            . "; BHYT thanh toan: " . number_format($data->total_hein_price) . $this->unit
            . "; BN phai thanh toan: " . number_format($data->total_patient_price) . $this->unit
            . "; Da thu: " . number_format($data->da_thu) . $this->unit
            . "; BN can nop them: " . number_format($data->fee) . $this->unit;

        return [
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => $data->fee,
            'orderInfo' => $orderInfo,
            'extraData' => '',
        ];
    }

    protected function generateTransactionDepositReqInfo($data, $costs)
    {
        $orderId = Str::uuid();
        $requestId = Str::uuid();
        $orderInfo =
            "Thanh toan Tam Ung theo yeu cau " . $data->deposit_req_code
            . "; Ten BN: " . $data->tdl_patient_name
            . "; Ma dieu tri: " . $data->treatment_code
            . "; So tien tam ung: " . number_format($data->amount) . $this->unit;

        return [
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => $data->amount,
            'orderInfo' => $orderInfo,
            'extraData' => '',
        ];
    }

    protected function generateSignature($paymentOption, $transactionInfo)
    {
        switch ($paymentOption) {
            case 'ThanhToanQRCode':
                $requestType = 'captureWallet';
                break;
            case 'ThanhToanTheQuocTe':
                $requestType = 'payWithCC';
                break;
            case 'ThanhToanTheATMNoiDia':
                $requestType = 'payWithATM';
                break;
            default:
                throw new \Exception('Invalid payment option');
        }

        $rawSignature = "accessKey={$this->accessKey}&amount={$transactionInfo['amount']}&extraData={$transactionInfo['extraData']}&ipnUrl={$this->notifyUrl}&orderId={$transactionInfo['orderId']}&orderInfo={$transactionInfo['orderInfo']}&partnerCode={$this->partnerCode}&redirectUrl={$this->returnUrl}&requestId={$transactionInfo['requestId']}&requestType=$requestType";
        $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);

        return [$requestType, $signature];
    }

    protected function sendPaymentRequest($dataMoMo, $fee)
    {
        // Kiểm tra lại phí 1 lần nữa trước khi gọi api đến momo (tránh trường hợp giao dịch đã thành công nhưng db chưa cập nhật được sẽ bị x2 link)
        try {
            if ($fee > 0) {
                $client = new Client();
                $response = $client->post($this->endpointCreatePayment, ['json' => $dataMoMo]);
                return json_decode($response->getBody(), true);
            } else return false;
        } catch (\Exception $e) {
            throw new \Exception('Error sending payment request to MoMo ' . $e);
        }
    }

    protected function formatResponseFromRepository($link, $amount, $orderInfo, $checkOtherLink = false)
    {
        return [
            'success' => true,
            'checkOtherLink' => $checkOtherLink,
            'deeplink' => $link->deep_link ?? '',
            'qrCodeUrl' => $link->qr_code_url ?? '',
            'payUrl' => $link->pay_url ?? '',
            'orderId' => $link->order_id,
            'amount' => $amount,
            'orderInfo' => $orderInfo,
            'requestId' => $link->request_id,
        ];
    }

    protected function formatResponseFromMoMo(array $response, array $transactionInfo, $checkOtherLink = false)
    {
        $dataReturn = [
            'success' => true,
            'checkOtherLink' => $checkOtherLink,
            'orderId' => $transactionInfo['orderId'],
            'amount' => $transactionInfo['amount'],
            'orderInfo' => $transactionInfo['orderInfo'],
            'requestId' => $transactionInfo['requestId'],
        ];
        if (isset($response['qrCodeUrl'])) {
            $dataReturn['qrCodeUrl'] = $response['qrCodeUrl'];
        }
        if (isset($response['payUrl'])) {
            $dataReturn['payUrl'] = $response['payUrl'];
        }
        if (isset($response['deeplink'])) {
            $dataReturn['deeplink'] = $response['deeplink'];
        }
        return $dataReturn;
    }
    public function updateDBTransactionTamUng($dataMoMo, $params)
    {
        if ($dataMoMo['resultCode'] == 0 || $dataMoMo['resultCode'] == 9000) {
            // và lấy treatmentId, treatmentCode
            $payment = $this->treatmentMoMoPaymentsRepository->getTreatmentByOrderId($dataMoMo['orderId']);

            // Nếu đã giao dịch thành công mà bị khóa viện phí thì hoàn tiền 
            $treatmentMoMoPaymentData = $this->treatmentMoMoPaymentsRepository->getByOrderId($dataMoMo['orderId']);
            $treatmentFeeData = $this->treatmentFeeDetailVViewRepository->getById($treatmentMoMoPaymentData->treatment_id);
            // Nếu hoàn tiền thành công thì ngắt không tạo các transaction trong db, còn nếu không hoàn tiền được thì vẫn tạo như bình thường để giải quyết tiền mặt
            // chỉ hoàn tiền các giao dịch = 0, = 9000 là ủy quyền k hoàn
            if ($treatmentFeeData['fee_lock_time'] != null && $dataMoMo['resultCode'] == 0) {

                // Nếu mã khác 0 => không thành công thì chạy lại việc hoàn tiền cho đến khi thành công
                $maxAttempts = 5; // Giới hạn số lần thử
                $attempt = 0;

                do {
                    $dataRefund = $this->refundPaymentMoMo($dataMoMo);
                    $attempt++;
                } while ($dataRefund['resultCode'] != 0 && $attempt < $maxAttempts);


                // Nếu hoàn tiền thành công, tức là mã = 0 thì trả về data này và dừng lại 
                if ($dataRefund['resultCode'] == 0) {
                    // tạo 1 phiếu thu với is_cancel = 1 và nội dung cancel là hủy do hoàn tiền thành công, link giao dịch tồn tại đến sau khi tờ điều trị được khóa viện phí
                    $transaction = $this->transactionRepository->createTransactionRefundSuccess($payment, $dataMoMo, $params->appCreator, $params->appModifier);
                    // Ngắt luôn để không tạo các transaction trong db và cập nhật trạng thái
                    return true;
                } 
            }
            DB::connection('oracle_his')->transaction(function () use ($payment, $dataMoMo, $params) {
                // Tạo transaction
                $transaction = $this->transactionRepository->createTransactionPaymentMoMoTamUng($payment, $dataMoMo, $params->appCreator, $params->appModifier);
                // Cập nhật bill_id và deposit_id cho treatmentMomoPayments (đã kiểm tra trong hàm updateBill)
                $this->treatmentMoMoPaymentsRepository->updateBill($payment, $transaction->id);
                // Nếu là thanh toán tạm ứng cho DepositReq thì cập nhật lại bản ghi trong his_deposit_req
                // Có deposit_req_code tức là thanh toán theo yêu cầu của khoa
                if ($payment->deposit_req_code) {
                    $depositRecord = $this->depositReqRepository->getByCode($payment->deposit_req_code);
                    if ($depositRecord) {
                        // Cập nhật deposit_id cho his_deposit_req khi transaction tạo thành công
                        $this->depositReqRepository->updateDepositId($depositRecord, $transaction->id);
                    }
                }

                // // Vô hiệu hóa các link thanh toán đã có trước khi thanh toán
                // $this->treatmentMoMoPaymentsRepository->setResultCode1005($payment->treatment_code);


                // Kiểm tra nếu payment không hợp lệ (tức đang tạo bản ghi trong his_transaction mà bên bản ghi bên his_treatment_momo_payments đã khác 1000)
                if (!($this->treatmentMoMoPaymentsRepository->checkNotifyMoMo($dataMoMo))) {
                    // nếu không hợp lệ thì ném ra lỗi và rollback lại
                    throw new Exception("Lỗi giao dịch bên MoMo và bên DB hệ thống không đồng bộ");
                }
            });
            return true;
        }else{
            return true;
        }
    }
    public function refundPaymentMoMo($dataMoMo)
    {
        /// kiểm tra xem trong db của mình đã refund lại tiền cho giao dịch momo này chưa
        /// hoàn tiền xong thì mới cập nhật lại đã hoàn tiền trong db
        /// nếu chưa thì hoàn tiền
        // dd(1);
        $orderId = Str::uuid();
        $requestId = Str::uuid();
        $transId = $dataMoMo['transId'];
        $treatmentMoMoPaymentData = $this->treatmentMoMoPaymentsRepository->getByOrderId($dataMoMo['orderId']);
        $description = 'Hoan tien ' . $treatmentMoMoPaymentData['order_info'] . '; Ma GD MOMO: '.$transId;
        // Log::error($description);
        // Thử lỗi số tiền hoàn lớn
        // $transId = 1000000;

        $rawSignature = "accessKey={$this->accessKey}&amount={$dataMoMo['amount']}&description={$description}&orderId={$orderId}&partnerCode={$this->partnerCode}&requestId={$requestId}&transId=$transId";
        $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);
        $jsonData = [
            'partnerCode' => $this->partnerCode,
            'orderId' => $orderId,
            'requestId' => $requestId,
            'amount' => $dataMoMo['amount'],
            'transId' => $transId,
            'lang' => 'vi',
            'description' => $description,
            'signature' => $signature,
        ];
        try {
            // Thử lỗi khi hoàn tiền
            // throw new \Exception('Error sending refund payment request to MoMo ');
            $client = new Client();
            // return [
            //     'resultCode' => 1080,
            // ];
            $response = $client->post($this->endpointRefundPayment, ['json' => $jsonData]);
            // Log::error($rawSignature);
            // Log::error($response->getBody()->getContents());

            return json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            return [
                'resultCode' => -1,
            ];
            // throw new \Exception('Error sending refund payment request to MoMo ' . $e);
        }
    }
    public function checkTimeLiveLinkPaymentMoMo($treatment_code, $requestType, $fee)
    {
        $dataReturn = null;
        $updateSuccess = true; // nếu k có gì
        // Nếu là giao dịch thanh toán
        if ($this->params->transactionTypeCode == 'TT') {
            $dataDB = $this->treatmentMoMoPaymentsRepository->checkTT($treatment_code, $requestType, $fee);
        }
        // Nếu là giao dịch tạm ứng
        if ($this->params->transactionTypeCode == 'TU') {
            $dataDB = $this->treatmentMoMoPaymentsRepository->checkTU($treatment_code, $requestType, $fee);
        }
        // Nếu có tồn tại trong DB và check bên MoMo ra mã 1000 thì trả về, k thì trả về null
        if ($dataDB) {
            // check trạng thái bên momo
            $dataMoMo = $this->checkTransactionStatus($dataDB->order_id, $dataDB->request_id);
            // Nếu check ra mã là 0 hoặc 9000 tức là giao dịch đã thành công 
            if ($dataMoMo['data']['resultCode'] == 0 || $dataMoMo['data']['resultCode'] == 9000) {
                // thì kiểm tra xem trong db tùy theo loại TT/TU xem đã có tạo các bản ghi tương ứng cho giao dịch này chưa


                // nếu chưa thì tạo 
                // Nếu là tạm ứng
                if ($this->params->transactionTypeCode == 'TU') {
                    $updateSuccess = $this->updateDBTransactionTamUng($dataMoMo['data'], $this->params);
                }
            }

            // cập nhật lại trạng thái trong bảng his_treatment_momo_payments
            if ($dataMoMo['data']['resultCode'] != 1000) {
                $dataReturn = null;
                if ($updateSuccess) {
                    $this->treatmentMoMoPaymentsRepository->update($dataMoMo['data']);
                }
            } else {
                $dataReturn = $dataDB;
            }
        }
        return $dataReturn;
    }

    public function checkTimeLiveLinkPaymentDepositReqMoMo($deposit_req_code, $requestType, $fee)
    {
        $dataReturn = null;
        $updateSuccess = true; // nếu k có gì
        // Nếu là giao dịch tạm ứng
        if ($this->params->transactionTypeCode == 'TU') {
            $dataDB = $this->treatmentMoMoPaymentsRepository->checkDepositReq($deposit_req_code, $requestType, $fee);
        }
        // Nếu có tồn tại trong DB và check bên MoMo ra mã 1000 thì trả về, k thì trả về null
        if ($dataDB) {
            $dataMoMo = $this->checkTransactionStatus($dataDB->order_id, $dataDB->request_id);
            // Nếu check ra mã là 0 hoặc 9000 tức là giao dịch đã thành công 
            if ($dataMoMo['data']['resultCode'] == 0 || $dataMoMo['data']['resultCode'] == 9000) {
                // thì kiểm tra xem trong db tùy theo loại TT/TU xem đã có tạo các bản ghi tương ứng cho giao dịch này chưa


                // nếu chưa thì tạo 
                // Nếu là tạm ứng
                if ($this->params->transactionTypeCode == 'TU') {
                    // Tạo xong mới cập nhật payment, đang tạo mà payment đã khác 1000 thì rollback
                    $updateSuccess = $this->updateDBTransactionTamUng($dataMoMo['data'], $this->params);
                }
            }

            // cập nhật lại trạng thái trong bảng his_treatment_momo_payments
            if ($dataMoMo['data']['resultCode'] != 1000) {
                $dataReturn = null;
                if ($updateSuccess) {
                    $this->treatmentMoMoPaymentsRepository->update($dataMoMo['data']);
                }
            } else {
                $dataReturn = $dataDB;
            }
        }
        return $dataReturn;
    }
    protected function checkOtherRequestTypeLinkPayment($treatmentCode, $requestType, $fee)
    {
        $allRequestType = ['payWithATM', 'payWithCC', 'captureWallet'];
        $arrOtherRequestType = array_diff($allRequestType, [$requestType]);
        // lặp qua các requestType khác với requestType của yêu cầu
        foreach ($arrOtherRequestType as $key => $item) {
            $check = $this->checkTimeLiveLinkPaymentMoMo($treatmentCode, $item, $fee);
            if ($check) return $check;
        }
        return false;
    }

    protected function checkOtherRequestTypeLinkPaymentDepositReq($depositReqCode, $requestType, $fee)
    {
        $allRequestType = ['payWithATM', 'payWithCC', 'captureWallet'];
        $arrOtherRequestType = array_diff($allRequestType, [$requestType]);
        // lặp qua các requestType khác với requestType của yêu cầu
        foreach ($arrOtherRequestType as $key => $item) {
            $check = $this->checkTimeLiveLinkPaymentDepositReqMoMo($depositReqCode, $item, $fee);
            if ($check) return $check;
        }
        return false;
    }
    protected function getListSereServ($treatmentId)
    {
        $data = $this->testServiceTypeListVViewRepository->applyJoins();
        $data = $this->testServiceTypeListVViewRepository->applyTreatmentIdFilter($data, $treatmentId);
        $data = $this->testServiceTypeListVViewRepository->applyChuaThanhToanFilter($data);
        $data = $this->testServiceTypeListVViewRepository->applyCoPhiFilter($data);
        $data = $data->get();
        return $data;
    }

    // Thanh toán tiền viện phí còn thiếu
    public function handleCreatePayment()
    {
        try {
            $data = $this->getTreatmentFeeData();
            if (!$data || $data->fee <= 0) {
                return ['data' => ['success' => false]];
            }

            // Nếu có các yêu cầu tạm ứng thì phải thanh toán chúng trước => k trả về link thanh toán
            $depositReqListData = $this->getDepositReqListData($data);
            if ($depositReqListData->isNotEmpty()) {
                return ['data' => ['success' => false]];
            }

            $costs = null;
            $transactionInfo = $this->generateTransactionInfo($data, $costs);

            if ($this->params->paymentMethod == 'MoMo') {
                $checkOtherLink = false;
                [$requestType, $signature] = $this->generateSignature($this->params->paymentOption, $transactionInfo);

                // Nếu đã có 1 link thanh toán của phương thức bất kỳ khác với cái đang chọn (qr, atm nội địa, atm quốc tế) 
                // thì trả về link đó kèm theo checkOtherLink = true 
                // người dùng phải tiếp tục thanh toán bằng link đó hoặc phải hủy link đó rồi mới được tạo link thanh toán mới
                $otherLink = $this->checkOtherRequestTypeLinkPayment($data->treatment_code, $requestType, $data->fee);
                if ($otherLink) {
                    $checkOtherLink = true;
                    return ['data' => $this->formatResponseFromRepository($otherLink, $transactionInfo['amount'], $transactionInfo['orderInfo'], $checkOtherLink)];
                }

                // Nếu không thì kiểm tra thời gian tồn tại của link và trả về như bình thường
                $link = $this->checkTimeLiveLinkPaymentMoMo($data->treatment_code, $requestType, $data->fee);
                if ($link) {
                    // Nếu bị khóa viện phí thì k trả về link
                    $treatmentFeeData = $this->treatmentFeeDetailVViewRepository->getById($data->id);
                    if ($treatmentFeeData['fee_lock_time'] != null) {
                        return ['data' => ['success' => false]];
                    }
                    return ['data' => $this->formatResponseFromRepository($link, $transactionInfo['amount'], $transactionInfo['orderInfo'], $checkOtherLink)];
                }

                $dataMoMo = array_merge($transactionInfo, [
                    'partnerCode' => $this->partnerCode,
                    'accessKey' => $this->accessKey,
                    'redirectUrl' => $this->returnUrl,
                    'ipnUrl' => $this->notifyUrl,
                    'requestType' => $requestType,
                    'signature' => $signature,
                    'lang' => 'vi',
                ]);

                // Nếu bị khóa viện phí thì k tạo link 
                $treatmentFeeData = $this->treatmentFeeDetailVViewRepository->getById($data->id);
                if ($treatmentFeeData['fee_lock_time'] != null) {
                    return ['data' => ['success' => false]];
                }

                $response = $this->sendPaymentRequest($dataMoMo, $this->getTreatmentFeeData()->fee);
                // nếu không có response => không có phí hoặc lỗi => trả về false luôn
                if (!$response)  return ['data' => ['success' => false]];
                $dataReturn = $this->formatResponseFromMoMo($response, $transactionInfo);
                // Lưu thông tin vào database
                $dataCreate =
                    [
                        'treatmentCode' => $data->treatment_code,
                        'treatmentId' => $data->id,
                        'orderId' => $dataReturn['orderId'],
                        'requestId' => $dataReturn['requestId'],
                        'amount' => $dataReturn['amount'],
                        'resultCode' => 1000,
                        'deeplink' => $dataReturn['deeplink'] ?? '',
                        'payUrl' => $dataReturn['payUrl'] ?? '',
                        'requestType' => $requestType,
                        'qrCodeUrl' => $dataReturn['qrCodeUrl'] ?? '',
                        'transactionTypeCode' => $this->params->transactionTypeCode ?? '',
                        'orderInfo' => $dataReturn['orderInfo'] ?? '',
                    ];
                // Tạo payment momo
                $treatmentMomoPayments = $this->treatmentMoMoPaymentsRepository->create($dataCreate, $this->params->appCreator, $this->params->appModifier);

                // Nếu là giao dịch thanh toán
                if ($this->params->transactionTypeCode == 'TT') {
                    // Tạo danh sách dịch vụ cho payment
                    $listSereServ = $this->getListSereServ($data->id);
                    foreach ($listSereServ as $key => $item) {
                        $dataSereServCreate = [
                            'sere_serv_id' => $item->id,
                            'treatment_momo_payments_id' => $treatmentMomoPayments->id,
                            'transaction_type_code' => $this->params->transactionTypeCode,
                        ];
                        $this->sereServMomoPayments->create($dataSereServCreate, $this->params->appCreator, $this->params->appModifier);
                    }
                }
                // Nếu là giao dịch tạm ứng
                if ($this->params->transactionTypeCode == 'TU') {
                    // hành động cho giao dịch tạm ứng
                }
                return ['data' => $dataReturn];
            }

            return ['data' => ['success' => false]];
        } catch (\Throwable $e) {
            return writeAndThrowError('Có lỗi khi tạo link thanh toán!', $e);
        }
    }

    // Thanh toán Tạm ứng tiền theo yêu cầu tạm ứng
    public function handleCreatePaymentDepositReq()
    {
        try {
            $data = $this->getDepositReqData();
            if (!$data || $data->amount <= 0) {
                return ['data' => ['success' => false]];
            }

            $costs = null;
            $transactionInfo = $this->generateTransactionDepositReqInfo($data, $costs);

            if ($this->params->paymentMethod == 'MoMo') {
                $checkOtherLink = false;
                [$requestType, $signature] = $this->generateSignature($this->params->paymentOption, $transactionInfo);

                // Nếu đã có 1 link thanh toán của phương thức bất kỳ khác với cái đang chọn (qr, atm nội địa, atm quốc tế) 
                // thì trả về link đó kèm theo checkOtherLink = true 
                // người dùng phải tiếp tục thanh toán bằng link đó hoặc phải hủy link đó rồi mới được tạo link thanh toán mới
                $otherLink = $this->checkOtherRequestTypeLinkPaymentDepositReq($data->deposit_req_code, $requestType, $data->amount);
                if ($otherLink) {
                    $checkOtherLink = true;
                    return ['data' => $this->formatResponseFromRepository($otherLink, $transactionInfo['amount'], $transactionInfo['orderInfo'], $checkOtherLink)];
                }

                // Nếu có link thanh toán khác (như thanh toán viện phí còn thiếu)
                // Xử lý khi có link thanh toán khác    

                // Nếu không thì kiểm tra thời gian tồn tại của link và trả về như bình thường
                $link = $this->checkTimeLiveLinkPaymentDepositReqMoMo($data->deposit_req_code, $requestType, $data->amount);
                if ($link) {
                    // Nếu bị khóa viện phí thì k trả về link 
                    $treatmentFeeData = $this->treatmentFeeDetailVViewRepository->getById($data->treatment_id);
                    if ($treatmentFeeData['fee_lock_time'] != null) {
                        return ['data' => ['success' => false]];
                    }
                    return ['data' => $this->formatResponseFromRepository($link, $transactionInfo['amount'], $transactionInfo['orderInfo'], $checkOtherLink)];
                }

                $dataMoMo = array_merge($transactionInfo, [
                    'partnerCode' => $this->partnerCode,
                    'accessKey' => $this->accessKey,
                    'redirectUrl' => $this->returnUrl,
                    'ipnUrl' => $this->notifyUrl,
                    'requestType' => $requestType,
                    'signature' => $signature,
                    'lang' => 'vi',
                ]);

                // Nếu bị khóa viện phí thì k tạo link 
                $treatmentFeeData = $this->treatmentFeeDetailVViewRepository->getById($data->treatment_id);
                if ($treatmentFeeData['fee_lock_time'] != null) {
                    return ['data' => ['success' => false]];
                }

                $response = $this->sendPaymentRequest($dataMoMo, $this->getDepositReqData()->amount ?? 0);
                // nếu không có response => không có phí hoặc lỗi => trả về false luôn
                if (!$response)  return ['data' => ['success' => false]];
                $dataReturn = $this->formatResponseFromMoMo($response, $transactionInfo);
                // Lưu thông tin vào database
                $dataCreate =
                    [
                        'treatmentCode' => $data->treatment_code,
                        'treatmentId' => $data->treatment_id,
                        'orderId' => $dataReturn['orderId'],
                        'requestId' => $dataReturn['requestId'],
                        'amount' => $dataReturn['amount'],
                        'resultCode' => 1000,
                        'deeplink' => $dataReturn['deeplink'] ?? '',
                        'payUrl' => $dataReturn['payUrl'] ?? '',
                        'requestType' => $requestType,
                        'qrCodeUrl' => $dataReturn['qrCodeUrl'] ?? '',
                        'transactionTypeCode' => $this->params->transactionTypeCode ?? '',
                        'depositReqCode' => $data->deposit_req_code,
                        'orderInfo' => $dataReturn['orderInfo'] ?? '',
                    ];
                // Tạo payment momo
                $treatmentMomoPayments = $this->treatmentMoMoPaymentsRepository->createDepositReq($dataCreate, $this->params->appCreator, $this->params->appModifier);
                // Nếu là giao dịch tạm ứng
                if ($this->params->transactionTypeCode == 'TU') {
                    // hành động cho giao dịch tạm ứng
                }
                return ['data' => $dataReturn];
            }

            return ['data' => ['success' => false]];
        } catch (\Throwable $e) {
            return writeAndThrowError('Có lỗi khi tạo link thanh toán!', $e);
        }
    }

    public function checkTransactionStatus($orderId, $requestId = 0)
    {
        if (!$requestId) {
            $requestId = $this->treatmentMoMoPaymentsRepository->getByOrderId($orderId)->request_id ?? '0';
        }
        // Tạo chữ ký (signature)
        $rawSignature = "accessKey=$this->accessKey&orderId=$orderId&partnerCode=$this->partnerCode&requestId=$requestId";
        $signature = hash_hmac('sha256', $rawSignature, $this->secretKey);

        // Tạo dữ liệu gửi đến API MoMo
        $data = [
            'partnerCode' => $this->partnerCode,
            'accessKey' => $this->accessKey,
            'requestId' => $requestId,
            'orderId' => $orderId,
            'signature' => $signature,
            'lang' => 'vi',
        ];
        // Gửi request đến API MoMo
        try {
            $client = new Client();
            $response = $client->post($this->endpointCheckTransaction, ['json' => $data]);
            $body = json_decode($response->getBody(), true); // Chuyển kết quả thành mảng
            return ['data' => $body];
        } catch (\Exception $e) {
            return response()->json([
                'status' => '500',
                'message' => 'Lỗi hệ thống: ',
            ], 500);
        }
    }
}
