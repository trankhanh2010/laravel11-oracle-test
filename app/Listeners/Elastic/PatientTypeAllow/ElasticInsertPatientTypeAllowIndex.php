<?php

namespace App\Listeners\Elastic\PatientTypeAllow;

use App\Events\Elastic\PatientTypeAllow\InsertPatientTypeAllowIndex;
use App\Jobs\ElasticSearch\UpdatePatientTypeAllowIndexJob;
use App\Models\HIS\PatientTypeAllow;
use App\Repositories\PatientTypeAllowRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertPatientTypeAllowIndex
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
    public function handle(InsertPatientTypeAllowIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(PatientTypeAllowRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdatePatientTypeAllowIndexJob::dispatch($record, 'patient_type_allow');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
