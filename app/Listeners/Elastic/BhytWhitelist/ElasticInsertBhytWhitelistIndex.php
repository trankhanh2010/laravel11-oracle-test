<?php

namespace App\Listeners\Elastic\BhytWhitelist;

use App\Events\Elastic\BhytWhitelist\InsertBhytWhitelistIndex;
use App\Models\HIS\BHYTWhitelist;
use App\Repositories\BhytWhitelistRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertBhytWhitelistIndex
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
    public function handle(InsertBhytWhitelistIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(BhytWhitelistRepository::class)->getDataFromDbToElastic(null, $record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];

            $this->client->index($params);
            $this->client->indices()->refresh([
                'index' => $event->modelName, // Chỉ mục cần refresh
            ]); // Gọi lệnh refresh
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
