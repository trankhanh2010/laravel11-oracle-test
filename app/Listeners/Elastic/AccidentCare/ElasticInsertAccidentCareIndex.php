<?php

namespace App\Listeners\Elastic\AccidentCare;

use App\Events\Elastic\AccidentCare\InsertAccidentCareIndex;
use App\Models\HIS\AccidentCare;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertAccidentCareIndex
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
    public function handle(InsertAccidentCareIndex $event): void
    {
        $record = $event->record;
        $data = AccidentCare::get_data_from_db_to_elastic($record->id);
        // Tạo chỉ mục hoặc cập nhật dữ liệu
        $params = [
            'index' => $event->model_name, // Chỉ mục bạn muốn tạo hoặc cập nhật
            'id'    => $record->id, // ID của bản ghi
            'body'  => $data, 
        ];

        $this->client->index($params);
    }
}