<?php

namespace App\Listeners\Elastic\BedRoom;

use App\Events\Elastic\BedRoom\InsertBedRoomIndex;
use App\Jobs\ElasticSearch\UpdateBedIndexJob;
use App\Jobs\ElasticSearch\UpdateRoomIndexJob;
use App\Models\HIS\BedRoom;
use App\Repositories\BedRoomRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertBedRoomIndex
{
    /**
     * Create the event listener.
     */
    protected $client;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->client = app('Elasticsearch');
    }

    /**
     * Handle the event.
     */
    public function handle(InsertBedRoomIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(BedRoomRepository::class)->getDataFromDbToElastic(null, $record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateBedIndexJob::dispatch($record, 'bed_room');
            UpdateRoomIndexJob::dispatch($record, 'bed_room');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
