<?php

namespace App\Listeners\Elastic\ServiceType;

use App\Events\Elastic\ServiceType\InsertServiceTypeIndex;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Jobs\ElasticSearch\UpdateTestIndexIndexJob;
use App\Models\HIS\ServiceType;
use App\Repositories\ServiceTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertServiceTypeIndex
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
    public function handle(InsertServiceTypeIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(ServiceTypeRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateServiceIndexJob::dispatch($record, 'service_type');
            UpdateTestIndexIndexJob::dispatch($record, 'test_service_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}