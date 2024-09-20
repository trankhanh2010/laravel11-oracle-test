<?php

namespace App\Listeners\Elastic\MediStock;

use App\Events\Elastic\MediStock\InsertMediStockIndex;
use App\Models\HIS\MediStock;
use App\Repositories\MediStockRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertMediStockIndex
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
    public function handle(InsertMediStockIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(MediStockRepository::class)->getDataFromDbToElastic($record->id);
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
