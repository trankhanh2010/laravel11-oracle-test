<?php

namespace App\Listeners\Elastic\PatientType;

use App\Events\Elastic\PatientType\InsertPatientTypeIndex;
use App\Jobs\ElasticSearch\UpdateDepartmentIndexJob;
use App\Jobs\ElasticSearch\UpdateMedicinePatyIndexJob;
use App\Jobs\ElasticSearch\UpdateMestPatientTypeIndexJob;
use App\Jobs\ElasticSearch\UpdatePatientClassifyIndexJob;
use App\Jobs\ElasticSearch\UpdatePatientTypeAllowIndexJob;
use App\Jobs\ElasticSearch\UpdatePatientTypeRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Jobs\ElasticSearch\UpdateServicePatyIndexJob;
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
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(PatientTypeRepository::class)->getDataFromDbToElastic(null, $record->id);
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
            UpdatePatientClassifyIndexJob::dispatch($record, 'patient_type');
            UpdatePatientTypeAllowIndexJob::dispatch($record, 'patient_type');
            UpdatePatientTypeRoomIndexJob::dispatch($record, 'patient_type');
            UpdateServicePatyIndexJob::dispatch($record, 'patient_type');
            UpdateServiceIndexJob::dispatch($record, 'default_patient_type');
            UpdateServiceIndexJob::dispatch($record, 'bill_patient_type');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
