<?php

namespace App\Listeners\Elastic\SaleProfitCfg;

use App\Events\Elastic\SaleProfitCfg\InsertSaleProfitCfgIndex;
use App\Models\HIS\SaleProfitCfg;
use App\Repositories\SaleProfitCfgRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertSaleProfitCfgIndex
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
    public function handle(InsertSaleProfitCfgIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(SaleProfitCfgRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];

            $this->client->index($params);
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}