<?php

namespace App\Listeners\Elastic\ProcessingMethod;

use App\Events\Elastic\ProcessingMethod\InsertProcessingMethodIndex;
use App\Models\HIS\ProcessingMethod;
use App\Repositories\ProcessingMethodRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertProcessingMethodIndex
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
    public function handle(InsertProcessingMethodIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(ProcessingMethodRepository::class)->getDataFromDbToElastic($record->id);
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