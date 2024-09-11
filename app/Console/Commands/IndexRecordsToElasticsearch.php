<?php

namespace App\Console\Commands;

use App\Events\Cache\DeleteCache;
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
use App\Events\Elastic\BedBsty\CreateBedBstyIndex;
use App\Events\Elastic\BedRoom\CreateBedRoomIndex;
use App\Events\Elastic\BedType\CreateBedTypeIndex;
use App\Events\Elastic\BhytBlacklist\CreateBhytBlacklistIndex;
use App\Events\Elastic\BhytParam\CreateBhytParamIndex;
use App\Events\Elastic\BhytWhitelist\CreateBhytWhitelistIndex;
use App\Events\Elastic\BidType\CreateBidTypeIndex;
use App\Events\Elastic\BloodGroup\CreateBloodGroupIndex;
use App\Events\Elastic\BloodVolume\CreateBloodVolumeIndex;
use App\Events\Elastic\BodyPart\CreateBodyPartIndex;
use App\Events\Elastic\BornPosition\CreateBornPositionIndex;
use App\Events\Elastic\Branch\CreateBranchIndex;
use App\Events\Elastic\CancelReason\CreateCancelReasonIndex;
use App\Events\Elastic\Career\CreateCareerIndex;
use App\Events\Elastic\CareerTitle\CreateCareerTitleIndex;
use App\Repositories\AccidentBodyPartRepository;
use App\Repositories\AccidentCareRepository;
use App\Repositories\AccidentHurtTypeRepository;
use App\Repositories\AccidentLocationRepository;
use App\Repositories\AgeTypeRepository;
use App\Repositories\AreaRepository;
use App\Repositories\AtcGroupRepository;
use App\Repositories\AwarenessRepository;
use App\Repositories\BedBstyRepository;
use App\Repositories\BedRepository;
use App\Repositories\BedRoomRepository;
use App\Repositories\BedTypeRepository;
use App\Repositories\BhytBlacklistRepository;
use App\Repositories\BhytParamRepository;
use App\Repositories\BhytWhitelistRepository;
use App\Repositories\BidTypeRepository;
use App\Repositories\BloodGroupRepository;
use App\Repositories\BloodVolumeRepository;
use App\Repositories\BodyPartRepository;
use App\Repositories\BornPositionRepository;
use App\Repositories\BranchRepository;
use App\Repositories\CancelReasonRepository;
use App\Repositories\CareerRepository;
use App\Repositories\CareerTitleRepository;

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
                    event(new DeleteCache($name_table));
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
                $results = app(AccidentBodyPartRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentBodyPartIndex($name_table));
                break;
            case 'his_accident_care':
                $results = app(AccidentCareRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentCareIndex($name_table));
                break;    
            case 'his_accident_hurt_type':
                $results = app(AccidentHurtTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentHurtTypeIndex($name_table));
                break;    
            case 'his_accident_location':
                $results = app(AccidentLocationRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentLocationIndex($name_table));
                break;     
            case 'his_age_type':
                $results = app(AgeTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAgeTypeIndex($name_table));
                break;     
            case 'his_area':
                $results = app(AreaRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAreaIndex($name_table));
                break;        
            case 'his_atc_group':
                $results = app(AtcGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAtcGroupIndex($name_table));
                break;  
            case 'his_awareness':
                $results = app(AwarenessRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAwarenessIndex($name_table));
                break; 
            case 'his_bed_bsty':
                $results =app(BedBstyRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedBstyIndex($name_table));
                break; 
            case 'his_bed':
                $results = app(BedRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedIndex($name_table));
                break;
            case 'his_bed_room':
                $results = app(BedRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedRoomIndex($name_table));
                break;
            case 'his_bed_type':
                $results = app(BedTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedTypeIndex($name_table));
                break;
            case 'his_bhyt_blacklist':
                $results = app(BhytBlacklistRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBhytBlacklistIndex($name_table));
                break;
            case 'his_bhyt_param':
                $results = app(BhytParamRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBhytParamIndex($name_table));
                break;
            case 'his_bhyt_whitelist':
                $results = app(BhytWhitelistRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBhytWhitelistIndex($name_table));
                break;
            case 'his_bid_type':
                $results = app(BidTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBidTypeIndex($name_table));
                break;
            case 'his_blood_group':
                $results = app(BloodGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBloodGroupIndex($name_table));
                break;
            case 'his_blood_volume':
                $results = app(BloodVolumeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBloodVolumeIndex($name_table));
                break;
            case 'his_body_part':
                $results = app(BodyPartRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBodyPartIndex($name_table));
                break;
            case 'his_born_position':
                $results = app(BornPositionRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBornPositionIndex($name_table));
                break;
            case 'his_branch':
                $results = app(BranchRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBranchIndex($name_table));
                break;
            case 'his_cancel_reason':
                $results = app(CancelReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCancelReasonIndex($name_table));
                break;
            case 'his_career':
                $results = app(CareerRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCareerIndex($name_table));
                break;
            case 'his_career_title':
                $results = app(CareerTitleRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCareerTitleIndex($name_table));
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
                'id'    => $result['id'],
                'body'  => $data
            ];

            $client->index($params);
        }
    }
}
