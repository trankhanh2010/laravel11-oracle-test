<?php

namespace App\Http\Controllers\BaseControllers;

use App\DTOs\DocumentListVViewDTO;
use App\DTOs\PdfDTO;
use App\Events\Cache\DeleteCache;
use App\Http\Controllers\Controller;
use App\Jobs\Pdf\SplitContentDocument;
use App\Jobs\Pdf\SplitPdfHeaderContent;
use App\Models\SAR\SarPrint;
use App\Services\Model\DocumentListVViewService;
use App\Services\Pdf\PdfService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Log;

class ConvertController extends BaseApiCacheController
{
    public $pdfService;
    public $documentListVViewService;
    protected $pdfDTO;
    protected $documentListVViewDTO;

    public function __construct(
        Request $request,
        PdfService $pdfService,
        DocumentListVViewService $documentListVViewService,
    ) {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->pdfService = $pdfService;
        $this->documentListVViewService = $documentListVViewService;
        $this->orderByJoin = [];
        // Thêm tham số vào service
        $this->pdfDTO = new PdfDTO(
            $this->treatmentCode,
            $this->documentIds,
            $this->orderBy,
            $this->orderByJoin,
            $this->param,
        );
        $this->pdfService->withParams($this->pdfDTO);
        $this->documentListVViewDTO = new DocumentListVViewDTO(
            $this->documentListVViewName,
            $this->keyword,
            $this->isActive,
            $this->isDelete,
            $this->orderBy,
            $this->orderByJoin,
            $this->orderByString,
            $this->getAll,
            $this->start,
            $this->limit,
            $request,
            $this->appCreator,
            $this->appModifier,
            $this->time,
            $this->treatmentId,
            $this->documentTypeId,
            $this->treatmentCode,
            $this->param,
            $this->noCache,
            $this->documentIds,
            $this->groupBy,
        );
        $this->documentListVViewService->withParams($this->documentListVViewDTO);
    }
    public function convertSarPrintToWord($id)
    {
        // Lấy nội dung và giải mã Base64
        try {
            $content = SarPrint::findOrFail($id)->content;
        } catch (\Exception $e) {
            return;
        }

        $rtfContent = base64_decode($content);

        // Lưu RTF vào file tạm
        $rtfPath = storage_path('temp.rtf');
        file_put_contents($rtfPath, $rtfContent);

        // Xác định đường dẫn LibreOffice (chỉnh đúng đường dẫn cài đặt)
        $libreOfficePath = config('database')['connections']['libre_office']['libre_office_path'];
        // Chuyển đổi sang .docx bằng shell_exec
        $docxPath = storage_path('temp.docx');
        $command = "$libreOfficePath --headless --convert-to docx --outdir " . storage_path() . " " . escapeshellarg($rtfPath);
        shell_exec($command);

        // Kiểm tra file đã được tạo chưa
        if (!file_exists($docxPath)) {
            return response()->json(['error' => 'Chuyển đổi thất bại'], 500);
        }

        //  // 🔹 Chuyển DOCX → PDF
        //  $pdfPath = storage_path("temp.pdf");
        //  shell_exec("$libreOfficePath --headless --convert-to pdf --outdir " . storage_path() . " " . escapeshellarg($docxPath));

        //  // 🔹 Kiểm tra file PDF
        // if (!file_exists($pdfPath)) {
        //     unlink($rtfPath);
        //     unlink($docxPath);
        //     return response()->json(['error' => 'Chuyển đổi PDF thất bại'], 500);
        // }

        // Đọc file .docx và chuyển thành Base64
        $docxContent = file_get_contents($docxPath);
        $base64Docx = base64_encode($docxContent);
        // $base64Pdf  = base64_encode(file_get_contents($pdfPath));

        // Xóa file tạm
        unlink($rtfPath);
        unlink($docxPath);
        // unlink($pdfPath);

        // Trả về Base64
        return returnDataSuccess([], [
            'fileBase64' => $base64Docx,
            // 'fileBase64Pdf' => $base64Pdf,
        ]);
    }

    // public function splitHeaderContentFilePDF_UsingImage()
    // {
    //     // Tìm vị trí key header-content
    //     $parser = new Parser();
    //     $pdf = $parser->parseFile(storage_path('app/f1ace4d9-72fc-466d-b4f5-0c01057ff76b.pdf'));
    //     $pages = $pdf->getPages();
    //     $key = '{SignLibrary.SplitPdfHeaderKey}';
    //     $targetPage = null;

    //     foreach ($pages as $index => $page) {
    //         dd($page->getDataTm());
    //         if (strpos($page->getText(), $key) !== false) {
    //             $targetPage = $index + 1; // PDF page bắt đầu từ 1
    //             break;
    //         }
    //     }

    //     if ($targetPage === null) {
    //         throw new \Exception("Không tìm thấy key trong PDF");
    //     }

    //     // 2. Chuyển trang đó sang ảnh JPG bằng pdftoppm (đảm bảo hiển thị toàn bộ text)
    //     $pdfPath = storage_path('app/f1ace4d9-72fc-466d-b4f5-0c01057ff76b.pdf');
    //     $imageOutputPrefix = storage_path("app/page_$targetPage");

    //     // Lệnh pdftoppm: xuất ra file như page_1-1.jpg
    //     $process = new \Symfony\Component\Process\Process([
    //         'C:\Users\tranl\Downloads\poppler-24.08.0\Library\bin\pdftoppm.exe',
    //         '-png',
    //         '-r', '400',
    //         '-f',
    //         $targetPage,
    //         '-l',
    //         $targetPage,
    //         $pdfPath,
    //         $imageOutputPrefix
    //     ]);

    //     $process->run();

    //     if (!$process->isSuccessful()) {
    //         throw new \Exception("Lỗi khi chạy pdftoppm: " . $process->getErrorOutput());
    //     }

    //     // Tệp ảnh đầu ra sẽ là page_1-1.jpg
    //     $imagePath = $imageOutputPrefix . "-1.png";

    //     // 3. Gọi Tesseract tạo file TSV
    //     $tsvPath = storage_path("app/output.tsv");

    //     $process = new \Symfony\Component\Process\Process([
    //         'tesseract',
    //         $imagePath,
    //         str_replace('.tsv', '', $tsvPath),
    //         '--psm',
    //         '3',
    //         '-c',
    //         'tessedit_create_tsv=1',
    //         'tsv'
    //     ]);

    //     $process->run();

    //     if (!$process->isSuccessful()) {
    //         throw new \Exception("Lỗi khi chạy Tesseract: " . $process->getErrorOutput());
    //     }
    //     // 4. Đọc TSV để tìm vị trí dòng chứa key
    //     $tsvLines = file($tsvPath);
    //     $cutY = null;

    //     foreach ($tsvLines as $line) {
    //         $columns = explode("\t", $line);

    //         // Cột text là cột thứ 11 (bắt đầu từ 0)
    //         if (count($columns) > 11 && strpos($columns[11], $key) !== false) {
    //             $top = (int)$columns[7];      // toạ độ top
    //             $height = (int)$columns[8];   // chiều cao
    //             $cutY = $top + $height;       // Y cần cắt (ngay dưới dòng key)
    //             break;
    //         }
    //     }

    //     if ($cutY === null) {
    //         throw new \Exception("Không tìm thấy vị trí dòng key trong TSV");
    //     }

    // }

    // public function splitHeaderContentFilePDF()
    // {
    //     $parser = new Parser();
    //     $filePath = storage_path('app/f1ace4d9-72fc-466d-b4f5-0c01057ff76b.pdf');
    //     $pdf = $parser->parseFile($filePath);
    //     $pages = $pdf->getPages();
    //     $searchKey = '{SignLibrary.SplitPdfHeaderKey}';

    //     $targetPage = null;
    //     $y_point = null;

    //     foreach ($pages as $index => $page) {
    //         foreach ($page->getDataTm() as $item) {
    //             if (isset($item[1]) && $item[1] === $searchKey) {
    //                 $targetPage = $index + 1;
    //                 $y_point = $item[0][5]; // đơn vị: point
    //                 break 2;
    //             }
    //         }
    //     }

    //     if (!$targetPage) {
    //         throw new \Exception("Không tìm thấy key trong PDF");
    //     }


    //     $fpdi = new Fpdi();
    //     $pageCount = $fpdi->setSourceFile($filePath);
    //     $tplId = $fpdi->importPage($targetPage);
    //     $size = $fpdi->getTemplateSize($tplId);
    //     $pageWidth = $size['width'];
    //     $pageHeight = $size['height'];

    //     // Chuyển point -> mm
    //     // Tọa độ Y gốc dưới → phải đảo về gốc trên
    //     // Y mới = Chiều cao trang (mm) - y_point (pt → mm)
    //     $y_mm = $pageHeight - ($y_point * 0.3528);

    //     // ===========
    //     // HEADER FILE
    //     // ===========
    //     $pdfHeader = new Fpdi();
    //     $pdfHeader->setSourceFile($filePath);

    //     for ($i = 1; $i < $targetPage; $i++) {
    //         $tpl = $pdfHeader->importPage($i);
    //         $pdfHeader->addPage();
    //         $pdfHeader->useTemplate($tpl);
    //     }

    //     $tpl = $pdfHeader->importPage($targetPage);
    //     $pdfHeader->addPage();
    //     $pdfHeader->useTemplate($tpl);

    //     // Che phần dưới key bằng một hình chữ nhật trắng
    //     $pdfHeader->SetFillColor(255, 255, 255);
    //     $pdfHeader->Rect(0, $y_mm, $pageWidth, $pageHeight - $y_mm, 'F');

    //     $pdfHeader->Output(storage_path('app/split_header.pdf'), 'F');

    //     // ===========
    //     // CONTENT FILE
    //     // ===========
    //     $pdfContent = new Fpdi();
    //     $pdfContent->setSourceFile($filePath);

    //     $tpl = $pdfContent->importPage($targetPage);
    //     $pdfContent->addPage();
    //     $pdfContent->useTemplate($tpl);

    //     // Che phần trên key bằng hình chữ nhật trắng
    //     $pdfContent->SetFillColor(255, 255, 255);
    //     $pdfContent->Rect(0, 0, $pageWidth, $y_mm, 'F');

    //     // Thêm các trang sau targetPage
    //     for ($i = $targetPage + 1; $i <= $pageCount; $i++) {
    //         $tpl = $pdfContent->importPage($i);
    //         $pdfContent->addPage();
    //         $pdfContent->useTemplate($tpl);
    //     }

    //     $pdfContent->Output(storage_path('app/split_content.pdf'), 'F');
    // }

    // public function mergeContentDocument()
    // {
    //     try {
    //         if ($this->documentIds == null) {
    //             $this->errors[$this->documentIdsName] = "Thiếu danh sách tài liệu cần gộp";
    //         }
    //         if ($this->checkParam()) {
    //             return $this->checkParam();
    //         }

    //         $listUrl = $this->documentListVViewService->getPathDocumentByIds();

    //         // Tải các file PDF từ URL
    //         $listFilePath = $this->downloadFiles($listUrl);

    //         // Dispatch các job xử lý file PDF
    //         $this->dispatchPdfJobs($listFilePath);
    //         // Kiểm tra và lấy dữ liệu từ cache

    //         $pdfsToMerge = $this->checkCacheAndMerge($listFilePath);

    //         // //Chạy tuần tự
    //         // //Lặp qua từng file ->tách content, header -> thêm vào mảng
    //         //  foreach($listFilePath as $key =>$item){
    //         //     $dataPath = $this->pdfService->mergeContentDocument($item);
    //         //     if($key == 0){
    //         //         // Nếu là file đầu thì k cần
    //         //         $pdfsToMerge[] = str_replace('_final_content', '',$dataPath['final_content_path']);
    //         //     }else{
    //         //         $pdfsToMerge[] = $dataPath['final_content_path'];
    //         //     }
    //         // }

    //         // Gộp các PDF và trả về kết quả
    //         return $this->mergePdfsAndReturn($pdfsToMerge);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError('Có lỗi khi xử lý file PDF', $e);
    //     }
    // }

    public function mergeContentDocument()
    {
        try {
            if ($this->documentIds == null) {
                $this->errors[$this->documentIdsName] = "Thiếu danh sách tài liệu cần gộp";
            }
            if ($this->checkParam()) {
                return $this->checkParam();
            }

            $listUrl = $this->documentListVViewService->getPathDocumentByIds();
            // Dispatch các job xử lý file PDF (tải và tách content)
            $this->dispatchSplitContentJobs($listUrl);

            $listFilePath = $this->getNameFiles($listUrl);
            // Kiểm tra và lấy dữ liệu từ cache
            $dataPdfsToMerge = $this->checkCacheAndMergeContent($listFilePath);
            // dd($dataPdfsToMerge);
            // Gộp các content (nếu trang cuối file trước có khoảng trống đủ thì chèn trang đầu file sau vào)
            $dataMerge = $this->mergeContentPdfs($dataPdfsToMerge);
            return $dataMerge;
        } catch (\Throwable $e) {
            return writeAndThrowError('Có lỗi khi xử lý file PDF', $e);
        }
    }
    private function downloadFiles(array $listUrl)
    {
        $listFilePath = [];
        foreach ($listUrl as $key => $item) {
            // Tải file từ URL về máy
            $filePath = $this->pdfService->downloadPdfFromUrl($item);
            $listFilePath[] = $filePath;
        }
        return $listFilePath;
    }

    private function getNameFiles(array $listUrl)
    {
        $listFilePath = [];
        foreach ($listUrl as $key => $item) {
            // Tải file từ URL về máy
            $filePath = $this->pdfService->getNameFileFromUrl($item);
            $listFilePath[] = $filePath;
        }
        return $listFilePath;
    }

    private function dispatchPdfJobs(array $listFilePath)
    {
        foreach ($listFilePath as $key => $item) {
            $cacheKeySet = "cache_keys:" . $this->param; // Set để lưu danh sách key
            $nameCache = "pdf_split_result_{$this->param}_{$key}";
            if($key == 0 ){
                $prevPath = null;
            }else{
                $prevPath = $listFilePath[$key-1];
            }
            $job = new SplitPdfHeaderContent($item, $key, $nameCache, $cacheKeySet, $prevPath);
            dispatch($job);
        }
        usleep(500000); // Chờ một chút trước khi lấy dữ liệu từ cache
    }
    private function dispatchSplitContentJobs(array $listFilePath)
    {
        foreach ($listFilePath as $key => $item) {
            $cacheKeySet = "cache_keys:" . $this->param; // Set để lưu danh sách key
            $nameCache = "pdf_split_result_{$this->param}_{$key}";
            if($key == 0 ){
                $prevPath = null;
            }else{
                $prevPath = $listFilePath[$key-1];
            }
            $job = new SplitContentDocument($item, $key, $nameCache, $cacheKeySet, $prevPath);
            dispatch($job);
        }
        usleep(500000); // Chờ một chút trước khi lấy dữ liệu từ cache
    }

    private function checkCacheAndMerge(array $listFilePath)
    {
        $maxRetries = 15; // Số lần thử lại tối đa
        $retryDelay = 500000; // Thời gian chờ giữa các lần thử (500ms)
        $retries = 0;
        $allCacheFound = false;
        $pdfsToMerge = [];
        $cacheMissingKeys = array_keys($listFilePath); // Khởi tạo mảng các key cần kiểm tra
    
        while ($retries < $maxRetries) {
            $allCacheFound = true; // Giả định rằng tất cả đều có cache
    
            // Duyệt qua các file để kiểm tra cache
            foreach ($cacheMissingKeys as $key) {
                $dataPath = cache()->get("pdf_split_result_{$this->param}_{$key}");
    
                // Nếu không tìm thấy cache, đánh dấu vị trí này cần thử lại
                if (!$dataPath) {
                    $allCacheFound = false;
                    continue; // Tiếp tục thử các file khác mà không dừng vòng lặp
                }
    
                // Thêm dữ liệu vào mảng để tiếp tục
                if ($key == 0) {
                    $pdfsToMerge[] = str_replace('_final_content', '', $dataPath['final_content_path']);
                } else {
                    $pdfsToMerge[] = $dataPath['final_content_path'];
                }
    
                // Loại bỏ key khỏi danh sách cacheMissingKeys khi đã tìm thấy cache
                $cacheMissingKeys = array_diff($cacheMissingKeys, [$key]);
            }
    
            // Nếu tất cả các phần tử đều có cache, thoát khỏi vòng lặp
            if ($allCacheFound) {
                break;
            }
    
            // Nếu có cache thiếu, chờ và thử lại
            $retries++;
            usleep($retryDelay); // Chờ một chút trước khi thử lại
        }
    
        // Nếu sau số lần thử mà vẫn không tìm thấy tất cả cache, ném ra ngoại lệ
        if (!$allCacheFound) {
            throw new \Exception("Không thể hoàn thành việc xử lý PDF sau $maxRetries lần thử.");
        }
    
        return $pdfsToMerge;
    }
    
    private function checkCacheAndMergeContent(array $listFilePath)
    {
        $maxRetries = 15; // Số lần thử lại tối đa
        $retryDelay = 500000; // Thời gian chờ giữa các lần thử (500ms)
        $retries = 0;
        $allCacheFound = false;
        $pdfsToMerge = [];
        $cacheMissingKeys = array_keys($listFilePath); // Khởi tạo mảng các key cần kiểm tra
    
        while ($retries < $maxRetries) {
            $allCacheFound = true; // Giả định rằng tất cả đều có cache
    
            // Duyệt qua các file để kiểm tra cache
            foreach ($cacheMissingKeys as $key) {
                $dataPath = cache()->get("pdf_split_result_{$this->param}_{$key}");
    
                // Nếu không tìm thấy cache, đánh dấu vị trí này cần thử lại
                if (!$dataPath) {
                    $allCacheFound = false;
                    continue; // Tiếp tục thử các file khác mà không dừng vòng lặp
                }
    
                // Thêm dữ liệu vào mảng để tiếp tục
                $pdfsToMerge[] = $dataPath;

                // Loại bỏ key khỏi danh sách cacheMissingKeys khi đã tìm thấy cache
                $cacheMissingKeys = array_diff($cacheMissingKeys, [$key]);
            }
    
            // Nếu tất cả các phần tử đều có cache, thoát khỏi vòng lặp
            if ($allCacheFound) {
                break;
            }
    
            // Nếu có cache thiếu, chờ và thử lại
            $retries++;
            usleep($retryDelay); // Chờ một chút trước khi thử lại
        }
    
        // Nếu sau số lần thử mà vẫn không tìm thấy tất cả cache, ném ra ngoại lệ
        if (!$allCacheFound) {
            throw new \Exception("Không thể hoàn thành việc xử lý PDF sau $maxRetries lần thử.");
        }
    
        return $pdfsToMerge;
    }
    

    private function mergePdfsAndReturn(array $pdfsToMerge)
    {
        // Gộp các file PDF
        $data = $this->pdfService->mergePdfs($pdfsToMerge);

        // Xóa cache redis
        event(new DeleteCache($this->param));

        return returnDataSuccess([], $data);
    }
    private function mergeContentPdfs(array $pdfsToMerge)
    {
        // Gộp các file PDF
        $data = $this->pdfService->mergeContentPrevPage($pdfsToMerge);

        // Xóa cache redis
        event(new DeleteCache($this->param));

        return returnDataSuccess([], $data);
    }
}
