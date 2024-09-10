<?php

namespace App\Listeners\Elastic\AccidentBodyPart;

use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Models\HIS\AccidentBodyPart;
use App\Repositories\AccidentBodyPartRepository;
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
        try {
            $record = $event->record;
            $data = app(AccidentBodyPartRepository::class)->getDataFromDbToElastic($record->id);
            // Tạo chỉ mục hoặc cập nhật dữ liệu
            $params = [
                'index' => $event->modelName, // Chỉ mục bạn muốn tạo hoặc cập nhật
                'id'    => $record['id'], // ID của bản ghi
                'body'  => $data,
            ];

            $this->client->index($params);
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['insert_index'], $e);
        }
    }
}
