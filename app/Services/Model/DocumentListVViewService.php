<?php

namespace App\Services\Model;

use App\DTOs\DocumentListVViewDTO;
use App\Events\Cache\DeleteCache;
use App\Events\Elastic\DocumentListVView\InsertDocumentListVViewIndex;
use App\Events\Elastic\DeleteIndex;
use Illuminate\Support\Facades\Cache;
use setasign\Fpdi\Fpdi;
use Illuminate\Support\Facades\Http;
use App\Repositories\DocumentListVViewRepository;
use Illuminate\Support\Facades\Log;

class DocumentListVViewService
{
    protected $documentListVViewRepository;
    protected $params;
    public function __construct(DocumentListVViewRepository $documentListVViewRepository)
    {
        $this->documentListVViewRepository = $documentListVViewRepository;
    }
    public function withParams(DocumentListVViewDTO $params)
    {
        $this->params = $params;
        return $this;
    }
    public function handleDataBaseSearch()
    {
        try {
            $data = $this->documentListVViewRepository->applyJoins();
            $data = $this->documentListVViewRepository->applyWithParam($data);
            $data = $this->documentListVViewRepository->applyKeywordFilter($data, $this->params->keyword);
            $data = $this->documentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
            $data = $this->documentListVViewRepository->applyIsDeleteFilter($data, 0);
            // $data = $this->documentListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
            $data = $this->documentListVViewRepository->applyDocumentTypeIdFilter($data, $this->params->documentTypeId);
            $data = $this->documentListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
            $count = $data->count();
            $data = $this->documentListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
            $data = $this->documentListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
            return ['data' => $data, 'count' => $count];
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
        }
    }
    private function mergeDocumentByIds()
    {
        $data = $this->documentListVViewRepository->applyJoins();
        $data = $this->documentListVViewRepository->applyWithParam($data);
        $data = $this->documentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->documentListVViewRepository->applyIsDeleteFilter($data, 0);
        // $data = $this->documentListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->documentListVViewRepository->applyDocumentTypeIdFilter($data, $this->params->documentTypeId);
        $data = $this->documentListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        $data = $this->documentListVViewRepository->applyDocumentIdsFilter($data, $this->params->documentIds);
        $count = null;
        $data = $this->documentListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);

        $urls = $data->pluck('last_version_url')->map(function ($path) {
            return config('database')['connections']['fss']['fss_url'] . $path;
        })->toArray();

        $outputFile = storage_path('app/merged_' . time() . '.pdf');
        $this->mergePdfFromUrls($urls, $outputFile);
    
        // Đọc file và mã hóa base64
        $base64 = base64_encode(file_get_contents($outputFile));
    
        // Xoá file sau khi đọc xong (tùy chọn)
        unlink($outputFile);
    
        return [
            'data' => [
                'base64' => $base64,
            ],
            'count' => $count,
        ];
    }
    private function getAllDataFromDatabase()
    {
        $data = $this->documentListVViewRepository->applyJoins();
        $data = $this->documentListVViewRepository->applyWithParam($data);
        $data = $this->documentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->documentListVViewRepository->applyIsDeleteFilter($data, 0);
        // $data = $this->documentListVViewRepository->applyTreatmentIdFilter($data, $this->params->treatmentId);
        $data = $this->documentListVViewRepository->applyDocumentTypeIdFilter($data, $this->params->documentTypeId);
        $data = $this->documentListVViewRepository->applyTreatmentCodeFilter($data, $this->params->treatmentCode);
        $count = $data->count();
        $data = $this->documentListVViewRepository->applyOrdering($data, $this->params->orderBy, $this->params->orderByJoin);
        $data = $this->documentListVViewRepository->fetchData($data, $this->params->getAll, $this->params->start, $this->params->limit);
        return ['data' => $data, 'count' => $count];
    }
    private function getDataById($id)
    {
        $data = $this->documentListVViewRepository->applyJoins()
            ->where('id', $id);
        $data = $this->documentListVViewRepository->applyIsActiveFilter($data, $this->params->isActive);
        $data = $this->documentListVViewRepository->applyIsDeleteFilter($data, 0);
        $data = $data->first();
        return $data;
    }
    public function handleMergeDocumentByIds()
    {
        try {
            return $this->mergeDocumentByIds();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetAll()
    {
        try {
            return $this->getAllDataFromDatabase();
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
        }
    }
    public function handleDataBaseGetWithId($id)
    {
        try {
            return $this->getDataById($id);
        } catch (\Throwable $e) {
            return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
        }
    }

    private function mergePdfFromUrls(array $urls, string $outputPath)
    {
        $pdf = new Fpdi();

        foreach ($urls as $url) {
            // Thay thế \ thành /
            $normalizedUrl = str_replace('\\', '/', $url);

            // Cố gắng tải nội dung file PDF từ URL
            $pdfContent = @file_get_contents($normalizedUrl);

            if (!$pdfContent) continue; // Nếu không lấy được thì bỏ qua
        
            $tempPath = storage_path('app/temp_' . uniqid() . '.pdf');
            file_put_contents($tempPath, $pdfContent);
        
            $pageCount = $pdf->setSourceFile($tempPath);
            for ($pageNo = 1; $pageNo <= $pageCount; $pageNo++) {
                $tplIdx = $pdf->importPage($pageNo);
                $pdf->AddPage();
                $pdf->useTemplate($tplIdx);
            }
        
            unlink($tempPath); // Xóa file tạm
        }

        $pdf->Output('F', $outputPath);

        return $outputPath;
    }
    // public function createDocumentListVView($request)
    // {
    //     try {
    //         $data = $this->documentListVViewRepository->create($request, $this->params->time, $this->params->appCreator, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->documentListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDocumentListVViewIndex($data, $this->params->documentListVViewName));
    //         return returnDataCreateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
    //     }
    // }

    // public function updateDocumentListVView($id, $request)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->documentListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->documentListVViewRepository->update($request, $data, $this->params->time, $this->params->appModifier);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->documentListVViewName));
    //         // Gọi event để thêm index vào elastic
    //         event(new InsertDocumentListVViewIndex($data, $this->params->documentListVViewName));
    //         return returnDataUpdateSuccess($data);
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
    //     }
    // }

    // public function deleteDocumentListVView($id)
    // {
    //     if (!is_numeric($id)) {
    //         return returnIdError($id);
    //     }
    //     $data = $this->documentListVViewRepository->getById($id);
    //     if ($data == null) {
    //         return returnNotRecord($id);
    //     }
    //     try {
    //         $data = $this->documentListVViewRepository->delete($data);
    //         // Gọi event để xóa cache
    //         event(new DeleteCache($this->params->documentListVViewName));
    //         // Gọi event để xóa index trong elastic
    //         event(new DeleteIndex($data, $this->params->documentListVViewName));
    //         return returnDataDeleteSuccess();
    //     } catch (\Throwable $e) {
    //         return writeAndThrowError(config('params')['db_service']['error']['document_list_v_view'], $e);
    //     }
    // }
}
