<?php

namespace App\Listeners\Elastic\UnlimitReason;

use App\Events\Elastic\UnlimitReason\InsertUnlimitReasonIndex;
use App\Models\HIS\UnlimitReason;
use App\Repositories\UnlimitReasonRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertUnlimitReasonIndex
{
    /**
     * Create the event listener.
     */
    protected $client;
    public function __construct()
    {
        $this->client = app('Elasticsearch');
    }

    /**
     * Handle the event.
     */
    public function handle(InsertUnlimitReasonIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(UnlimitReasonRepository::class)->getDataFromDbToElastic($record->id);
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