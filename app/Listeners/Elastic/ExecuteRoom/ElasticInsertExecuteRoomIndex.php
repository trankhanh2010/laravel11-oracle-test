<?php

namespace App\Listeners\Elastic\ExecuteRoom;

use App\Events\Elastic\ExecuteRoom\InsertExecuteRoomIndex;
use App\Jobs\ElasticSearch\UpdateExroRoomIndexJob;
use App\Jobs\ElasticSearch\UpdatePtttTableIndexJob;
use App\Jobs\ElasticSearch\UpdateRoomIndexJob;
use App\Models\HIS\ExecuteRoom;
use App\Repositories\ExecuteRoomRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertExecuteRoomIndex
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
    public function handle(InsertExecuteRoomIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(ExecuteRoomRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateExroRoomIndexJob::dispatch($record, 'execute_room');
            UpdatePtttTableIndexJob::dispatch($record, 'pttt_table');
            UpdateRoomIndexJob::dispatch($record, 'execute_room');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
