<?php

namespace App\Listeners\Elastic\CareerTitle;

use App\Events\Elastic\CareerTitle\InsertCareerTitleIndex;
use App\Jobs\ElasticSearch\UpdateEmployeeIndexJob;
use App\Models\HIS\CareerTitle;
use App\Repositories\CareerTitleRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertCareerTitleIndex
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
    public function handle(InsertCareerTitleIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(CareerTitleRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateEmployeeIndexJob::dispatch($record, 'career_title');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
