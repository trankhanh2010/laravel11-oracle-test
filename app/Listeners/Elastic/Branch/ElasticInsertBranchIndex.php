<?php

namespace App\Listeners\Elastic\Branch;

use App\Events\Elastic\Branch\InsertBranchIndex;
use App\Jobs\ElasticSearch\UpdateDepartmentIndexJob;
use App\Jobs\ElasticSearch\UpdateServicePatyIndexJob;
use App\Models\HIS\Branch;
use App\Repositories\BranchRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertBranchIndex
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
    public function handle(InsertBranchIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(BranchRepository::class)->getDataFromDbToElastic(null, $record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            // UpdateDepartmentIndexJob::dispatch($record, 'branch');
            // UpdateServicePatyIndexJob::dispatch($record, 'branch');
            $this->client->indices()->refresh([
                'index' => $event->modelName, // Chỉ mục cần refresh
            ]); // Gọi lệnh refresh
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
