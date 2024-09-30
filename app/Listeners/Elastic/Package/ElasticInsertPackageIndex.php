<?php

namespace App\Listeners\Elastic\Package;

use App\Events\Elastic\Package\InsertPackageIndex;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Jobs\ElasticSearch\UpdateServicePatyIndexJob;
use App\Models\HIS\Package;
use App\Repositories\PackageRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertPackageIndex
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
    public function handle(InsertPackageIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(PackageRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateServicePatyIndexJob::dispatch($record, 'package');
            UpdateServiceIndexJob::dispatch($record, 'package');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
