<?php

namespace App\Listeners\Elastic\Bed;

use App\Events\Elastic\Bed\InsertBedIndex;
use App\Jobs\ElasticSearch\UpdateBedBstyIndexJob;
use App\Models\HIS\Bed;
use App\Repositories\BedRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertBedIndex
{
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
    public function handle(InsertBedIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(BedRepository::class)->getDataFromDbToElastic(null, $record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            // UpdateBedBstyIndexJob::dispatch($record, 'bed');
            $this->client->indices()->refresh([
                'index' => $event->modelName, // Chỉ mục cần refresh
            ]); // Gọi lệnh refresh
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
