<?php

namespace App\Listeners\Elastic\ReceptionRoom;

use App\Events\Elastic\ReceptionRoom\InsertReceptionRoomIndex;
use App\Models\HIS\ReceptionRoom;
use App\Repositories\ReceptionRoomRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertReceptionRoomIndex
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
    public function handle(InsertReceptionRoomIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(ReceptionRoomRepository::class)->getDataFromDbToElastic($record->id);
            // Decode
            $data = convertKeysToSnakeCase(json_decode($data, true));
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