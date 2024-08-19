<?php

namespace App\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class IndexTableToElasticsearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;
    protected $table;
    protected $first_table;
    protected $name_table;

    /**
     * Create a new job instance.
     */
    public function __construct( $table, $first_table, $name_table)
    {
        $this->table = $table;
        $this->first_table = $first_table;
        $this->name_table = $name_table;
    }
    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->client = app('Elasticsearch');

        $results = DB::connection('oracle_' . $this->first_table)->table($this->table)->get();
        foreach ($results as $result) {
            $data = [];
            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }
            $params = [
                'index' => $this->name_table,
                'id'    => $result->id,
                'body'  => $data
            ];

            $this->client->index($params);
        }
    }
}
