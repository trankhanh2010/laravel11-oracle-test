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

class SplitContentDocument implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels, Batchable;

    public $url;
    public $index;
    public $nameCache;
    public $cacheKeySet;
    public $prevPath;
    public function __construct($url, $index, $nameCache, $cacheKeySet, $prevPath)
    {
        $this->url = $url;
        $this->index = $index;
        $this->nameCache = $nameCache;
        $this->cacheKeySet = $cacheKeySet;
        $this->prevPath = $prevPath;
    }

    public function handle()
    {
        $pdfService = app(PdfService::class);
        $dataPath = $pdfService->splitContentDocument($this->url, $this->prevPath);

        // Lưu vào cache
        cache()->put($this->nameCache, $dataPath, 60); // 1 phút
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($this->cacheKeySet, [$this->nameCache]);
    }
}
