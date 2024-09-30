<?php

namespace App\Listeners\Elastic\FuexType;

use App\Events\Elastic\FuexType\InsertFuexTypeIndex;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Models\HIS\FuexType;
use App\Repositories\FuexTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertFuexTypeIndex
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
    public function handle(InsertFuexTypeIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(FuexTypeRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateServiceIndexJob::dispatch($record, 'fuex_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
