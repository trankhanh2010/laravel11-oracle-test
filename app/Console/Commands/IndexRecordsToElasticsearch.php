<?php

namespace App\Console\Commands;

use App\Providers\ElasticsearchServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class IndexRecordsToElasticsearch extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:index-records-to-elasticsearch {--table=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Chen ban ghi tu DB Oracle sang index Elastic.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table_arr = config('params')['elastic']['all_table'];
        // Kết nối đến elastic
        $client = app('Elasticsearch');
        $this->info(env('ELASTICSEARCH_HOST') . ':' . env('ELASTICSEARCH_PORT'));

        $param = $this->option('table');
        $table = null;
        if ($param != 'all' && $param != null) {
            $table = explode(',', $param);
        } else {
           $table = $table_arr;
        }

        if ($table !== null) {
            foreach ($table as $key => $item) {
                if(in_array($item, $table_arr)){
                        // lấy ra tiền tố his, acs, ....
                    $first_table = strtolower(explode('_', $item)[0]);
                    $name_table = strtolower(substr($item, strlen($first_table . '_')));
                    dispatch(new \App\Jobs\IndexTableToElasticsearch($item, $first_table, $name_table));
                    
                    $this->info('Đã dispatch job cho bảng ' . $item . '.');
                }else{
                    $this->error('Không tồn tại bảng ' . $item . '.');
                }
            }
        }
    }
}
