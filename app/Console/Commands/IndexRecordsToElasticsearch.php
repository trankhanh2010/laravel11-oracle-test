<?php

namespace App\Console\Commands;

use App\Providers\ElasticsearchServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Events\Elastic\Bed\CreateBedIndex;
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
        $this->info(env('ELASTICSEARCH_HOST') . ':' . env('ELASTICSEARCH_PORT'));

        $param = $this->option('table');
        $table = [];
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
                    $this->processAndIndexData($item, $first_table, $name_table);
                    
                    $this->info('Đã tạo Index cho bảng ' . $item . '.');
                }else{
                    $this->error('Không tồn tại bảng ' . $item . '.');
                }
            }
        }
    }

    protected function processAndIndexData($table, $first_table, $name_table)
    {
       // Khởi tạo kết nối đến Elastic
       $client = app('Elasticsearch');
       switch ($table) {
           case 'his_bed':
               $results = DB::connection('oracle_' . $first_table)
                   ->table($table)
                   ->leftJoin('his_bed_type', 'his_bed.bed_type_id', '=', 'his_bed_type.id')
                   ->leftJoin('his_bed_room', 'his_bed.bed_room_id', '=', 'his_bed_room.id')
                   ->leftJoin('his_room', 'his_bed_room.room_id', '=', 'his_room.id')
                   ->leftJoin('his_department', 'his_room.department_id', '=', 'his_department.id')

                   ->select(
                       'his_bed.*',
                       'his_bed_type.bed_type_name',
                       'his_bed_type.bed_type_code',
                       'his_bed_room.bed_room_name',
                       'his_bed_room.bed_room_code',
                       'his_department.department_name',
                       'his_department.department_code',
                   )
                   ->get();
               event(new CreateBedIndex($name_table));
               break;

           default:
               // Xử lý mặc định hoặc xử lý khi không có bảng khớp
               $results = DB::connection('oracle_' . $first_table)->table($table)->get();
               break;
       }
       foreach ($results as $result) {
           $data = [];
           foreach ($result as $key => $value) {
               $data[$key] = $value;
           }
           $params = [
               'index' => $name_table,
               'id'    => $result->id,
               'body'  => $data
           ];

           $client->index($params);
       }
    }
}
