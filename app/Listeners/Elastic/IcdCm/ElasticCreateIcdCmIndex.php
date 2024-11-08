<?php

namespace App\Listeners\Elastic\IcdCm;

use App\Events\Elastic\IcdCm\CreateIcdCmIndex;

class ElasticCreateIcdCmIndex
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
    public function handle(CreateIcdCmIndex $event): void
    {
        try {
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
