<?php

namespace App\Listeners\Elastic\AccidentBodyPart;

use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Models\HIS\AccidentBodyPart;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class ElasticInsertAccidentBodyPartIndex
{
    /**
     * Create the event listener.
     */
    protected $client;
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        $this->client = app('Elasticsearch');
    }

    /**
     * Handle the event.
     */
    public function handle(InsertAccidentBodyPartIndex $event): void
    {
        $record = $event->record;
        $data = AccidentBodyPart::get_data_from_db_to_elastic($record->id);
        // Tạo chỉ mục hoặc cập nhật dữ liệu
        $params = [
            'index' => $event->model_name, // Chỉ mục bạn muốn tạo hoặc cập nhật
            'id'    => $record->id, // ID của bản ghi
            'body'  => $data, 
        ];

        $this->client->index($params);
    }
}
