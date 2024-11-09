<?php

namespace App\Listeners\Elastic\MediStock;

use App\Events\Elastic\MediStock\InsertMediStockIndex;
use App\Jobs\ElasticSearch\UpdateMediStockIndexJob;
use App\Jobs\ElasticSearch\UpdateMediStockMatyIndexJob;
use App\Jobs\ElasticSearch\UpdateMediStockMetyIndexJob;
use App\Jobs\ElasticSearch\UpdateMestPatientTypeIndexJob;
use App\Jobs\ElasticSearch\UpdateMestRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateRoomIndexJob;
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
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(MediStockRepository::class)->getDataFromDbToElastic(null, $record->id);
            // Decode
            $data = convertKeysToSnakeCase(json_decode($data, true));
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateMediStockMatyIndexJob::dispatch($record, 'medi_stock');
            UpdateMediStockMetyIndexJob::dispatch($record, 'medi_stock');
            UpdateMediStockIndexJob::dispatch($record, 'parent');
            UpdateMestPatientTypeIndexJob::dispatch($record, 'medi_stock');
            UpdateMestRoomIndexJob::dispatch($record, 'medi_stock');
            UpdateRoomIndexJob::dispatch($record, 'medi_stock');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
