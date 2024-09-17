<?php

namespace App\Listeners\Elastic;

use App\Events\Elastic\DeleteIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Request;
use Illuminate\Queue\InteractsWithQueue;

class ElasticDeleteIndex
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
    public function handle(DeleteIndex $event): void
    {
        try {
            $record = $event->record;
            if(is_array($record)){
                foreach($record as $key => $item){
                    $params = [
                        'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                        'id'    => $item, // ID của bản ghi
                    ];
                    $this->client->delete($params);
                }
            }else{
                // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
            ];
            $this->client->delete($params);
            }
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['delete_index'], $e);
        }
    }
}
