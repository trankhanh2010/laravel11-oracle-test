<?php

namespace App\Http\Controllers\BaseControllers;

use App\DTOs\DigitalCertificateDTO;
use App\Services\DigitalCertificate\DigitalCertificateService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
class DigitalCertificateController extends BaseApiCacheController
{
    protected $digitalCertificateService;
    protected $digitalCertificateDTO;
    public function __construct(Request $request, DigitalCertificateService $digitalCertificateService)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->digitalCertificateService = $digitalCertificateService;
        // Thêm tham số vào service
        $this->digitalCertificateDTO = new DigitalCertificateDTO(
            $this->loginname,
        );
        $this->digitalCertificateService->withParams($this->digitalCertificateDTO);
    }
    // Cấp mới chứng thư
    // Gửi đi csr cần ký
    public function certificates()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->certificates();
        return returnDataSuccess([],$data);
    }

    // Gia hạn chứng thư 
    public function renewCertificate()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->renewCertificate();
        return returnDataSuccess([],$data);
    }

    // Thu hồi chứng chỉ
    public function revokeCertificate()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->revokeCertificate();
        return returnDataSuccess([],$data);
    }

    public function getCertificateInfo()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->getCertificateInfo();
        return returnDataSuccess([],$data);
    }

    public function sign()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->sign();
        return returnDataSuccess([],$data);
    }
    
}
