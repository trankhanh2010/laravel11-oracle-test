<?php

namespace App\Listeners\Elastic;

use App\Events\Elastic\DeleteIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticDeleteIndex
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
    public function handle(DeleteIndex $event): void
    {
        $record = $event->record;

        // Tạo chỉ mục hoặc cập nhật dữ liệu
        $params = [
            'index' => $event->model_name, // Chỉ mục bạn muốn tạo hoặc cập nhật
            'id'    => $record->id, // ID của bản ghi
        ];

        $this->client->delete($params);
    }
}
