<?php

namespace App\Services\Pdf;

use App\DTOs\PdfDTO;
use Intervention\Image\ImageManager;
use Smalot\PdfParser\Parser;
use setasign\Fpdi\Fpdi;
use Intervention\Image\Drivers\Gd\Driver;
use Imagick;

class PdfService
{
    protected $params;
    public function __construct() {}
    public function withParams(PdfDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function mergeContentDocument($filePath, $prevPdfPath = null)
    {
        $dpi = 100; // Độ phân giải sử dụng khi chuyển đổi PDF sang ảnh
        // $filePath = storage_path('app/f1ace4d9-72fc-466d-b4f5-0c01057ff76b.pdf');
        $filenameBase = pathinfo($filePath, PATHINFO_FILENAME); // Lấy tên file

        // Tìm vị trí key
        $position = $this->findSplitKeyPosition($filePath);
        $targetPage = $position['targetPage'];
        $y_point = $position['y_point'];
        $y_point_adjusted = $y_point;
        $pages = $position['pages'];
        $y_end = $this->getLastContentY($filePath, $targetPage);
        $y_end_adjusted = $y_end;

        // 2. Chuyển trang đó sang ảnh JPG bằng pdftoppm (đảm bảo hiển thị toàn bộ text)
        $pdfPath = $filePath;
        // Dùng imagick
        $pageIndex = $targetPage - 1; // Vì Imagick bắt đầu từ 0
        $imagePath = storage_path("app/{$filenameBase}_page_$targetPage.png");

        // Chuyển pdf sang ảnh
        $this->convertPdfPageToImageUsingImagick($pdfPath, $pageIndex, $dpi, $imagePath);

        $pointToPx = function ($pt) use ($dpi) {
            return ($pt / 72) * $dpi;
        };

        // Tách ảnh trước sau vị trí key
        $headerImagePath = $this->splitImageHeader($imagePath, $y_point_adjusted, $filenameBase, $pointToPx);
        $contentImagePath = $this->splitImageContent($imagePath, $y_point_adjusted, $y_end_adjusted, $filenameBase, $pointToPx);

        // // Nếu có file phía trước, thử nhét content vào trang cuối
        // if ($prevPdfPath && file_exists($prevPdfPath)) {
        //     $parser = new Parser();
        //     $pdf = $parser->parseFile($prevPdfPath);
        //     $pages = $pdf->getPages();
        //     $lastPage = count($pages) - 1;
        //     foreach ($pages[$lastPage]->getDataTm() as $item) {
        //         if (isset($item[0][5]) && trim($item[1] ?? '') !== '') {
        //             $y = $item[0][5]; // tọa độ y
        //             if ($y_end === null || $y < $y_end) {
        //                 $y_end = $y;
        //             }
        //         }
        //     }

        //     list($imgWidthPx, $imgHeightPx) = getimagesize($contentImagePath);
        //     $imgHeightPt = ($imgHeightPx / $dpi) * 72;
        //     $pageHeightPt = 842; // A4 dọc = 842pt (nếu không lấy từ file thì fix tạm)
        //     $cmToPt = function ($cm) {
        //         return ($cm / 2.54) * 72;
        //     };
        //     $availableSpace = $pageHeightPt - $y_end - $cmToPt(1);
        //     // VỪA ⇒ gắn vào trang cuối file trước
        //     if ($imgHeightPt <= $availableSpace) { 
        //         $filenameBase = pathinfo($prevPdfPath, PATHINFO_FILENAME);
        //         // 1. Convert trang cuối file trước thành ảnh
        //         $lastPageImage = storage_path("app/{$filenameBase}_prev_last_page.png");
        //         $pageIndex = $lastPage;
        //         $this->convertPdfPageToImageUsingImagick($prevPdfPath, $pageIndex, $dpi, $lastPageImage);

        //         // 2. Ghép ảnh trang cuối + ảnh content
        //         $mergedImagePath = storage_path("app/{$filenameBase}_merged_content.png");
        //         $this->mergeImagesVertically($lastPageImage, $contentImagePath, $mergedImagePath);

        //         // 3. Ghi đè ảnh content bằng ảnh đã ghép
        //         copy($mergedImagePath, $contentImagePath);

        //         // 4. Xóa trang cuối khỏi file trước (tạo file mới không có trang cuối)
        //         $pdf = new Fpdi();
        //         $pageCount = $pdf->setSourceFile($prevPdfPath);

        //         // Tạo file mới trừ trang cuối
        //         for ($i = 1; $i < $pageCount; $i++) {
        //             $tpl = $pdf->importPage($i);
        //             $size = $pdf->getTemplateSize($tpl);
        //             $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        //             $pdf->useTemplate($tpl);
        //         }

        //         // Ghi đè file trước
        //         $pdf->Output('F', $prevPdfPath);
        //     }
        // } 

        // Chuyển sang PDF
        $headerPdfPath = storage_path("app/{$filenameBase}_header.pdf");
        $contentPdfPath = storage_path("app/{$filenameBase}_content.pdf");

        $this->convertImageToPdfWithFpdi($headerImagePath, $headerPdfPath);
        $this->convertImageToPdfWithFpdi($contentImagePath, $contentPdfPath, 10);

        // Nối lại nếu header hoặc content nhiều trang 
        $pageCount = count($pages);
        // $finalHeaderPdfPath = storage_path("app/{$filenameBase}_final_header.pdf");
        $finalContentPdfPath = storage_path("app/{$filenameBase}_final_content.pdf");

        // // Xử lý header
        // if ($targetPage > 1) {
        //     $this->mergeHeaderPart($filePath, $headerPdfPath, $targetPage, $filenameBase);
        // } else {
        //     rename($headerPdfPath, $finalHeaderPdfPath);
        // }
        // Xử lý content
        if ($targetPage < $pageCount) {
            $this->mergeContentPart($filePath, $contentPdfPath, $targetPage, $filenameBase);
        } else {
            rename($contentPdfPath, $finalContentPdfPath);
        }

        // DỌn dẹp file tạm 
        $this->cleanUpFiles(
            $headerPdfPath,
            $contentPdfPath,
            $headerImagePath,
            $contentImagePath,
            $imagePath,
        );

        // Trả về 

        return [
            // 'final_header_path' => storage_path("app/{$filenameBase}_final_header.pdf"),
            'final_content_path' => storage_path("app/{$filenameBase}_final_content.pdf"),
        ];
    }
    function convertImageToPdfWithFpdi(string $imagePath, string $outputPdfPath, float $paddingTop = 0)
    {
        // Tính toán tỉ lệ ảnh so với A4
        list($imgWidth, $imgHeight) = getimagesize($imagePath); // đơn vị: pixel

        // A4: 210mm x 297mm
        $a4Width = 210;
        $a4Height = 297;

        // Tính tỉ lệ scale để ảnh vừa khít theo chiều ngang
        $scale = $a4Width / $imgWidth;
        $displayWidth = $a4Width;
        $displayHeight = $imgHeight * $scale;

        $x = 0;
        $y = $paddingTop; // <<< thêm padding top ở đây

        // Khởi tạo đối tượng FPDF
        $pdf = new Fpdi('P', 'mm', 'A4');
        // Thêm trang A4
        $pdf->AddPage();
        $pdf->Image($imagePath, $x, $y, $displayWidth, $displayHeight);
        $pdf->Output('F', $outputPdfPath);
    }
    function mergeHeaderPart(string $originalPdfPath, string $headerPdfPath, int $targetPage, string $filenameBase)
    {
        $headerOutputPath = storage_path("app/{$filenameBase}_final_header.pdf");
        $pdfHeader = new Fpdi();
        $pdfHeader->setSourceFile($originalPdfPath);

        if ($targetPage > 1) {
            for ($i = 1; $i < $targetPage; $i++) {
                $tpl = $pdfHeader->importPage($i);
                $size = $pdfHeader->getTemplateSize($tpl);
                $pdfHeader->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdfHeader->useTemplate($tpl);
            }
        }

        // Thêm trang crop header
        $pdfHeader->setSourceFile($headerPdfPath);
        $tpl = $pdfHeader->importPage(1);
        $size = $pdfHeader->getTemplateSize($tpl);
        $pdfHeader->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdfHeader->useTemplate($tpl);

        $pdfHeader->Output('F', $headerOutputPath);
    }

    function mergeContentPart(string $originalPdfPath, string $contentPdfPath, int $targetPage, string $filenameBase)
    {
        $contentOutputPath = storage_path("app/{$filenameBase}_final_content.pdf");
        $pdfContent = new Fpdi();

        // Thêm trang crop content
        $pdfContent->setSourceFile($contentPdfPath);
        $tpl = $pdfContent->importPage(1);
        $size = $pdfContent->getTemplateSize($tpl);
        $pdfContent->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdfContent->useTemplate($tpl);

        // Thêm các trang sau targetPage
        $pageCount = $pdfContent->setSourceFile($originalPdfPath);
        if ($targetPage < $pageCount) {
            for ($i = $targetPage + 1; $i <= $pageCount; $i++) {
                $tpl = $pdfContent->importPage($i);
                $size = $pdfContent->getTemplateSize($tpl);
                $pdfContent->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdfContent->useTemplate($tpl);
            }
        }

        $pdfContent->Output('F', $contentOutputPath);
    }
    function cleanUpFiles(...$paths)
    {
        foreach ($paths as $path) {
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    public function mergePdfs(array $pdfPaths)
    {
        $outputPath = storage_path('app/pdf_merged_' . time() . '.pdf');
        $pdf = new Fpdi();

        // // Nối từng trang
        foreach ($pdfPaths as $path) {
            $pageCount = $pdf->setSourceFile($path);

            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tpl = $pdf->importPage($pageNo);
                $size = $pdf->getTemplateSize($tpl);
                $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
                $pdf->useTemplate($tpl);
            }
        }


        $pdf->Output('F', $outputPath);
        $base64 = base64_encode(file_get_contents($outputPath));
        // Xóa các file gốc, header, contetn, file gộp
        foreach ($pdfPaths as $key => $path) {
            if (file_exists($path)) {
                unlink($path);
            }

            // Loại bỏ phần _final_content.pdf để lấy base name
            if ($key == 0) {
                $basePath = str_replace('.pdf', '', $path);
            } else {
                $basePath = str_replace('_final_content.pdf', '', $path);
            }


            // Xác định các file liên quan
            $relatedFiles = [
                $basePath . '_final_header.pdf',
                $basePath . '_final_content.pdf',
                $basePath . '.pdf',
                $basePath . '.png', // nếu có ảnh tạm
                $basePath . '_split_header.png',
                $basePath . '_split_content.png',
            ];

            foreach ($relatedFiles as $relatedFile) {
                if (file_exists($relatedFile)) {
                    unlink($relatedFile);
                }
            }
        }
        // unlink($outputPath); // Xóa file merge tạm
        return [
            'base64' => $base64
        ];
    }


    public function downloadPdfFromUrl(string $url): string
    {
        $normalizedUrl = str_replace('\\', '/', $url);
        $pdfContent = @file_get_contents($normalizedUrl);

        if ($pdfContent === false) {
            throw new \Exception("Không thể tải file từ URL: $normalizedUrl");
        }

        $filename = pathinfo(parse_url($normalizedUrl, PHP_URL_PATH), PATHINFO_BASENAME);
        $filePath = storage_path("app/{$filename}");

        file_put_contents($filePath, $pdfContent);

        return $filePath;
    }

    public function getNameFileFromUrl(string $url): string
    {
        $normalizedUrl = str_replace('\\', '/', $url);
        $filename = pathinfo(parse_url($normalizedUrl, PHP_URL_PATH), PATHINFO_BASENAME);
        $filePath = storage_path("app/{$filename}");

        return $filePath;
    }

    public function findSplitKeyPosition(string $filePath, string $searchKey = '{SignLibrary.SplitPdfHeaderKey}'): array
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();

        foreach ($pages as $index => $page) {
            foreach ($page->getDataTm() as $item) {
                if (isset($item[1]) && $item[1] === $searchKey) {
                    $targetPage = $index + 1;
                    $y_point = $item[0][5]; // đơn vị: point
                    return [
                        'targetPage' => $targetPage,
                        'y_point' => $y_point,
                        'pages' => $pages,
                    ];
                }
            }
        }

        throw new \Exception("Không tìm thấy key trong PDF");
    }
    function convertPdfPageToImageUsingImagick($pdfPath, $pageIndex, $dpi, $imagePath)
    {
        $imagick = new Imagick();
        $imagick->setResolution($dpi, $dpi); // DPI giống như pdftoppm
        $imagick->readImage($pdfPath . "[$pageIndex]"); // Lấy đúng trang
        $imagick->setImageFormat('png');
        $imagick->writeImage($imagePath); // Xuất ra ảnh
        $imagick->clear();
        $imagick->destroy();
    }
    function convertPdfPageToImageCroppedBottomWithY($pdfPath, $pageIndex, $dpi, $imagePath, $y_end)
    {
        $imagick = new Imagick();
        $imagick->setResolution($dpi, $dpi);
        $imagick->readImage($pdfPath . "[$pageIndex]");
        $imagick->setImageFormat('png');
        $imagick = $imagick->flattenImages();
    
        $width = $imagick->getImageWidth();
        $height = $imagick->getImageHeight();
    
        // PDF mặc định đơn vị point: 1 inch = 72 point, ảnh là pixel theo DPI
        // => tỷ lệ quy đổi: pixel = point * dpi / 72
        $y_end_pixels = intval($y_end * $dpi / 72);
    
         // Cắt giữ lại từ trên đến điểm nội dung
        $crop_height = $height - $y_end_pixels;
        $imagick->cropImage($width, $crop_height, 0, 0);
        $imagick->writeImage($imagePath);
        $imagick->clear();
        $imagick->destroy();
    }    

    function splitImageHeader($imagePath, $y_point_adjusted, $filenameBase, $pointToPx)
    {
        // Load ảnh
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);

        // Lấy chiều rộng và chiều cao ảnh
        $height = $image->height();
        $width = $image->width();
        $imageHeight = $image->height();

        // Tính toán y_px
        $y_px = $pointToPx($y_point_adjusted);
        $y_image = $imageHeight - $y_px;

        // Cắt phần Header từ top đến $y_px
        $headerImage = $image->crop($width, $y_image, 0, 0);
        // $headerImage->resizeCanvas(
        //     height: $height, 
        //     width: $width, 
        //     position: 'top'
        // );

        // Lưu ảnh header
        $headerImagePath = storage_path("app/{$filenameBase}_split_header.png");
        $headerImage->save($headerImagePath);

        return $headerImagePath;
    }
    function splitImageContent($imagePath, $y_point_adjusted, $y_end_adjusted, $filenameBase, $pointToPx)
    {
        // Load ảnh
        $manager = new ImageManager(new Driver());
        $image = $manager->read($imagePath);

        // Lấy chiều rộng và chiều cao ảnh
        $imageHeight = $image->height();
        $width = $image->width();

        // Chuyển từ point sang pixel
        $y_start_px = $pointToPx($y_point_adjusted);
        $y_end_px   = $pointToPx($y_end_adjusted);

        // Chuyển sang toạ độ pixel trong ảnh (gốc toạ độ ảnh ở góc trái trên)
        $y_start_image = $imageHeight - $y_start_px;
        $y_end_image   = $imageHeight - $y_end_px;

        // Tính chiều cao đoạn cần cắt
        $crop_height = $y_end_image - $y_start_image;

        if ($crop_height <= 0) {
            throw new \Exception("Chiều cao crop không hợp lệ: $crop_height");
        }

        // Cắt phần Content từ y_start đến y_end
        $contentImage = $image->crop($width, $crop_height, 0, $y_start_image);
        // $contentImage->resizeCanvas(
        //     height: $imageHeight, 
        //     width: $width, 
        //     position: 'top'
        // );

        // Lưu ảnh content
        $contentImagePath = storage_path("app/{$filenameBase}_split_content.png");
        $contentImage->save($contentImagePath);

        return $contentImagePath;
    }

    public function getLastContentY(string $filePath, int $pageNumber): ?float
    {
        $parser = new Parser();
        $pdf = $parser->parseFile($filePath);
        $pages = $pdf->getPages();

        if (!isset($pages[$pageNumber - 1])) {
            throw new \Exception("Trang không tồn tại");
        }

        $page = $pages[$pageNumber - 1];
        $dataTm = $page->getDataTm();

        $y_end = null;

        foreach ($dataTm as $item) {
            if (isset($item[0][5]) && trim($item[1] ?? '') !== '') {
                $y = $item[0][5]; // tọa độ y
                if ($y_end === null || $y < $y_end) {
                    $y_end = $y;
                }
            }
        }

        return $y_end;
    }
    public function getLastContentYPage($page): ?float
    {
        $dataTm = $page->getDataTm();

        $y_end = null;

        foreach ($dataTm as $item) {
            if (isset($item[0][5]) && trim($item[1] ?? '') !== '') {
                $y = $item[0][5]; // tọa độ y
                if ($y_end === null || $y < $y_end) {
                    $y_end = $y;
                }
            }
        }

        return $y_end;
    }

    function mergeImagesVertically($topImagePath, $bottomImagePath, $outputPath)
    {
        $top = new Imagick($topImagePath);
        $bottom = new Imagick($bottomImagePath);

        // Đảm bảo hai ảnh cùng chiều rộng
        $topWidth = $top->getImageWidth();
        $bottom->resizeImage($topWidth, 0, Imagick::FILTER_LANCZOS, 1);

        // Tạo canvas mới với chiều rộng như ảnh đầu và chiều cao là tổng chiều cao của cả hai ảnh
        $canvas = new Imagick();
        $canvas->newImage(
            $topWidth,
            $top->getImageHeight() + $bottom->getImageHeight(),
            '#ffffff' // Màu nền canvas nếu cần
        );

        // Ghép ảnh lên canvas (thêm ảnh thứ hai sau ảnh đầu tiên)
        $canvas->compositeImage($top, Imagick::COMPOSITE_DEFAULT, 0, 0); // Đặt ảnh đầu tiên
        $canvas->compositeImage($bottom, Imagick::COMPOSITE_DEFAULT, 0, $top->getImageHeight()); // Đặt ảnh thứ hai dưới ảnh đầu tiên

        // Đặt định dạng ảnh và lưu
        $canvas->setImageFormat('png');
        $canvas->writeImage($outputPath);
    }

    public function splitContentDocument($url, $prevPdfPath = null)
    {
        $dpi = 100; // Độ phân giải sử dụng khi chuyển đổi PDF sang ảnh
        $filePath = $this->downloadPdfFromUrl($url);
        $filenameBase = pathinfo($filePath, PATHINFO_FILENAME); // Lấy tên file

        // Tìm vị trí key
        $position = $this->findSplitKeyPosition($filePath);
        $targetPage = $position['targetPage'];
        $y_point = $position['y_point'];
        $y_point_adjusted = $y_point;
        $pages = $position['pages'];
        $pageCount = count($pages);
        $y_end = $this->getLastContentY($filePath, $targetPage);
        $y_end_adjusted = $y_end;

        // 2. Chuyển trang đó sang ảnh JPG bằng pdftoppm (đảm bảo hiển thị toàn bộ text)
        $pdfPath = $filePath;
        // Dùng imagick
        $pageIndex = $targetPage - 1; // Vì Imagick bắt đầu từ 0
        $imagePath = storage_path("app/{$filenameBase}_page_$targetPage.png");

        // Chuyển pdf sang ảnh
        if(!$prevPdfPath){
            // Nếu là page của file đầu tiên thì cắt đúng kích thước ảnh
            $this->convertPdfPageToImageCroppedBottomWithY($pdfPath, $pageIndex, $dpi, $imagePath, $y_end);
        }else{
            $this->convertPdfPageToImageUsingImagick($pdfPath, $pageIndex, $dpi, $imagePath);
        }

        $pointToPx = function ($pt) use ($dpi) {
            return ($pt / 72) * $dpi;
        };

        $dataReturn = [
            'file_path' => $filePath,
            'file_name_base' => $filenameBase,
            'total_page' =>  $pageCount,
            'target_page' => $targetPage,
            'available_space' => null,
            'img_height' => null,
        ];
        // Tách ảnh trước sau vị trí key
        // $headerImagePath = $this->splitImageHeader($imagePath, $y_point_adjusted, $filenameBase, $pointToPx);
        $contentImagePath = $this->splitImageContent($imagePath, $y_point_adjusted, $y_end_adjusted, $filenameBase, $pointToPx);

        // Lấy thêm thông tin để nối trang cuối và trang đầu
        $availableSpace = $this->getAvailableSpace($pages[$pageCount - 1]);
        $imgHeight = $this->getImgHeight($contentImagePath, $dpi);
        $dataReturn['available_space'] = $availableSpace;
        $dataReturn['img_height'] = $imgHeight;
        $dataReturn['y_end_px'] = $pointToPx($y_end_adjusted);
        // Trả về 
        return $dataReturn;
    }

    public function getAvailableSpace($page)
    {
        $y_end = $this->getLastContentYPage($page);

        foreach ($page->getDataTm() as $item) {
            if (isset($item[0][5]) && trim($item[1] ?? '') !== '') {
                $y = $item[0][5]; // tọa độ y
                if ($y_end === null || $y < $y_end) {
                    $y_end = $y;
                }
            }
        }

        $pageHeightPt = 842; // A4 dọc = 842pt (nếu không lấy từ file thì fix tạm)
        $cmToPt = function ($cm) {
            return ($cm / 2.54) * 72;
        };
        $availableSpace = $pageHeightPt - $y_end - $cmToPt(2);
        return $availableSpace;
    }

    public function getImgHeight($contentImagePath, $dpi)
    {

        list($imgWidthPx, $imgHeightPx) = getimagesize($contentImagePath);
        $imgHeightPt = ($imgHeightPx / $dpi) * 72;
        return $imgHeightPt;
    }
    public function mergeContentPrevPage($dataPages, $dpi = 100)
    {
        $outputPath = storage_path('app/pdf_merged_' . time() . '.pdf');

        $baseImagePath = storage_path('app/' . $dataPages[0]['file_name_base'] . '_page_' . $dataPages[0]['target_page'] . '.png');
        $paddingPixels = (1 * $dpi) / 2.54;
        $available_space = $dataPages[0]['available_space'];
        $firstBaseImagePath = null; // Biến lưu trữ baseImagePath cũ

        foreach ($dataPages as $key => $item) {
            $contentImagePath = storage_path('app/' . $item['file_name_base'] . '_split_content' . '.png');
            if ($key != 0) {
                // Nếu đủ thì thêm vào
                if ((($item['img_height'] + 2*$paddingPixels) <= $available_space)) {
                    $this->mergeImagesVertically(
                        $baseImagePath,
                        $contentImagePath,
                        $baseImagePath
                    );
                    // Cập nhật lại available_space
                    $available_space = $available_space - ($item['img_height'] + 2*$paddingPixels);
                }else{
                    // Nếu KHÔNG đủ chỗ thì:
                    // 1. Flush base hiện tại
                    $this->flushBaseImageToPdf($baseImagePath, $firstBaseImagePath);
                    // 2. Dùng ảnh hiện tại làm base mới
                    $baseImagePath = $contentImagePath;
                    $available_space = $item['available_space'];
                }
                // TẠI CUỐI VÒNG LẶP → nếu là phần tử cuối cùng thì flush base lần cuối
                if ($key == count($dataPages) - 1) {
                    $this->flushBaseImageToPdf($baseImagePath, $firstBaseImagePath);
                }
            }
        }
        return 1;
    }
    private function flushBaseImageToPdf($baseImagePath, &$firstBaseImagePath) {
        $pdfPath = storage_path('app/' . uniqid('merged_page_') . '.pdf');
        // Dùng imagick
        // $img = new \Imagick($baseImagePath);
        // $img->setImageFormat('pdf');
        // $img->writeImage($pdfPath);
        // $img->clear();

        // Dùng fpdi
        $this->convertImageToPdfWithFpdi($baseImagePath, $pdfPath, 1);

    
        if ($firstBaseImagePath !== null) {
            $pdf = new Fpdi('P', 'mm', 'A4');

            foreach ([$firstBaseImagePath, $pdfPath] as $file) {
                $pageCount = $pdf->setSourceFile($file);
                for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                    $tpl = $pdf->importPage($pageNo);
                    $size = $pdf->getTemplateSize($tpl);
                    $pdf->AddPage($size['orientation'], 'A4');
                    $pdf->useTemplate($tpl,0 ,  0, $size['width'], $size['height']);
                }
            }
    
            $outputPath = storage_path('app/' . uniqid('merged_final_') . '.pdf');
            $pdf->Output('F', $outputPath);
            $firstBaseImagePath = $outputPath;
        } else {
            $firstBaseImagePath = $pdfPath;
        }
    }
    
}
