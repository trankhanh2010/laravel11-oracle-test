<?php

namespace App\Jobs\ElasticSearch;

use App\Events\Elastic\BedBsty\InsertBedBstyIndex;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class UpdateBedBstyIndexJob implements ShouldQueue
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
                    'index' => 'bed_bsty',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'bed_service_type_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertBedBstyIndex($item, 'bed_bsty'));
                }
                break;
            case 'bed':
                $params = [
                    'index' => 'bed_bsty',
                    'body'  => [
                        '_source' => false,
                        'query'   => [
                            'term' => [
                                'bed_id' => $record->id
                            ]
                        ]
                    ]
                ];
                $response = $this->client->search($params);
                $ids = array_map(function ($hit) {
                    return new \ArrayObject(['id' => $hit['_id']], \ArrayObject::ARRAY_AS_PROPS);
                }, $response['hits']['hits']);
                foreach ($ids as $item) {
                    event(new InsertBedBstyIndex($item, 'bed_bsty'));
                }
                break;
            default:
                break;
        }
    }
}
