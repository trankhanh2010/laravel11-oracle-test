<?php

namespace App\Listeners\Elastic\TreatmentType;

use App\Events\Elastic\TreatmentType\InsertTreatmentTypeIndex;
use App\Jobs\ElasticSearch\UpdateDepartmentIndexJob;
use App\Models\HIS\TreatmentType;
use App\Repositories\TreatmentTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertTreatmentTypeIndex
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
    public function handle(InsertTreatmentTypeIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(TreatmentTypeRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateDepartmentIndexJob::dispatch($record, 'req_surg_treatment_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
