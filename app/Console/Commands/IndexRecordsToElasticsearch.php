<?php

namespace App\Console\Commands;

use App\Events\Elastic\AccidentBodyPart\CreateAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\CreateAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\CreateAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\CreateAccidentLocationIndex;
use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use App\Events\Elastic\Area\CreateAreaIndex;
use App\Events\Elastic\AtcGroup\CreateAtcGroupIndex;
use App\Providers\ElasticsearchServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Events\Elastic\Bed\CreateBedIndex;
use App\Models\HIS\AccidentBodyPart;
use App\Models\HIS\AccidentCare;
use App\Models\HIS\AccidentHurtType;
use App\Models\HIS\AccidentLocation;
use App\Models\HIS\AgeType;
use App\Models\HIS\Area;
use App\Models\HIS\AtcGroup;
use App\Models\HIS\Bed;

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
                if (in_array($item, $table_arr)) {
                    // lấy ra tiền tố his, acs, ....
                    $first_table = strtolower(explode('_', $item)[0]);
                    $name_table = strtolower(substr($item, strlen($first_table . '_')));
                    $this->processAndIndexData($item, $first_table, $name_table);

                    $this->info('Đã tạo Index cho bảng ' . $item . '.');
                } else {
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
            case 'his_accident_body_part':
                $results = AccidentBodyPart::get_data_from_db_to_elastic(null);
                event(new CreateAccidentBodyPartIndex($name_table));
                break;
            case 'his_accident_care':
                $results = AccidentCare::get_data_from_db_to_elastic(null);
                event(new CreateAccidentCareIndex($name_table));
                break;    
            case 'his_accident_hurt_type':
                $results = AccidentHurtType::get_data_from_db_to_elastic(null);
                event(new CreateAccidentHurtTypeIndex($name_table));
                break;    
            case 'his_accident_location':
                $results = AccidentLocation::get_data_from_db_to_elastic(null);
                event(new CreateAccidentLocationIndex($name_table));
                break;     
            case 'his_age_type':
                $results = AgeType::get_data_from_db_to_elastic(null);
                event(new CreateAgeTypeIndex($name_table));
                break;     
            case 'his_area':
                $results = Area::get_data_from_db_to_elastic(null);
                event(new CreateAreaIndex($name_table));
                break;        
            case 'his_atc_group':
                $results = AtcGroup::get_data_from_db_to_elastic(null);
                event(new CreateAtcGroupIndex($name_table));
                break;  
            case 'his_bed':
                $results = Bed::get_data_from_db_to_elastic(null);
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
