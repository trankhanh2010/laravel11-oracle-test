<?php

namespace App\Listeners\Elastic\Department;

use App\Events\Elastic\Department\InsertDepartmentIndex;
use App\Jobs\ElasticSearch\UpdateEmployeeIndexJob;
use App\Jobs\ElasticSearch\UpdateMachineIndexJob;
use App\Jobs\ElasticSearch\UpdateServiceIndexJob;
use App\Models\HIS\Department;
use App\Repositories\DepartmentRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertDepartmentIndex
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
    public function handle(InsertDepartmentIndex $event): void
    {
        try {
            if(!$this->client->indices()->exists(['index' => $event->modelName])->asBool()){
                return ;
            }
            $record = $event->record;
            $data = app(DepartmentRepository::class)->getDataFromDbToElastic(null, $record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];
            $this->client->index($params);
            // Cập nhật các index liên quan
            UpdateEmployeeIndexJob::dispatch($record, 'department');
            UpdateMachineIndexJob::dispatch($record, 'department');
            UpdateServiceIndexJob::dispatch($record, 'revenue_department');
            $this->client->indices()->refresh([
                'index' => $event->modelName, // Chỉ mục cần refresh
            ]); // Gọi lệnh refresh
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
