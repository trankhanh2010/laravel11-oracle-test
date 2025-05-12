<?php

namespace App\Jobs\Momo;

use App\DTOs\TreatmentFeePaymentDTO;
use App\Repositories\PaymentFeeLockListVViewRepository;
use App\Repositories\TreatmentMoMoPaymentsRepository;
use App\Services\Transaction\TreatmentFeePaymentService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckPaymentSuccessMoMo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    protected $paymentFeeLockListVViewRepository;
    protected $treatmentMomoPaymentsRepository;
    protected $treatmentFeePaymentDTO;
    protected $treatmentFeePaymentService;
    /**
     * Create a new job instance.
     */
    public function __construct(
        PaymentFeeLockListVViewRepository $paymentFeeLockListVViewRepository,
        TreatmentMoMoPaymentsRepository $treatmentMomoPaymentsRepository,
        TreatmentFeePaymentService $treatmentFeePaymentService,
    ) {
        $this->paymentFeeLockListVViewRepository = $paymentFeeLockListVViewRepository;
        $this->treatmentMomoPaymentsRepository = $treatmentMomoPaymentsRepository;
        $this->treatmentFeePaymentService = $treatmentFeePaymentService;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $list = $this->paymentFeeLockListVViewRepository->getAll();
        foreach ($list as $key => $data) {
            // nếu có dữ liệu, kiểm tra xem có khóa viện phí không
            if ($data) {
                // nếu có khóa viện phí thì kiểm tra xem có giao dịch nào có mã 1000 không
                if ($data->is_active == 0) {
                    $listPayment = $this->treatmentMomoPaymentsRepository->getAllPayment1000($data->id);
                    // nếu có mã 1000 thì gọi lại việc lấy link để tạo lại, cập nhật bản ghi, => nếu đã khóa viện phí thì link sẽ k được trả về
                    // lặp qua từng bản ghi để kiểm tra
                    if ($listPayment->isNotEmpty()) {
                        foreach ($listPayment as $key => $item) {
                            switch ($item->request_type) {
                                case 'captureWallet':
                                    $requestType = 'ThanhToanQRCode';
                                    break;
                                case 'payWithCC':
                                    $requestType = 'ThanhToanTheQuocTe';
                                    break;
                                case 'payWithATM':
                                    $requestType = 'ThanhToanTheATMNoiDia';
                                    break;
                                default:
                                    $requestType = '';
                            }

                            $this->treatmentFeePaymentDTO = new TreatmentFeePaymentDTO(
                                'MOS_v2',
                                'MOS_v2',
                                'MoMo',
                                $requestType,
                                null,
                                $data->treatment_code,
                                $item->transaction_type_code,
                                $item->deposit_req_code,
                                null,
                                false,
                                ''
                            );
                            $this->treatmentFeePaymentService->withParams($this->treatmentFeePaymentDTO);

                            // nếu là link thanh toán viện phí còn thiếu => gọi handleCreatePayment
                            if ($item->deposit_req_code == null) {
                                $this->treatmentFeePaymentService->handleCreatePayment();
                            }

                            // nếu là link thanh toán yêu cầu tạm ứng => gọi handleCreatePaymentDepositReq
                            if ($item->deposit_req_code != null) {
                                $this->treatmentFeePaymentService->handleCreatePaymentDepositReq();
                            }
                        }
                    }
                }
            }
        }
    }
}
