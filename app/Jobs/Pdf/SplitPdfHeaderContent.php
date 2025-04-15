<?php

namespace App\Jobs\Pdf;

use App\Services\Pdf\PdfService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Bus\Batchable;
use Illuminate\Support\Facades\Redis;

class SplitPdfHeaderContent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $filePath;
    public $index;
    public $nameCache;
    public $cacheKeySet;
    public $prevPath;
    public function __construct($filePath, $index, $nameCache, $cacheKeySet, $prevPath)
    {
        $this->filePath = $filePath;
        $this->index = $index;
        $this->nameCache = $nameCache;
        $this->cacheKeySet = $cacheKeySet;
        $this->prevPath = $prevPath;
    }

    public function handle()
    {
        $pdfService = app(PdfService::class);
        $dataPath = $pdfService->mergeContentDocument($this->filePath, $this->prevPath);

        // Lưu vào cache
        cache()->put($this->nameCache, $dataPath, 60); // 1 phút
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySet, [$this->nameCache]);
    }
}
