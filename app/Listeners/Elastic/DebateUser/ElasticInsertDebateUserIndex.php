<?php

namespace App\Listeners\Elastic\DebateUser;

use App\Events\Elastic\DebateUser\InsertDebateUserIndex;
use App\Models\HIS\DebateUser;
use App\Repositories\DebateUserRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertDebateUserIndex
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
    public function handle(InsertDebateUserIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(DebateUserRepository::class)->getDataFromDbToElastic(null, $record->id);
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
