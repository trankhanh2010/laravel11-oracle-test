<?php

namespace App\Listeners\Elastic\SereServTeinVView;

use App\Events\Elastic\SereServTeinVView\InsertSereServTeinVViewIndex;
use App\Models\HIS\SereServTeinVView;
use App\Repositories\SereServTeinVViewRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertSereServTeinVViewIndex
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
    public function handle(InsertSereServTeinVViewIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(SereServTeinVViewRepository::class)->getDataFromDbToElastic($record->id);
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