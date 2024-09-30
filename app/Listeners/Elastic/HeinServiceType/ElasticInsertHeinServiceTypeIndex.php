<?php

namespace App\Listeners\Elastic\HeinServiceType;

use App\Events\Elastic\HeinServiceType\InsertHeinServiceTypeIndex;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Models\HIS\HeinServiceType;
use App\Repositories\HeinServiceTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertHeinServiceTypeIndex
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
    public function handle(InsertHeinServiceTypeIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(HeinServiceTypeRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateServiceIndexJob::dispatch($record, 'hein_service_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
