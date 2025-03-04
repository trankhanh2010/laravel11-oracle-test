<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Models\SAR\SarPrint;
use Illuminate\Support\Facades\Request;
use NcJoes\OfficeConverter\OfficeConverter;

class ConvertController extends Controller
{
    public function convertSarPrintToWord($id)
    {
        // Lấy nội dung và giải mã Base64
        try {
            $content = SarPrint::findOrFail($id)->content;
        } catch (\Exception $e) {
            return ;
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
        return returnDataSuccess([],[
            'fileBase64' => $base64Docx,
            // 'fileBase64Pdf' => $base64Pdf,
        ]);
    }
}


