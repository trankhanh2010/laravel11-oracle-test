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
            $this->loginnames,
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
        return returnDataSuccess([], $data);
    }

    // Gia hạn chứng thư 
    public function renewCertificate()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->renewCertificate();
        return returnDataSuccess([], $data);
    }

    // Thu hồi chứng chỉ
    public function revokeCertificate()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->revokeCertificate();
        return returnDataSuccess([], $data);
    }

    public function getCertificateInfo()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->getCertificateInfo();
        return returnDataSuccess([], $data);
    }

    public function sign()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $data = $this->digitalCertificateService->sign();
        return returnDataSuccess([], $data);
    }

    public function signXML()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $pdfPath = 'C:\Users\tranl\Downloads\f1ace4d9-72fc-466d-b4f5-0c01057ff76b.pdf';
        $pdfOutPath = 'C:\Users\tranl\Downloads\out_put.pdf';
        $data = $this->digitalCertificateService->signXML(
            storage_path('app/temp/'.$this->loginname.'_'.now()->format('YmdHis').'_input.xml'),
            storage_path('app/temp/'.$this->loginname.'_'.now()->format('YmdHis').'_signed_output.xml'),
            storage_path('app/certificate/user/truyenlm/private.key'),
            storage_path('app/certificate/user/truyenlm/cert-chain.crt'),
            '',
            $pdfPath,
            $pdfOutPath
        );
        return returnDataSuccess([], $data);
    }
    public function multiSignXml()
    {
        if ($this->checkParam()) {
            return $this->checkParam();
        }
        $pdfPath = 'https://apigw.vinhlong.xuyenahospital.xyz/dev/vss/fss/Upload/EMR/000000098666/9b153b60-ea35-4881-bca8-63c66f4c5c5d.pdf';
        $pdfData = file_get_contents($pdfPath); // Đọc file PDF
        $base64Pdf = base64_encode($pdfData);    // Mã hóa Base64
        foreach($this->loginnames as $key => $item){
            $data = $this->digitalCertificateService->multiSignXml(
                storage_path('app/temp/input.xml'),
                storage_path('app/certificate/user/'.$item.'/private.key'),
                storage_path('app/certificate/user/'.$item.'/certificate.crt'),
                '',
                $base64Pdf
            );
        }
        return returnDataSuccess([], $data);
    }  
}
