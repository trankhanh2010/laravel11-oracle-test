<?php

namespace App\Listeners\Elastic\AtcGroup;

use App\Events\Elastic\AtcGroup\CreateAtcGroupIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticCreateAtcGroupIndex
{
    /**
     * Create the event listener.
     */
    public $client;
    public function __construct()
    {
        $this->client = app('Elasticsearch');
    }

    /**
     * Handle the event.
     */
    public function handle(CreateAtcGroupIndex $event): void
    {
        // Kiểm tra xem có tồn tại Index chưa
        $exists = $this->client->indices()->exists(['index' => $event->model_name])->asBool();
        if(!$exists){
            // Tạo chỉ mục
            $this->client->indices()->create($event->params);
        }
    }
}
