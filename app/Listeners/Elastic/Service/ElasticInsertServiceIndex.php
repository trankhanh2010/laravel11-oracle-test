<?php

namespace App\Listeners\Elastic\Service;

use App\Events\Elastic\Service\InsertServiceIndex;
use App\Models\HIS\Service;
use App\Repositories\ServiceRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertServiceIndex
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
    public function handle(InsertServiceIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(ServiceRepository::class)->getDataFromDbToElastic($record->id);
            // Decode
            $data = convertKeysToSnakeCase(json_decode($data, true));
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
