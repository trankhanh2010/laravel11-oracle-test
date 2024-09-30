<?php

namespace App\Listeners\Elastic\TestType;

use App\Events\Elastic\TestType\InsertTestTypeIndex;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Models\HIS\TestType;
use App\Repositories\TestTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertTestTypeIndex
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
    public function handle(InsertTestTypeIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(TestTypeRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateServiceIndexJob::dispatch($record, 'test_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
