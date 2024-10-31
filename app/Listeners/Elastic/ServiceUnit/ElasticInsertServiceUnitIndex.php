<?php

namespace App\Listeners\Elastic\ServiceUnit;

use App\Events\Elastic\ServiceUnit\InsertServiceUnitIndex;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceUnitIndexJob;
use App\Models\HIS\ServiceUnit;
use App\Repositories\ServiceUnitRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertServiceUnitIndex
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
    public function handle(InsertServiceUnitIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(ServiceUnitRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateServiceIndexJob::dispatch($record, 'service_unit');
            UpdateServiceUnitIndexJob::dispatch($record, 'convert');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
