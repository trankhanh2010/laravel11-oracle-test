<?php

namespace App\Listeners\Elastic\Bed;

use App\Events\Elastic\Bed\InsertBedIndex;
use App\Models\HIS\Bed;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertBedIndex
{
    protected $client;
    protected $request;
    /**
     * Create the event listener.
     */
    public function __construct(Request $request)
    {
        $this->client = app('Elasticsearch');
        $this->request = $request;
    }

    /**
     * Handle the event.
     */
    public function handle(InsertBedIndex $event): void
    {
        try {
            $record = $event->record;
            $data = Bed::getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record->id, // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
