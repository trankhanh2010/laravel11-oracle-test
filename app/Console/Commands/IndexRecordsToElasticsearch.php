<?php

namespace App\Console\Commands;

use App\Events\Elastic\AccidentBodyPart\CreateAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\CreateAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\CreateAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\CreateAccidentLocationIndex;
use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use App\Events\Elastic\Area\CreateAreaIndex;
use App\Events\Elastic\AtcGroup\CreateAtcGroupIndex;
use App\Events\Elastic\Awareness\CreateAwarenessIndex;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Events\Elastic\Bed\CreateBedIndex;

use App\Repositories\AccidentBodyPartRepository;
use App\Repositories\AccidentCareRepository;
use App\Repositories\AccidentHurtTypeRepository;
use App\Repositories\AccidentLocationRepository;
use App\Repositories\AgeTypeRepository;
use App\Repositories\AreaRepository;
use App\Repositories\AtcGroupRepository;
use App\Repositories\AwarenessRepository;
use App\Repositories\BedRepository;

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
                $results = AccidentBodyPartRepository::getDataFromDbToElastic(null);
                event(new CreateAccidentBodyPartIndex($name_table));
                break;
            case 'his_accident_care':
                $results = AccidentCareRepository::getDataFromDbToElastic(null);
                event(new CreateAccidentCareIndex($name_table));
                break;    
            case 'his_accident_hurt_type':
                $results = AccidentHurtTypeRepository::getDataFromDbToElastic(null);
                event(new CreateAccidentHurtTypeIndex($name_table));
                break;    
            case 'his_accident_location':
                $results = AccidentLocationRepository::getDataFromDbToElastic(null);
                event(new CreateAccidentLocationIndex($name_table));
                break;     
            case 'his_age_type':
                $results = AgeTypeRepository::getDataFromDbToElastic(null);
                event(new CreateAgeTypeIndex($name_table));
                break;     
            case 'his_area':
                $results = AreaRepository::getDataFromDbToElastic(null);
                event(new CreateAreaIndex($name_table));
                break;        
            case 'his_atc_group':
                $results = AtcGroupRepository::getDataFromDbToElastic(null);
                event(new CreateAtcGroupIndex($name_table));
                break;  
            case 'his_awareness':
                $results = AwarenessRepository::getDataFromDbToElastic(null);
                event(new CreateAwarenessIndex($name_table));
                break;  
            case 'his_bed':
                $results = BedRepository::getDataFromDbToElastic(null);
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
