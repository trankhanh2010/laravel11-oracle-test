<?php

namespace App\Listeners\Elastic\BhytBlacklist;

use App\Events\Elastic\BhytBlacklist\InsertBhytBlacklistIndex;
use App\Models\HIS\BhytBlacklist;
use App\Repositories\BhytBlacklistRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertBhytBlacklistIndex
{
    /**
     * Create the event listener.
     */
    protected $client;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->client = app('Elasticsearch');
    }

    /**
     * Handle the event.
     */
    public function handle(InsertBhytBlacklistIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(BhytBlacklistRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];

            $this->client->index($params);
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
