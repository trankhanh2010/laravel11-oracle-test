<?php

namespace App\Listeners\Elastic\Service;

use App\Events\Elastic\Service\InsertServiceIndex;
use App\Jobs\ElasticSearch\UpdateBedBstyIndexJob;
use App\Jobs\ElasticSearch\UpdatePtttGroupIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceFollowIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceMachineIndexJob;
use App\Jobs\ElasticSearch\UpdateServicePatyIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceRoomIndexJob;
use App\Jobs\ElasticSearch\UpdateServSegrIndexJob;
use App\Jobs\ElasticSearch\UpdateTestIndexIndexJob;
use App\Repositories\ServiceRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertServiceIndex
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
    public function handle(InsertServiceIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(ServiceRepository::class)->getDataFromDbToElastic($record->id);
            // Decode
            $data = convertKeysToSnakeCase(json_decode($data, true));
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateBedBstyIndexJob::dispatch($record, 'service');
            UpdatePtttGroupIndexJob::dispatch($record, 'bed_services');
            UpdateServiceFollowIndexJob::dispatch($record, 'service');
            UpdateServiceFollowIndexJob::dispatch($record, 'service_follow');
            UpdateServiceMachineIndexJob::dispatch($record, 'service');
            UpdateServicePatyIndexJob::dispatch($record, 'service');
            UpdateServiceIndexJob::dispatch($record, 'parent');
            UpdateServiceRoomIndexJob::dispatch($record, 'service');
            UpdateServSegrIndexJob::dispatch($record, 'service');
            UpdateTestIndexIndexJob::dispatch($record, 'service');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
