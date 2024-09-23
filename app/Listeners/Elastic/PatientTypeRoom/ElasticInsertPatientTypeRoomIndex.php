<?php

namespace App\Listeners\Elastic\PatientTypeRoom;

use App\Events\Elastic\PatientTypeRoom\InsertPatientTypeRoomIndex;
use App\Models\HIS\PatientTypeRoom;
use App\Repositories\PatientTypeRoomRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertPatientTypeRoomIndex
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
    public function handle(InsertPatientTypeRoomIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(PatientTypeRoomRepository::class)->getDataFromDbToElastic($record->id);
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
