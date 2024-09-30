<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\Service\InsertServiceIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateServiceIndexJob implements ShouldQueue
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
            case 'default_patient_type':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'default_patient_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'film_size':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'film_size_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'other_pay_source':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'other_pay_source_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'test_type':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'test_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'fuex_type':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'fuex_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'diim_type':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'diim_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'ration_group':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'ration_group_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'gender':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'gender_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'exe_service_module':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'exe_service_module_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'package':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'package_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'revenue_department':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'revenue_department_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'icd_cm':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'icd_cm_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'pttt_method':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'pttt_method_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'pttt_group':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'pttt_group_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'bill_patient_type':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'bill_patient_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'hein_service_type':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'hein_service_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'service_unit':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'service_unit_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'parent':
                $params = [
                    'index' => 'service',
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
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;
            case 'service_type':
                $params = [
                    'index' => 'service',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'service_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertServiceIndex($item, 'service'));
                }
                break;

            default:
                break;
        }
        event(new DeleteCache('service'));
    }
}
