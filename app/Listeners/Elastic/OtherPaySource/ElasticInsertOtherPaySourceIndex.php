<?php

namespace App\Listeners\Elastic\OtherPaySource;

use App\Events\Elastic\OtherPaySource\InsertOtherPaySourceIndex;
use App\Jobs\ElasticSearch\UpdatePatientClassifyIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Models\HIS\OtherPaySource;
use App\Repositories\OtherPaySourceRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertOtherPaySourceIndex
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
    public function handle(InsertOtherPaySourceIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(OtherPaySourceRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdatePatientClassifyIndexJob::dispatch($record, 'other_pay_source');
            UpdateServiceIndexJob::dispatch($record, 'other_pay_source');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
