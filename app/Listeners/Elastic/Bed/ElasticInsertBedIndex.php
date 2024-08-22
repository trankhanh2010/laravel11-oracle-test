<?php

namespace App\Listeners\Elastic\Bed;

use App\Events\Elastic\Bed\InsertBedIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertBedIndex
{
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
    public function handle(InsertBedIndex $event): void
    {
        $record = $event->record;

        // Tạo chỉ mục hoặc cập nhật dữ liệu
        $params = [
            'index' => $event->model_name, // Chỉ mục bạn muốn tạo hoặc cập nhật
            'id'    => $record->id, // ID của bản ghi
            'body'  => $record->toArray() // Chuyển bản ghi thành mảng
        ];

        $this->client->index($params);
    }
}
