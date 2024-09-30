<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Room\InsertRoomIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateRoomIndexJob implements ShouldQueue
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
            case 'station':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'data_store':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'medi_stock':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'sample_room':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'refectory':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'reception_room':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'execute_room':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'cashier_room':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            case 'bed_room':
                $params = [
                    'index' => 'room',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertRoomIndex($item, 'room'));
                }
                break;
            default:
                break;
        }
        event(new DeleteCache('room'));
    }
}
