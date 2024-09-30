<?php

namespace App\Listeners\Elastic\Room;

use App\Events\Elastic\Room\InsertRoomIndex;
use App\Jobs\ElasticSearch\UpdateBedIndexJob;
use App\Jobs\ElasticSearch\UpdateBedRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateCashierRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateDataStoreIndexJob;
use App\Jobs\ElasticSearch\UpdateExecuteRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateMediStockIndexJob;
use App\Jobs\ElasticSearch\UpdateMestRoomIndexJob;
use App\Jobs\ElasticSearch\UpdatePatientTypeRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateReceptionRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateRefectoryIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceRoomIndexJob;
use App\Models\HIS\Room;
use App\Repositories\RoomRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertRoomIndex
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
    public function handle(InsertRoomIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(RoomRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];

            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateBedIndexJob::dispatch($record, 'room');
            UpdateDataStoreIndexJob::dispatch($record, 'room');
            UpdateDataStoreIndexJob::dispatch($record, 'stored_room');
            UpdateMediStockIndexJob::dispatch($record, 'room');
            UpdateMestRoomIndexJob::dispatch($record, 'room');
            UpdatePatientTypeRoomIndexJob::dispatch($record, 'room');
            UpdateBedRoomIndexJob::dispatch($record, 'room');
            UpdateCashierRoomIndexJob::dispatch($record, 'room');
            UpdateExecuteRoomIndexJob::dispatch($record, 'room');
            UpdateReceptionRoomIndexJob::dispatch($record, 'room');
            UpdateRefectoryIndexJob::dispatch($record, 'room');
            UpdateServiceRoomIndexJob::dispatch($record, 'room');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
