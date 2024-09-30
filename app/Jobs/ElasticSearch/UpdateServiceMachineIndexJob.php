<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\ServiceMachine\InsertServiceMachineIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateServiceMachineIndexJob implements ShouldQueue
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
            case 'service':
                $params = [
                    'index' => 'service_machine',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'service_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceMachineIndex($item, 'service_machine'));
                }
                break;
            case 'machine':
                $params = [
                    'index' => 'service_machine',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'machine_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceMachineIndex($item, 'service_machine'));
                }
                break;
            default:
                break;
        }
        event(new DeleteCache('service_machine'));
    }
}
