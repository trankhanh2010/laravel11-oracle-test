<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\MediStockMety\InsertMediStockMetyIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateMediStockMetyIndexJob implements ShouldQueue
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
            case 'exp_medi_stock':
                $params = [
                    'index' => 'medi_stock_mety',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'exp_medi_stock_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertMediStockMetyIndex($item, 'medi_stock_mety'));
                }
                break;
            case 'medicine_type':
                $params = [
                    'index' => 'medi_stock_mety',
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
                    event(new InsertMediStockMetyIndex($item, 'medi_stock_mety'));
                }
                break;
            case 'medi_stock':
                $params = [
                    'index' => 'medi_stock_mety',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'medi_stock_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertMediStockMetyIndex($item, 'medi_stock_mety'));
                }
                break;
            default:
                break;
        }
        event(new DeleteCache('medi_stock_mety'));
    }
}
