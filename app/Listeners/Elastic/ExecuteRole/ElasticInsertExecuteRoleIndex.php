<?php

namespace App\Listeners\Elastic\ExecuteRole;

use App\Events\Elastic\ExecuteRole\InsertExecuteRoleIndex;
use App\Jobs\ElasticSearch\UpdateExecuteRoleUserIndexJob;
use App\Models\HIS\ExecuteRole;
use App\Repositories\ExecuteRoleRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertExecuteRoleIndex
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
    public function handle(InsertExecuteRoleIndex $event): void
    {
        try {
            $record = $event->record;
            $data = app(ExecuteRoleRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateExecuteRoleUserIndexJob::dispatch($record, 'execute_role');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
