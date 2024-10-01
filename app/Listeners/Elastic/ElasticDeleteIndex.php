<?php

namespace App\Listeners\Elastic;

use App\Events\Elastic\DeleteIndex;
use App\Jobs\ElasticSearch\UpdateRoleIndexJob;
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
                    $this->updateDocument($event, $item);
                    $this->client->delete($params);
                }
            }else{
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
            ];
            $this->updateDocument($event, $record);
            $this->client->delete($params);
            }
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['delete_index'], $e);
        }
    }

    public function updateDocument($event, $record){
        switch ($event->modelName) {
            case 'module_role':
                // Cập nhật các index liên quan
                UpdateRoleIndexJob::dispatch($record, 'delete_module_role');
                break;
            default:
                break;
        }
    }
}
