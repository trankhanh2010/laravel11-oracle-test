<?php

namespace App\Listeners\Elastic\CashierRoom;

use App\Events\Elastic\CashierRoom\InsertCashierRoomIndex;
use App\Jobs\ElasticSearch\UpdateRoomIndexJob;
use App\Models\HIS\CashierRoom;
use App\Repositories\CashierRoomRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertCashierRoomIndex
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
    public function handle(InsertCashierRoomIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(CashierRoomRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateRoomIndexJob::dispatch($record, 'cashier_room');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
