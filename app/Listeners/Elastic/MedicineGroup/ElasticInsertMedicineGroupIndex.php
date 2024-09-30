<?php

namespace App\Listeners\Elastic\MedicineGroup;

use App\Events\Elastic\MedicineGroup\InsertMedicineGroupIndex;
use App\Models\HIS\MedicineGroup;
use App\Repositories\MedicineGroupRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertMedicineGroupIndex
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
    public function handle(InsertMedicineGroupIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(MedicineGroupRepository::class)->getDataFromDbToElastic($record->id);
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
