<?php

namespace App\Listeners\Elastic\PatientTypeAlterVView;

use App\Events\Elastic\PatientTypeAlterVView\InsertPatientTypeAlterVViewIndex;
use App\Models\HIS\PatientTypeAlterVView;
use App\Repositories\PatientTypeAlterVViewRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertPatientTypeAlterVViewIndex
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
    public function handle(InsertPatientTypeAlterVViewIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(PatientTypeAlterVViewRepository::class)->getDataFromDbToElastic($record->id);
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