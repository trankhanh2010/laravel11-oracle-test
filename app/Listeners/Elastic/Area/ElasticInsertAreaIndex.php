<?php

namespace App\Listeners\Elastic\Area;

use App\Events\Elastic\Area\InsertAreaIndex;
use App\Models\HIS\Area;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertAreaIndex
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
    public function handle(InsertAreaIndex $event): void
    {
        try {
            $record = $event->record;
            $data = Area::getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->model_name, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record->id, // ID của bản ghi
                'body'  => $data,
            ];

            $this->client->index($params);
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
