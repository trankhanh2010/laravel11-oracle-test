<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\TestIndex\InsertTestIndexIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateTestIndexIndexJob implements ShouldQueue
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
            case 'test_service_type':
                $params = [
                    'index' => 'test_index',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'test_service_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertTestIndexIndex($item, 'test_index'));
                }
                break;
            case 'material_type':
                $params = [
                    'index' => 'test_index',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'material_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertTestIndexIndex($item, 'test_index'));
                }
                break;
            case 'test_index_group':
                $params = [
                    'index' => 'test_index',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'test_index_group_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertTestIndexIndex($item, 'test_index'));
                }
                break;
            case 'test_index_unit':
                $params = [
                    'index' => 'test_index',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'test_index_unit_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertTestIndexIndex($item, 'test_index'));
                }
                break;
            case 'service':
                $params = [
                    'index' => 'test_index',
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
                    event(new InsertTestIndexIndex($item, 'test_index'));
                }
                break;
            default:
                break;
        }
        event(new DeleteCache('test_index'));
    }
}
