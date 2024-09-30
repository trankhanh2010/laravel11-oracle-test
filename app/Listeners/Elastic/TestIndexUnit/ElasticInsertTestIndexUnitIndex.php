<?php

namespace App\Listeners\Elastic\TestIndexUnit;

use App\Events\Elastic\TestIndexUnit\InsertTestIndexUnitIndex;
use App\Models\HIS\TestIndexUnit;
use App\Repositories\TestIndexUnitRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertTestIndexUnitIndex
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
    public function handle(InsertTestIndexUnitIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(TestIndexUnitRepository::class)->getDataFromDbToElastic($record->id);
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
