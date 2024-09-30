<?php

namespace App\Listeners\Elastic\PatientType;

use App\Events\Elastic\PatientType\InsertPatientTypeIndex;
use App\Jobs\ElasticSearch\UpdateDepartmentIndexJob;
use App\Jobs\ElasticSearch\UpdateMedicinePatyIndexJob;
use App\Jobs\ElasticSearch\UpdateMestPatientTypeIndexJob;
use App\Models\HIS\PatientType;
use App\Repositories\PatientTypeRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertPatientTypeIndex
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
    public function handle(InsertPatientTypeIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(PatientTypeRepository::class)->getDataFromDbToElastic($record->id);
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
            UpdateDepartmentIndexJob::dispatch($record, 'default_instr_patient_type');
            UpdateMedicinePatyIndexJob::dispatch($record, 'patient_type');
            UpdateMestPatientTypeIndexJob::dispatch($record, 'patient_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
