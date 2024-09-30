<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MedicineTypeAcin\InsertMedicineTypeAcinIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateMedicineTypeAcinIndexJob implements ShouldQueue
{
    use Queueable;
    protected $client;
    protected $record;
    protected $tableJoined;
    public function __construct($record, $tableJoined)
    {
        $this->record = $record;
        $this->tableJoined = $tableJoined;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $this->client = app('Elasticsearch');
        $record = $this->record;
        switch ($this->tableJoined) {
            case 'medicine_type':
                $params = [
                    'index' => 'medicine_type_acin',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'medicine_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertMedicineTypeAcinIndex($item, 'medicine_type_acin'));
                }
                break;
            case 'active_ingredient':
                $params = [
                    'index' => 'medicine_type_acin',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'active_ingredient_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertMedicineTypeAcinIndex($item, 'medicine_type_acin'));
                }
                break;
            default:
                break;
        }
        event(new DeleteCache('medicine_type_acin'));
    }
}
