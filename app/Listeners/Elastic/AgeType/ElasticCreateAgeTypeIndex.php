<?php

namespace App\Listeners\Elastic\AgeType;

use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticCreateAgeTypeIndex
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
    public function handle(CreateAgeTypeIndex $event): void
    {
        // Kiểm tra xem có tồn tại Index chưa
        $exists = $this->client->indices()->exists(['index' => $event->model_name])->asBool();
        if(!$exists){
            // Tạo chỉ mục
            $this->client->indices()->create($event->params);
        }
    }
}
