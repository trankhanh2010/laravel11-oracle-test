<?php

namespace App\Jobs\Xml;

use App\Services\Xml\XmlService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessXmlChunkJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $filePaths;

    public function __construct(array $filePaths)
    {
        $this->filePaths = $filePaths;
    }

    public function handle()
    {
        $xmlService = new XmlService();

        foreach ($this->filePaths as $filePath) {
            $xmlContent = file_get_contents($filePath);
            $xml = simplexml_load_string($xmlContent);

            if (!$xml) {
                Log::error("Không thể parse XML: " . basename($filePath));
                continue;
            }
            $fileName = $xmlService->getFileNameFromPath($filePath); // file hiện tại đang xử lý
            $partFileName = $xmlService->getPartFileNameFromFileName($fileName); // tách ra để lấy thời gian, patient_code, treatment_code
            $xmlService->setCurrentParam($partFileName);
            $danhSachHoSo = $xml->xpath('//DANHSACHHOSO'); // lấy bất kể cấp bậc
            try {
                DB::connection('oracle_his')->transaction(function () use ($xmlService, $danhSachHoSo) {
                    $xmlService->checkDBXML($danhSachHoSo);
                    $dataInsert = $xmlService->getListDataInsert();
                    $dataErr = $xmlService->getListDataErr();
                    dd($dataInsert[$xmlService->getCurrentFileName()],$dataErr);
                    if(empty($dataErr[$xmlService->getCurrentFileName()])){
                        // Xử lý khi k qua được validate
                        // dd('vướng validate');
                    }else{
                        // Cập nhật db
                        // dd('cập nhật db');
                    }
                });
            } catch (\Throwable $e) {
                Log::error('Lỗi khi xử lý file: '. $fileName . ' - ' . $e->getMessage());
            } finally{
                DB::disconnect();
            }
        }
    }
}
