<?php

namespace App\Services\DigitalCertificate;

use App\DTOs\DigitalCertificateDTO;
use DOMDocument;
use Exception;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use setasign\Fpdi\Tcpdf\Fpdi;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use RobRichards\XMLSecLibs\XMLSecurityDSig;

class DigitalCertificateService
{
    protected $params;
    public function __construct()
    {
    }
    public function withParams(DigitalCertificateDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function certificates()
    {
        $url = 'https://localhost:8443/1.0/sign';
        // CSR được tạo
        $csrData = $this->generateCSR($this->params->loginname);
        $csr = $csrData['csr'];
        // Lấy token ngắn hạn để ký
        $jwt = $this->getStepCaTokenSign($this->params->loginname);
        // Gửi yêu cầu tới Step CA API
        $notAfter = Carbon::now('UTC')->addDays(365)->toIso8601String(); // 1 năm
        $notBefore = Carbon::now('UTC')->toIso8601String(); // Hiệu lực ngay
        // $notBefore = Carbon::now('UTC')->subMonths(6)->toIso8601String(); // Test hiệu lực cách đó 6 tháng

        $data = [
            'csr' => $csr,
            'ott' => $jwt,
            'notAfter' => $notAfter, // thời gian hiệu lực
            'notBefore' => $notBefore, // hiệu lực sau khoảng thời gian
        ];
        $response = Http::withOptions([
            'verify' => false
        ])->asJson()->post($url, $data);
        // Lưu kết quả
        if($response->successful()){
            $this->saveFileCertificates($this->params->loginname, $response->json());
        }
        return $response->json();
    }

    // Gia hạn chứng thư 
    public function renewCertificate()
    {

        // URL của Step CA hoặc endpoint gia hạn chứng thư
        $url = 'https://localhost:8443/1.0/renew';

        // Đường dẫn đến file certificate và private key
        $dir = storage_path("app/certificate/user/{$this->params->loginname}");
        $certPath = "{$dir}/certificate.crt";
        $keyPath = "{$dir}/private.key";

        // $cert = file_get_contents($certPath);
        // $key = file_get_contents($keyPath);


        // Gửi yêu cầu tới Step CA API để gia hạn chứng thư
        $response = Http::withOptions([
            'verify' => false,
            'cert' => $certPath, 
            'ssl_key' => $keyPath,
        ])->asJson()->post($url);
        // Lưu kết quả
        if($response->successful()){
            $this->saveFileCertificates($this->params->loginname, $response->json());
        }
        // Kiểm tra kết quả
        return $response->json();
    }

    // Thu hồi chứng chỉ
    public function revokeCertificate()
    {

        // Endpoint thu hồi chứng thư
        $url = 'https://localhost:8443/1.0/revoke';

        // Đường dẫn đến file chứng thư và private key
        $dir = storage_path("app/certificate/user/{$this->params->loginname}");
        $certPath = "{$dir}/certificate.crt";
        $keyPath = "{$dir}/private.key";
        // Lấy số seri
        $certContent = file_get_contents($certPath);
        $cert = openssl_x509_parse($certContent);
        $serial = strtoupper(($cert['serialNumber']));
        // Lý do thu hồi (có thể là "superseded", "keyCompromise", v.v. - Step CA sẽ chấp nhận lý do tùy config)
        $reason = 'superseded'; // có thể thay đổi tùy tình huống
        $jwt = $this->getStepCaTokenRevoke($this->params->loginname);
        dd($jwt, $serial);
        // Gửi yêu cầu tới Step CA để thu hồi chứng thư
        $response = Http::withOptions([
            'verify' => false,
            'ott' => $jwt,
            'cert' => $certPath,
            'ssl_key' => $keyPath,
        ])->asJson()->post($url, [
            'serial' => $serial,
            'reason' => $reason
        ]);

        return $response->json();
    }
    
    // Lấy thông tin chứng thư
    public function getCertificateInfo()
    {
        $dir = storage_path("app/certificate/user/{$this->params->loginname}");
        $certPath = "{$dir}/certificate.crt";

        if (!file_exists($certPath)) {
            return response()->json([
                'status' => 'error',
                'message' => 'Certificate not found.'
            ], 404);
        }
        

        $certContent = file_get_contents($certPath);
        $certParsed = openssl_x509_parse($certContent);
        $data = [];
        if (!$certParsed) {
            throw new \Exception("Unable to parse certificate.");
        }
        $data = [
                'subject' => $certParsed['subject'] ?? null,
                'issuer' => $certParsed['issuer'] ?? null,
                'valid_from' => date('Y-m-d H:i:s', $certParsed['validFrom_time_t']),
                'valid_to' => date('Y-m-d H:i:s', $certParsed['validTo_time_t']),
                'serial_number_hex' => strtoupper($certParsed['serialNumberHex']),
                'serial_number_decimal' => $certParsed['serialNumber'],
                'extensions' => $certParsed['extensions'] ?? [],
        ];
        return $data;
    }

    // Ký số
    public function sign()
    {
        $pdfPath = 'C:\Users\tranl\Downloads\f1ace4d9-72fc-466d-b4f5-0c01057ff76b.pdf';
        $pdfOutPath = 'C:\Users\tranl\Downloads\out_put.pdf';

        $privateKeyPath = storage_path("app/certificate/user/{$this->params->loginname}/private.key");
        $certChainPath = storage_path("app/certificate/user/{$this->params->loginname}/cert-chain.crt");
        $pdf = new Fpdi();
        $pageCount = $pdf->setSourceFile($pdfPath);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $templateId = $pdf->importPage($pageNo);
            $pdf->AddPage();
            $pdf->useTemplate($templateId);
        }

        // Thêm chữ ký số
        $privateKeyContent = file_get_contents($privateKeyPath);
        $certContent = file_get_contents($certChainPath);
        
        // Gán trực tiếp nội dung, không phải đường dẫn
        $pdf->setSignature($certContent, $privateKeyContent);
    
        // Xuất file PDF đã ký ra 
        $pdf->Output($pdfOutPath, 'F'); // 'F' = File
        // Mã hóa file PDF đã ký dưới dạng base64
        $pdfBase64 = base64_encode(file_get_contents($pdfOutPath));
        return $pdfBase64;
    }
    
    // Ký xml
    public function signXML(string $inputXmlPath, string $outputXmlPath, string $privateKeyPath, string $certPath, string $passphrase = '', $pdfPath, $pdfOutPath)
    {
        // Nếu file XML chưa tồn tại thì tạo mới
    if (!file_exists($inputXmlPath)) {
        // Tạo thư mục nếu chưa có
        $dir = dirname($inputXmlPath);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }
        $sampleXml = <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Invoice>
    <InvoiceNumber>INV-20250422-001</InvoiceNumber>
    <Date>2025-04-22</Date>
    <Customer>
        <Name>Nguyễn Văn A</Name>
    </Customer>
</Invoice>
XML;
        file_put_contents($inputXmlPath, $sampleXml);
    }
        // Load XML
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = false;
        $doc->formatOutput = true;

        if (!$doc->load($inputXmlPath)) {
            throw new Exception("Không thể tải XML từ: $inputXmlPath");
        }

        // Tạo đối tượng XMLDSig
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        // Thêm reference để xác định phần cần ký
        $objDSig->addReference(
            $doc,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
        );

        // Tạo key để ký
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        if ($passphrase) {
            $objKey->passphrase = $passphrase;
        }
        $objKey->loadKey($privateKeyPath, true);

        // Tiến hành ký
        $objDSig->sign($objKey);

        // Gắn chứng chỉ vào chữ ký
        $objDSig->add509Cert(file_get_contents($certPath));

        // Gắn chữ ký vào XML
        $objDSig->appendSignature($doc->documentElement);

        // Lưu XML đã ký
        $doc->save($outputXmlPath);

        // Đính kèm xml đã ký
        $this->attachXmlToPdfWithTcpdf($pdfPath, $outputXmlPath, $pdfOutPath);
        $pdfBase64 = base64_encode(file_get_contents($pdfOutPath));
        return $pdfBase64;
    }

    public function attachXmlToPdfWithTcpdf(string $pdfPath, string $xmlPath, string $pdfOutPath): void
    {
        // Tạo TCPDF object (chế độ kế thừa nội dung từ file PDF gốc)
        $pdf = new Fpdi();
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->AddPage();
    
        // Nhúng nội dung PDF gốc vào
        $pageCount = $pdf->setSourceFile($pdfPath);
        for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
            $tpl = $pdf->importPage($pageNo);
            $pdf->useTemplate($tpl);
            if ($pageNo < $pageCount) {
                $pdf->AddPage();
            }
        }
    
        // Đính kèm file XML
        $xmlData = file_get_contents($xmlPath);
        // Đính kèm XML dưới dạng annotation có biểu tượng ghim tại (x=15, y=30)
        $pdf->Annotation(15, 30, 5, 5, 'Signed XML file', array(
            'Subtype' => 'FileAttachment',
            'Name' => 'PushPin',
            'FS' => $xmlPath,
        ));
    
        // Xuất ra file mới
        $pdf->Output($pdfOutPath, 'F');
    }

    public function multiSignXml(string $inputXmlPath, string $privateKeyPath, string $certPath, string $passphrase = '', $base64Pdf): bool
    {
        // Nếu file XML chưa tồn tại thì tạo mới
        if (!file_exists($inputXmlPath)) {
            $dir = dirname($inputXmlPath);
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        
            $sampleXml = <<<XML
        <?xml version="1.0" encoding="UTF-8"?>
        <Document>
            <Content>$base64Pdf</Content>
        </Document>
        XML;
        
            file_put_contents($inputXmlPath, $sampleXml);
        }
        
        // Load XML gốc
        $doc = new DOMDocument();
        $doc->preserveWhiteSpace = true; // Giữ nguyên khoảng trắng ban đầu
        $doc->formatOutput = false;      // Không format lại XML

        if (!$doc->load($inputXmlPath)) {
            throw new Exception("Không thể tải XML từ: $inputXmlPath");
        }

        // Tạo đối tượng XMLSecurityDSig
        $objDSig = new XMLSecurityDSig();
        $objDSig->setCanonicalMethod(XMLSecurityDSig::EXC_C14N);

        // Thêm tham chiếu tới toàn bộ tài liệu (ký Enveloped)
        $objDSig->addReference(
            $doc,
            XMLSecurityDSig::SHA256,
            ['http://www.w3.org/2000/09/xmldsig#enveloped-signature']
        );

        // Tạo Private Key
        $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA256, ['type' => 'private']);
        if ($passphrase) {
            $objKey->passphrase = $passphrase;
        }
        $objKey->loadKey($privateKeyPath, true);

        // Ký dữ liệu
        $objDSig->sign($objKey);

        // // Tạo Object chứa SigningTime
        // $signingTimeXml = '<SigningTime>' . gmdate('Y-m-d\TH:i:s\Z') . '</SigningTime>';

        // // Thêm Object vào chữ ký
        // $objDSig->addObject($signingTimeXml);

        // Gắn chứng chỉ vào chữ ký
        $objDSig->add509Cert(file_get_contents($certPath));

        // Gắn chữ ký vào XML
        $objDSig->appendSignature($doc->documentElement);

        // Lưu XML đã ký ra file mới
        return $doc->save($inputXmlPath) !== false;
    }
    // Lấy token ngắn hạn cho việc ký
    public function getStepCaTokenSign($name)
    {
        // Cấu hình các tham số JWT
        $keyFile = 'C:\Users\tranl\laravel-priv.json'; // Tệp khóa riêng 
        $passwordFile = storage_path('app\certificate\laravel-provisioner-password.txt'); // Tệp khóa riêng 
        if (!file_exists($passwordFile)) {
            throw new \Exception("Không tìm thấy file password");
        }
        $iss = 'laravel-provisioner'; // Tên provisioner
        $aud = 'https://localhost:8443/1.0/sign'; // Dùng cho api nào
        $sub = $name; // Tên CN của chứng thư
        // Lấy thời gian hiện tại và cộng thêm 5 phút
        $exp = Carbon::now()->addMinutes(5)->timestamp;
    
        // Lệnh để tạo JWT
        $command = "step crypto jwt sign --key {$keyFile} --iss \"{$iss}\" --aud \"{$aud}\" --sub \"{$sub}\" --exp {$exp} --password-file {$passwordFile}";
    
        // Thực thi lệnh và lấy kết quả
        $output = [];
        $status = null;
        exec($command, $output, $status);
        // Kiểm tra xem lệnh có thực thi thành công không
        if ($status === 0) {
            // Trả về token đã ký
            return implode("", $output);
        }
    
        // Trả về lỗi nếu không thành công
        throw new \Exception("Lỗi khi tạo token ngắn hạn để ký");
    }

    // Lấy token ngắn hạn cho việc thu hồi
    public function getStepCaTokenRevoke($name)
    {
        // Cấu hình các tham số JWT
        $keyFile = 'C:\Users\tranl\laravel-priv.json'; // Tệp khóa riêng 
        $pubFile = 'C:\Users\tranl\laravel-pub.json'; // Tệp khóa công khai 
        $dataPub = json_decode(file_get_contents($pubFile));

        $passwordFile = storage_path('app\certificate\laravel-provisioner-password.txt'); // Tệp khóa riêng 
        if (!file_exists($passwordFile)) {
            throw new \Exception("Không tìm thấy file password");
        }
        $iss = 'laravel-provisioner'; // Tên provisioner
        $aud = 'https://localhost:8443/1.0/revoke'; // Dùng cho api nào
        $kid = $dataPub->kid; // kid của provisioner
        $sub = $name; // Tên CN của chứng thư
        // Lấy thời gian hiện tại và cộng thêm 5 phút
        $exp = Carbon::now()->addMinutes(5)->timestamp;
        
        // Lệnh để tạo JWT
        $command = "step crypto jwt sign --key {$keyFile} --iss \"{$iss}\" --aud \"{$aud}\" --sub \"{$sub}\" --kid {$kid} --exp {$exp} --password-file {$passwordFile}";
        // Thực thi lệnh và lấy kết quả
        $output = [];
        $status = null;
        exec($command, $output, $status);
        // Kiểm tra xem lệnh có thực thi thành công không
        if ($status === 0) {
            // Trả về token đã ký
            return implode("", $output);
        }
    
        // Trả về lỗi nếu không thành công
        throw new \Exception("Lỗi khi tạo token ngắn hạn để thu hồi");
    }

    // Tạo csr chứng thư cần ký
    private function generateCSR($name)
    {
        $dir = storage_path("app/certificate/user/{$name}");
        $keyFile = "{$dir}/private.key";
        $csrFile = "{$dir}/request.csr";
    
        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }
    
        $dn = "/CN={$name}/OU=Dev/O=MyOrg/L=City/ST=State/C=VN";
        $cmd = "openssl req -new -newkey rsa:2048 -nodes -keyout \"$keyFile\" -out \"$csrFile\" -subj \"$dn\" 2>&1";
    
        exec($cmd, $output, $returnVar);
    
        if ($returnVar !== 0 || !file_exists($csrFile)) {
            throw new \Exception("Không thể tạo CSR:\n" . implode("\n", $output));
        }
    
        return [
            'csr' => file_get_contents($csrFile),
            'key_path' => $keyFile,
        ];
    }

    // lưu chứng thư đã ký
    public function saveFileCertificates($name, $response){
        $path = storage_path("app/certificate/user/{$name}/");
        // Lưu file chứng chỉ
        file_put_contents($path . 'certificate.crt', $response['crt']);
        file_put_contents($path . 'ca.crt', $response['ca']);

        // Lưu file chain.crt
        $chain = implode("\n", $response['certChain']);
        file_put_contents($path . 'cert-chain.crt', $chain);

    }

}
