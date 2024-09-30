<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediStock\InsertMediStockIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateMediStockIndexJob implements ShouldQueue
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
            case 'room':
                $params = [
                    'index' => 'medi_stock',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'room_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertMediStockIndex($item, 'medi_stock'));
                }
                break;
            case 'parent':
                $params = [
                    'index' => 'medi_stock',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'parent_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertMediStockIndex($item, 'medi_stock'));
                }
                break;
                case 'imp_mest_types':
                    $params = [
                        'index' => 'medi_stock',
                        'body'  => [
                            '_source' => false,
                            'query'   => [
                                'term' => [
                                    'imp_mest_types.pivot.imp_mest_type_id' => $record->id
                                ]
                            ]
                        ]
                    ];
                    $response = $this->client->search($params);
                    $ids = array_map(function ($hit) {
                        return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                    }, $response['hits']['hits']);
                    foreach ($ids as $item) {
                        event(new InsertMediStockIndex($item, 'medi_stock'));
                    }
                    break;
                    case 'exp_mest_types':
                        $params = [
                            'index' => 'medi_stock',
                            'body'  => [
                                '_source' => false,
                                'query'   => [
                                    'term' => [
                                        'exp_mest_types.pivot.exp_mest_type_id' => $record->id
                                    ]
                                ]
                            ]
                        ];
                        $response = $this->client->search($params);
                        $ids = array_map(function ($hit) {
                            return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                        }, $response['hits']['hits']);
                        foreach ($ids as $item) {
                            event(new InsertMediStockIndex($item, 'medi_stock'));
                        }
                        break;
            default:
                break;
        }
        event(new DeleteCache('medi_stock'));
    }
}
