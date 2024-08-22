<?php

namespace App\Listeners\Elastic\Bed;

use App\Events\Elastic\Bed\CreateBedIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticCreateBedIndex
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
    public function handle(CreateBedIndex $event): void
    {
        // Kiểm tra xem có tồn tại Index chưa
        $exists = $this->client->indices()->exists(['index' => $event->model_name])->asBool();
        if(!$exists){
            // Tạo chỉ mục
            $this->client->indices()->create($event->params);
        }
    }
}
