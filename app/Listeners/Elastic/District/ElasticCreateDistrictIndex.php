<?php

namespace App\Listeners\Elastic\District;

use App\Events\Elastic\District\CreateDistrictIndex;

class ElasticCreateDistrictIndex
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
    public function handle(CreateDistrictIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            // Kiểm tra xem có tồn tại Index chưa
            $exists = $this->client->indices()->exists(['index' => $event->modelName])->asBool();
            if (!$exists) {
                // Tạo chỉ mục
                $this->client->indices()->create($event->params);
            }
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['create_index'], $e);
        }
    }
}
