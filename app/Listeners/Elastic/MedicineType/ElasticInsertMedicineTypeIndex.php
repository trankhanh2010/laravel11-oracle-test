<?php

namespace App\Listeners\Elastic\MedicineType;

use App\Events\Elastic\MedicineType\InsertMedicineTypeIndex;
use App\Jobs\ElasticSearch\UpdateMedicineTypeAcinIndexJob;
use App\Jobs\ElasticSearch\UpdateMediStockMetyIndexJob;
use App\Models\HIS\MedicineType;
use App\Repositories\MedicineTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertMedicineTypeIndex
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
    public function handle(InsertMedicineTypeIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(MedicineTypeRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateMedicineTypeAcinIndexJob::dispatch($record, 'medicine_type');
            UpdateMediStockMetyIndexJob::dispatch($record, 'medicine_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
