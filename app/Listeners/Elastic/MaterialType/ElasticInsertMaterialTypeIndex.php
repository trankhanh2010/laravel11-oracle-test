<?php

namespace App\Listeners\Elastic\MaterialType;

use App\Events\Elastic\MaterialType\InsertMaterialTypeIndex;
use App\Jobs\ElasticSearch\UpdateMediStockMatyIndexJob;
use App\Jobs\ElasticSearch\UpdateTestIndexIndexJob;
use App\Models\HIS\MaterialType;
use App\Repositories\MaterialTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertMaterialTypeIndex
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
    public function handle(InsertMaterialTypeIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(MaterialTypeRepository::class)->getDataFromDbToElastic(null, $record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            // UpdateMediStockMatyIndexJob::dispatch($record, 'material_type');
            // UpdateTestIndexIndexJob::dispatch($record, 'material_type');
            $this->client->indices()->refresh([
                'index' => $event->modelName, // Chỉ mục cần refresh
            ]); // Gọi lệnh refresh
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
