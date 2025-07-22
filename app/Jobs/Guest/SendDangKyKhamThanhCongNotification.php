<?php

namespace App\Jobs\Guest;

use App\Services\Mail\MailService;
use App\Services\Zalo\ZaloService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDangKyKhamThanhCongNotification implements ShouldQueue
{
    use Queueable;
    protected $responeMos;
    protected $mailService;
    protected $zaloSerivce;

    /**
     * Create a new job instance.
     */
    public function __construct($responeMos)
    {
        $this->responeMos = $responeMos;
    }

    /**
     * Execute the job.
     */
    public function handle(
        MailService $mailService,
        ZaloService $zaloSerivce,
    ): void
    {
        if(empty($this->responeMos)){
            return;
        }
        // Nếu k có dịch vụ nào => ngắt
        if(empty($this->responeMos['SereServs'])){
            return;
        }
        // Không có thông tin => ngắt
        if(empty($this->responeMos['HisPatientProfile'])){
            return;
        }
        if(empty($this->responeMos['HisPatientProfile']['HisPatient'])){
            return;
        }

        // gửi mail
        if(!empty($this->responeMos['HisPatientProfile']['HisPatient']['EMAIL'])){
            $this->mailService = $mailService;
            $this->mailService->sendThongBaoDangKyKhamThanhCong($this->responeMos['HisPatientProfile']['HisPatient']['EMAIL'], $this->responeMos);
        }
        // gửi zalo
    }
}
