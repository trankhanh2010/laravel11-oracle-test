<?php

namespace App\Listeners\Elastic\MedicineUseForm;

use App\Events\Elastic\MedicineUseForm\InsertMedicineUseFormIndex;
use App\Models\HIS\MedicineUseForm;
use App\Repositories\MedicineUseFormRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertMedicineUseFormIndex
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
    public function handle(InsertMedicineUseFormIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(MedicineUseFormRepository::class)->getDataFromDbToElastic($record->id);
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