<?php

namespace App\Listeners\Elastic\Province;

use App\Events\Elastic\Province\InsertProvinceIndex;
use App\Jobs\ElasticSearch\UpdateDistrictIndexJob;
use App\Models\HIS\Province;
use App\Repositories\ProvinceRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertProvinceIndex
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
    public function handle(InsertProvinceIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(ProvinceRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateDistrictIndexJob::dispatch($record, 'province');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
