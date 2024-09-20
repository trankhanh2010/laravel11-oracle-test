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
use App\Events\Elastic\CashierRoom\CreateCashierRoomIndex;
use App\Events\Elastic\Commune\CreateCommuneIndex;
use App\Events\Elastic\Contraindication\CreateContraindicationIndex;
use App\Events\Elastic\DataStore\CreateDataStoreIndex;
use App\Events\Elastic\DeathWithin\CreateDeathWithinIndex;
use App\Events\Elastic\DebateReason\CreateDebateReasonIndex;
use App\Events\Elastic\DebateType\CreateDebateTypeIndex;
use App\Events\Elastic\Department\CreateDepartmentIndex;
use App\Events\Elastic\DiimType\CreateDiimTypeIndex;
use App\Events\Elastic\District\CreateDistrictIndex;
use App\Events\Elastic\DosageForm\CreateDosageFormIndex;
use App\Events\Elastic\EmotionlessMethod\CreateEmotionlessMethodIndex;
use App\Events\Elastic\Employee\CreateEmployeeIndex;
use App\Events\Elastic\Ethnic\CreateEthnicIndex;
use App\Events\Elastic\ExecuteGroup\CreateExecuteGroupIndex;
use App\Events\Elastic\ExecuteRole\CreateExecuteRoleIndex;
use App\Events\Elastic\ExecuteRoleUser\CreateExecuteRoleUserIndex;
use App\Events\Elastic\ExecuteRoom\CreateExecuteRoomIndex;
use App\Events\Elastic\ExeServiceModule\CreateExeServiceModuleIndex;
use App\Events\Elastic\ExpMestReason\CreateExpMestReasonIndex;
use App\Events\Elastic\ExroRoom\CreateExroRoomIndex;
use App\Events\Elastic\FileType\CreateFileTypeIndex;
use App\Events\Elastic\FilmSize\CreateFilmSizeIndex;
use App\Events\Elastic\FuexType\CreateFuexTypeIndex;
use App\Events\Elastic\Gender\CreateGenderIndex;
use App\Events\Elastic\Group\CreateGroupIndex;
use App\Events\Elastic\HeinServiceType\CreateHeinServiceTypeIndex;
use App\Events\Elastic\HospitalizeReason\CreateHospitalizeReasonIndex;
use App\Events\Elastic\Icd\CreateIcdIndex;
use App\Events\Elastic\IcdCm\CreateIcdCmIndex;
use App\Events\Elastic\IcdGroup\CreateIcdGroupIndex;
use App\Events\Elastic\InteractionReason\CreateInteractionReasonIndex;
use App\Events\Elastic\LicenseClass\CreateLicenseClassIndex;
use App\Events\Elastic\LocationStore\CreateLocationStoreIndex;
use App\Events\Elastic\Machine\CreateMachineIndex;
use App\Events\Elastic\Manufacturer\CreateManufacturerIndex;
use App\Events\Elastic\MaterialType\CreateMaterialTypeIndex;
use App\Events\Elastic\Medicine\CreateMedicineIndex;
use App\Events\Elastic\MedicineGroup\CreateMedicineGroupIndex;
use App\Events\Elastic\MedicineLine\CreateMedicineLineIndex;
use App\Events\Elastic\MedicinePaty\CreateMedicinePatyIndex;
use App\Events\Elastic\MedicineType\CreateMedicineTypeIndex;
use App\Events\Elastic\MedicineTypeAcin\CreateMedicineTypeAcinIndex;
use App\Events\Elastic\MedicineUseForm\CreateMedicineUseFormIndex;
use App\Events\Elastic\MediOrg\CreateMediOrgIndex;
use App\Events\Elastic\MediRecordType\CreateMediRecordTypeIndex;
use App\Events\Elastic\MediStock\CreateMediStockIndex;
use App\Models\HIS\MedicineUseForm;
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
use App\Repositories\CashierRoomRepository;
use App\Repositories\CommuneRepository;
use App\Repositories\ContraindicationRepository;
use App\Repositories\DataStoreRepository;
use App\Repositories\DeathWithinRepository;
use App\Repositories\DebateReasonRepository;
use App\Repositories\DebateTypeRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\DiimTypeRepository;
use App\Repositories\DistrictRepository;
use App\Repositories\DosageFormRepository;
use App\Repositories\EmotionlessMethodRepository;
use App\Repositories\EmployeeRepository;
use App\Repositories\EthnicRepository;
use App\Repositories\ExecuteGroupRepository;
use App\Repositories\ExecuteRoleRepository;
use App\Repositories\ExecuteRoleUserRepository;
use App\Repositories\ExecuteRoomRepository;
use App\Repositories\ExeServiceModuleRepository;
use App\Repositories\ExpMestReasonRepository;
use App\Repositories\ExroRoomRepository;
use App\Repositories\FileTypeRepository;
use App\Repositories\FilmSizeRepository;
use App\Repositories\FuexTypeRepository;
use App\Repositories\GenderRepository;
use App\Repositories\GroupRepository;
use App\Repositories\HeinServiceTypeRepository;
use App\Repositories\HospitalizeReasonRepository;
use App\Repositories\IcdCmRepository;
use App\Repositories\IcdGroupRepository;
use App\Repositories\IcdRepository;
use App\Repositories\InteractionReasonRepository;
use App\Repositories\LicenseClassRepository;
use App\Repositories\LocationStoreRepository;
use App\Repositories\MachineRepository;
use App\Repositories\ManufacturerRepository;
use App\Repositories\MaterialTypeRepository;
use App\Repositories\MedicineGroupRepository;
use App\Repositories\MedicineLineRepository;
use App\Repositories\MedicinePatyRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\MedicineTypeAcinRepository;
use App\Repositories\MedicineTypeRepository;
use App\Repositories\MedicineUseFormRepository;
use App\Repositories\MediOrgRepository;
use App\Repositories\MediRecordTypeRepository;
use App\Repositories\MediStockRepository;

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
                    $this->processAndIndexData($item);
                    event(new DeleteCache($item));
                    $this->info('Đã tạo Index cho bảng ' . $item . '.');
                } else {
                    $this->error('Không tồn tại bảng ' . $item . '.');
                }
            }
        }
    }

    protected function processAndIndexData($name_table)
    {
        // Khởi tạo kết nối đến Elastic
        $client = app('Elasticsearch');
        switch ($name_table) {
            case 'accident_body_part':
                $results = app(AccidentBodyPartRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentBodyPartIndex($name_table));
                break;
            case 'accident_care':
                $results = app(AccidentCareRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentCareIndex($name_table));
                break;    
            case 'accident_hurt_type':
                $results = app(AccidentHurtTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentHurtTypeIndex($name_table));
                break;    
            case 'accident_location':
                $results = app(AccidentLocationRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAccidentLocationIndex($name_table));
                break;     
            case 'age_type':
                $results = app(AgeTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAgeTypeIndex($name_table));
                break;     
            case 'area':
                $results = app(AreaRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAreaIndex($name_table));
                break;        
            case 'atc_group':
                $results = app(AtcGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAtcGroupIndex($name_table));
                break;  
            case 'awareness':
                $results = app(AwarenessRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAwarenessIndex($name_table));
                break; 
            case 'bed_bsty':
                $results =app(BedBstyRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedBstyIndex($name_table));
                break; 
            case 'bed':
                $results = app(BedRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedIndex($name_table));
                break;
            case 'bed_room':
                $results = app(BedRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedRoomIndex($name_table));
                break;
            case 'bed_type':
                $results = app(BedTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBedTypeIndex($name_table));
                break;
            case 'bhyt_blacklist':
                $results = app(BhytBlacklistRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBhytBlacklistIndex($name_table));
                break;
            case 'bhyt_param':
                $results = app(BhytParamRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBhytParamIndex($name_table));
                break;
            case 'bhyt_whitelist':
                $results = app(BhytWhitelistRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBhytWhitelistIndex($name_table));
                break;
            case 'bid_type':
                $results = app(BidTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBidTypeIndex($name_table));
                break;
            case 'blood_group':
                $results = app(BloodGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBloodGroupIndex($name_table));
                break;
            case 'blood_volume':
                $results = app(BloodVolumeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBloodVolumeIndex($name_table));
                break;
            case 'body_part':
                $results = app(BodyPartRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBodyPartIndex($name_table));
                break;
            case 'born_position':
                $results = app(BornPositionRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBornPositionIndex($name_table));
                break;
            case 'branch':
                $results = app(BranchRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBranchIndex($name_table));
                break;
            case 'cancel_reason':
                $results = app(CancelReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCancelReasonIndex($name_table));
                break;
            case 'career':
                $results = app(CareerRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCareerIndex($name_table));
                break;
            case 'career_title':
                $results = app(CareerTitleRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCareerTitleIndex($name_table));
                break;
            case 'cashier_room':
                $results = app(CashierRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCashierRoomIndex($name_table));
                break;
            case 'commune':
                $results = app(CommuneRepository::class)->getDataFromDbToElastic(null);
                event(new CreateCommuneIndex($name_table));
                break;
            case 'contraindication':
                $results = app(ContraindicationRepository::class)->getDataFromDbToElastic(null);
                event(new CreateContraindicationIndex($name_table));
                break;
            case 'data_store':
                $results = app(DataStoreRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDataStoreIndex($name_table));
                break;
            case 'death_within':
                $results = app(DeathWithinRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDeathWithinIndex($name_table));
                break;
            case 'debate_reason':
                $results = app(DebateReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDebateReasonIndex($name_table));
                break;
            case 'debate_type':
                $results = app(DebateTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDebateTypeIndex($name_table));
                break;
            case 'department':
                $results = app(DepartmentRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDepartmentIndex($name_table));
                break;
            case 'diim_type':
                $results = app(DiimTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDiimTypeIndex($name_table));
                break;
            case 'district':
                $results = app(DistrictRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDistrictIndex($name_table));
                break;
            case 'dosage_form':
                $results = app(DosageFormRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDosageFormIndex($name_table));
                break;
            case 'emotionless_method':
                $results = app(EmotionlessMethodRepository::class)->getDataFromDbToElastic(null);
                event(new CreateEmotionlessMethodIndex($name_table));
                break;
            case 'employee':
                $results = app(EmployeeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateEmployeeIndex($name_table));
                break;
            case 'ethnic':
                $results = app(EthnicRepository::class)->getDataFromDbToElastic(null);
                event(new CreateEthnicIndex($name_table));
                break;
            case 'execute_group':
                $results = app(ExecuteGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateExecuteGroupIndex($name_table));
                break;
            case 'execute_role':
                $results = app(ExecuteRoleRepository::class)->getDataFromDbToElastic(null);
                event(new CreateExecuteRoleIndex($name_table));
                break;
            case 'execute_role_user':
                $results = app(ExecuteRoleUserRepository::class)->getDataFromDbToElastic(null);
                event(new CreateExecuteRoleUserIndex($name_table));
                break;
            case 'execute_room':
                $results = app(ExecuteRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateExecuteRoomIndex($name_table));
                break;
            case 'exe_service_module':
                $results = app(ExeServiceModuleRepository::class)->getDataFromDbToElastic(null);
                event(new CreateExeServiceModuleIndex($name_table));
                break;
            case 'exp_mest_reason':
                $results = app(ExpMestReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateExpMestReasonIndex($name_table));
                break;
            case 'exro_room':
                $results = app(ExroRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateExroRoomIndex($name_table));
                break;
            case 'file_type':
                $results = app(FileTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateFileTypeIndex($name_table));
                break;
            case 'film_size':
                $results = app(FilmSizeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateFilmSizeIndex($name_table));
                break;
            case 'fuex_type':
                $results = app(FuexTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateFuexTypeIndex($name_table));
                break;
            case 'gender':
                $results = app(GenderRepository::class)->getDataFromDbToElastic(null);
                event(new CreateGenderIndex($name_table));
                break;
            case 'group':
                $results = app(GroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateGroupIndex($name_table));
                break;
            case 'hein_service_type':
                $results = app(HeinServiceTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateHeinServiceTypeIndex($name_table));
                break;
            case 'hospitalize_reason':
                $results = app(HospitalizeReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateHospitalizeReasonIndex($name_table));
                break;
            case 'icd_cm':
                $results = app(IcdCmRepository::class)->getDataFromDbToElastic(null);
                event(new CreateIcdCmIndex($name_table));
                break;
            case 'icd':
                $results = app(IcdRepository::class)->getDataFromDbToElastic(null);
                event(new CreateIcdIndex($name_table));
                break;
            case 'icd_group':
                $results = app(IcdGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateIcdGroupIndex($name_table));
                break;
            case 'interaction_reason':
                $results = app(InteractionReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateInteractionReasonIndex($name_table));
                break;
            case 'license_class':
                $results = app(LicenseClassRepository::class)->getDataFromDbToElastic(null);
                event(new CreateLicenseClassIndex($name_table));
                break;
            case 'location_store':
                $results = app(LocationStoreRepository::class)->getDataFromDbToElastic(null);
                event(new CreateLocationStoreIndex($name_table));
                break;
            case 'machine':
                $results = app(MachineRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMachineIndex($name_table));
                break;
            case 'manufacturer':
                $results = app(ManufacturerRepository::class)->getDataFromDbToElastic(null);
                event(new CreateManufacturerIndex($name_table));
                break;
            case 'material_type':
                $results = app(MaterialTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMaterialTypeIndex($name_table));
                break;
            case 'medicine':
                $results = app(MedicineRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicineIndex($name_table));
                break;
            case 'medicine_group':
                $results = app(MedicineGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicineGroupIndex($name_table));
                break;
            case 'medicine_line':
                $results = app(MedicineLineRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicineLineIndex($name_table));
                break;
            case 'medicine_paty':
                $results = app(MedicinePatyRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicinePatyIndex($name_table));
                break;
            case 'medicine_type_acin':
                $results = app(MedicineTypeAcinRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicineTypeAcinIndex($name_table));
                break;
            case 'medicine_type':
                $results = app(MedicineTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicineTypeIndex($name_table));
                break;
            case 'medicine_use_form':
                $results = app(MedicineUseFormRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicineUseFormIndex($name_table));
                break;
            case 'medi_org':
                $results = app(MediOrgRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMediOrgIndex($name_table));
                break;
            case 'medi_record_type':
                $results = app(MediRecordTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMediRecordTypeIndex($name_table));
                break;
            case 'medi_stock':
                $results = app(MediStockRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMediStockIndex($name_table));
                break;
            default:
                // Xử lý mặc định hoặc xử lý khi không có bảng khớp
                $this->error('Không có dữ liệu của bảng ' . $name_table . '.');
                break;
        }
        // Danh sách các bảng dùng with cần phải decode trước khi thêm vào elastic
        $arr_json_decode = [
            'medi_stock',
        ];

        // Chèn từng bản ghi
        // foreach ($results as $result) {
        //     $data = [];
        //     foreach ($result as $key => $value) {
        //         $data[$key] = $value;
        //     }
        //     $params = [
        //         'index' => $name_table,
        //         'id'    => $result['id'],
        //         'body'  => $data
        //     ];

        //     $client->index($params);
        // }
        if(isset($results)){
            // Dùng Bulk
        $bulkData = [];
        $batchSize = 10000; // Số lượng bản ghi mỗi batch, bạn có thể điều chỉnh
        foreach ($results as $result) {
            // Chuẩn bị dữ liệu cho mỗi bản ghi
            $data = [];
            // Decode và đổi tên trường về mặc định các bảng có dùng with
            if(in_array($name_table, $arr_json_decode)){
                $result = convertKeysToSnakeCase(json_decode($result, true));
            }
            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }
            // Thêm các thông tin cần thiết cho mỗi tài liệu vào bulkData
            $bulkData[] = [
                'index' => [
                    '_index' => $name_table,
                    '_id'    => $result['id'], // Sử dụng id của bản ghi làm id cho Elasticsearch
                ]
            ];
            $bulkData[] = $data;
            // Khi số lượng bản ghi đạt batchSize, thực hiện bulk insert
            if (count($bulkData) >= $batchSize * 2) { // Mỗi tài liệu chiếm 2 entry trong bulkData
                $client->bulk(['body' => $bulkData]);
                $bulkData = []; // Xóa dữ liệu sau khi chèn để chuẩn bị batch tiếp theo
            }
        }
        // Chèn các bản ghi còn lại nếu có
        if (!empty($bulkData)) {
            $client->bulk(['body' => $bulkData]);
        }
        }
    }
}
