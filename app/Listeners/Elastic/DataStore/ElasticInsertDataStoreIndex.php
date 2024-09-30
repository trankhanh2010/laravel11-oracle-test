<?php

namespace App\Listeners\Elastic\DataStore;

use App\Events\Elastic\DataStore\InsertDataStoreIndex;
use App\Jobs\ElasticSearch\UpdateDataStoreIndexJob;
use App\Jobs\ElasticSearch\UpdateLocationStoreIndexJob;
use App\Jobs\ElasticSearch\UpdateRoomIndexJob;
use App\Models\HIS\DataStore;
use App\Repositories\DataStoreRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertDataStoreIndex
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
    public function handle(InsertDataStoreIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(DataStoreRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateDataStoreIndexJob::dispatch($record, 'parent');
            UpdateLocationStoreIndexJob::dispatch($record, 'data_store');
            UpdateRoomIndexJob::dispatch($record, 'data_store');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
