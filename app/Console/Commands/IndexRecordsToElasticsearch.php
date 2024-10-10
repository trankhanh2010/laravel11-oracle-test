<?php

namespace App\Console\Commands;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\CreateAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\CreateAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\CreateAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\CreateAccidentLocationIndex;
use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use App\Events\Elastic\Area\CreateAreaIndex;
use App\Events\Elastic\Atc\CreateAtcIndex;
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
use App\Events\Elastic\Bid\CreateBidIndex;
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
use App\Events\Elastic\Debate\CreateDebateIndex;
use App\Events\Elastic\DebateEkipUser\CreateDebateEkipUserIndex;
use App\Events\Elastic\DebateReason\CreateDebateReasonIndex;
use App\Events\Elastic\DebateType\CreateDebateTypeIndex;
use App\Events\Elastic\DebateUser\CreateDebateUserIndex;
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
use App\Events\Elastic\GroupType\CreateGroupTypeIndex;
use App\Events\Elastic\HeinServiceType\CreateHeinServiceTypeIndex;
use App\Events\Elastic\HospitalizeReason\CreateHospitalizeReasonIndex;
use App\Events\Elastic\Htu\CreateHtuIndex;
use App\Events\Elastic\Icd\CreateIcdIndex;
use App\Events\Elastic\IcdCm\CreateIcdCmIndex;
use App\Events\Elastic\IcdGroup\CreateIcdGroupIndex;
use App\Events\Elastic\ImpSource\CreateImpSourceIndex;
use App\Events\Elastic\InteractionReason\CreateInteractionReasonIndex;
use App\Events\Elastic\LicenseClass\CreateLicenseClassIndex;
use App\Events\Elastic\LocationStore\CreateLocationStoreIndex;
use App\Events\Elastic\Machine\CreateMachineIndex;
use App\Events\Elastic\Manufacturer\CreateManufacturerIndex;
use App\Events\Elastic\MaterialType\CreateMaterialTypeIndex;
use App\Events\Elastic\MaterialTypeMap\CreateMaterialTypeMapIndex;
use App\Events\Elastic\MedicalContract\CreateMedicalContractIndex;
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
use App\Events\Elastic\MediStockMaty\CreateMediStockMatyIndex;
use App\Events\Elastic\MediStockMety\CreateMediStockMetyIndex;
use App\Events\Elastic\MemaGroup\CreateMemaGroupIndex;
use App\Events\Elastic\MestPatientType\CreateMestPatientTypeIndex;
use App\Events\Elastic\MestRoom\CreateMestRoomIndex;
use App\Events\Elastic\MilitaryRank\CreateMilitaryRankIndex;
use App\Events\Elastic\Module\CreateModuleIndex;
use App\Events\Elastic\ModuleRole\CreateModuleRoleIndex;
use App\Events\Elastic\National\CreateNationalIndex;
use App\Events\Elastic\OtherPaySource\CreateOtherPaySourceIndex;
use App\Events\Elastic\Package\CreatePackageIndex;
use App\Events\Elastic\PackingType\CreatePackingTypeIndex;
use App\Events\Elastic\PatientCase\CreatePatientCaseIndex;
use App\Events\Elastic\PatientClassify\CreatePatientClassifyIndex;
use App\Events\Elastic\PatientType\CreatePatientTypeIndex;
use App\Events\Elastic\PatientTypeAllow\CreatePatientTypeAllowIndex;
use App\Events\Elastic\PatientTypeRoom\CreatePatientTypeRoomIndex;
use App\Events\Elastic\Position\CreatePositionIndex;
use App\Events\Elastic\PreparationsBlood\CreatePreparationsBloodIndex;
use App\Events\Elastic\PriorityType\CreatePriorityTypeIndex;
use App\Events\Elastic\ProcessingMethod\CreateProcessingMethodIndex;
use App\Events\Elastic\Province\CreateProvinceIndex;
use App\Events\Elastic\PtttCatastrophe\CreatePtttCatastropheIndex;
use App\Events\Elastic\PtttCondition\CreatePtttConditionIndex;
use App\Events\Elastic\PtttGroup\CreatePtttGroupIndex;
use App\Events\Elastic\PtttMethod\CreatePtttMethodIndex;
use App\Events\Elastic\PtttTable\CreatePtttTableIndex;
use App\Events\Elastic\RationGroup\CreateRationGroupIndex;
use App\Events\Elastic\RationTime\CreateRationTimeIndex;
use App\Events\Elastic\ReceptionRoom\CreateReceptionRoomIndex;
use App\Events\Elastic\Refectory\CreateRefectoryIndex;
use App\Events\Elastic\Relation\CreateRelationIndex;
use App\Events\Elastic\Religion\CreateReligionIndex;
use App\Events\Elastic\Role\CreateRoleIndex;
use App\Events\Elastic\Room\CreateRoomIndex;
use App\Events\Elastic\RoomGroup\CreateRoomGroupIndex;
use App\Events\Elastic\RoomType\CreateRoomTypeIndex;
use App\Events\Elastic\SaleProfitCfg\CreateSaleProfitCfgIndex;
use App\Events\Elastic\Service\CreateServiceIndex;
use App\Events\Elastic\ServiceCondition\CreateServiceConditionIndex;
use App\Events\Elastic\ServiceFollow\CreateServiceFollowIndex;
use App\Events\Elastic\ServiceGroup\CreateServiceGroupIndex;
use App\Events\Elastic\ServiceMachine\CreateServiceMachineIndex;
use App\Events\Elastic\ServicePaty\CreateServicePatyIndex;
use App\Events\Elastic\ServiceReq\CreateServiceReqIndex;
use App\Events\Elastic\ServiceReqType\CreateServiceReqTypeIndex;
use App\Events\Elastic\ServiceRoom\CreateServiceRoomIndex;
use App\Events\Elastic\ServiceType\CreateServiceTypeIndex;
use App\Events\Elastic\ServiceUnit\CreateServiceUnitIndex;
use App\Events\Elastic\ServSegr\CreateServSegrIndex;
use App\Events\Elastic\Speciality\CreateSpecialityIndex;
use App\Events\Elastic\StorageCondition\CreateStorageConditionIndex;
use App\Events\Elastic\SuimIndex\CreateSuimIndexIndex;
use App\Events\Elastic\SuimIndexUnit\CreateSuimIndexUnitIndex;
use App\Events\Elastic\Supplier\CreateSupplierIndex;
use App\Events\Elastic\TestIndex\CreateTestIndexIndex;
use App\Events\Elastic\TestIndexGroup\CreateTestIndexGroupIndex;
use App\Events\Elastic\TestIndexUnit\CreateTestIndexUnitIndex;
use App\Events\Elastic\TestSampleType\CreateTestSampleTypeIndex;
use App\Events\Elastic\TestType\CreateTestTypeIndex;
use App\Events\Elastic\TranPatiTech\CreateTranPatiTechIndex;
use App\Events\Elastic\TreatmentEndType\CreateTreatmentEndTypeIndex;
use App\Events\Elastic\TreatmentType\CreateTreatmentTypeIndex;
use App\Events\Elastic\UnlimitReason\CreateUnlimitReasonIndex;
use App\Events\Elastic\UserRoom\CreateUserRoomIndex;
use App\Events\Elastic\VaccineType\CreateVaccineTypeIndex;
use App\Events\Elastic\WorkPlace\CreateWorkPlaceIndex;
use App\Http\Resources\DebateUserResource;
use App\Models\HIS\MedicineUseForm;
use App\Repositories\AccidentBodyPartRepository;
use App\Repositories\AccidentCareRepository;
use App\Repositories\AccidentHurtTypeRepository;
use App\Repositories\AccidentLocationRepository;
use App\Repositories\AgeTypeRepository;
use App\Repositories\AreaRepository;
use App\Repositories\AtcGroupRepository;
use App\Repositories\AtcRepository;
use App\Repositories\AwarenessRepository;
use App\Repositories\BedBstyRepository;
use App\Repositories\BedRepository;
use App\Repositories\BedRoomRepository;
use App\Repositories\BedTypeRepository;
use App\Repositories\BhytBlacklistRepository;
use App\Repositories\BhytParamRepository;
use App\Repositories\BhytWhitelistRepository;
use App\Repositories\BidRepository;
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
use App\Repositories\DebateEkipUserRepository;
use App\Repositories\DebateReasonRepository;
use App\Repositories\DebateRepository;
use App\Repositories\DebateTypeRepository;
use App\Repositories\DebateUserRepository;
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
use App\Repositories\GroupTypeRepository;
use App\Repositories\HeinServiceTypeRepository;
use App\Repositories\HospitalizeReasonRepository;
use App\Repositories\HtuRepository;
use App\Repositories\IcdCmRepository;
use App\Repositories\IcdGroupRepository;
use App\Repositories\IcdRepository;
use App\Repositories\ImpSourceRepository;
use App\Repositories\InteractionReasonRepository;
use App\Repositories\LicenseClassRepository;
use App\Repositories\LocationStoreRepository;
use App\Repositories\MachineRepository;
use App\Repositories\ManufacturerRepository;
use App\Repositories\MaterialTypeMapRepository;
use App\Repositories\MaterialTypeRepository;
use App\Repositories\MedicalContractRepository;
use App\Repositories\MedicineGroupRepository;
use App\Repositories\MedicineLineRepository;
use App\Repositories\MedicinePatyRepository;
use App\Repositories\MedicineRepository;
use App\Repositories\MedicineTypeAcinRepository;
use App\Repositories\MedicineTypeRepository;
use App\Repositories\MedicineUseFormRepository;
use App\Repositories\MediOrgRepository;
use App\Repositories\MediRecordTypeRepository;
use App\Repositories\MediStockMatyRepository;
use App\Repositories\MediStockMetyRepository;
use App\Repositories\MediStockRepository;
use App\Repositories\MemaGroupRepository;
use App\Repositories\MestPatientTypeRepository;
use App\Repositories\MestRoomRepository;
use App\Repositories\MilitaryRankRepository;
use App\Repositories\ModuleRepository;
use App\Repositories\ModuleRoleRepository;
use App\Repositories\NationalRepository;
use App\Repositories\OtherPaySourceRepository;
use App\Repositories\PackageRepository;
use App\Repositories\PackingTypeRepository;
use App\Repositories\PatientCaseRepository;
use App\Repositories\PatientClassifyRepository;
use App\Repositories\PatientTypeAllowRepository;
use App\Repositories\PatientTypeRepository;
use App\Repositories\PatientTypeRoomRepository;
use App\Repositories\PositionRepository;
use App\Repositories\PreparationsBloodRepository;
use App\Repositories\PriorityTypeRepository;
use App\Repositories\ProcessingMethodRepository;
use App\Repositories\ProvinceRepository;
use App\Repositories\PtttCatastropheRepository;
use App\Repositories\PtttConditionRepository;
use App\Repositories\PtttGroupRepository;
use App\Repositories\PtttMethodRepository;
use App\Repositories\PtttTableRepository;
use App\Repositories\RationGroupRepository;
use App\Repositories\RationTimeRepository;
use App\Repositories\ReceptionRoomRepository;
use App\Repositories\RefectoryRepository;
use App\Repositories\RelationRepository;
use App\Repositories\ReligionRepository;
use App\Repositories\RoleRepository;
use App\Repositories\RoomGroupRepository;
use App\Repositories\RoomRepository;
use App\Repositories\RoomTypeRepository;
use App\Repositories\SaleProfitCfgRepository;
use App\Repositories\ServiceConditionRepository;
use App\Repositories\ServiceFollowRepository;
use App\Repositories\ServiceGroupRepository;
use App\Repositories\ServiceMachineRepository;
use App\Repositories\ServicePatyRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\ServiceReqRepository;
use App\Repositories\ServiceReqTypeRepository;
use App\Repositories\ServiceRoomRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\ServiceUnitRepository;
use App\Repositories\ServSegrRepository;
use App\Repositories\SpecialityRepository;
use App\Repositories\StorageConditionRepository;
use App\Repositories\SuimIndexRepository;
use App\Repositories\SuimIndexUnitRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\TestIndexGroupRepository;
use App\Repositories\TestIndexRepository;
use App\Repositories\TestIndexUnitRepository;
use App\Repositories\TestSampleTypeRepository;
use App\Repositories\TestTypeRepository;
use App\Repositories\TranPatiTechRepository;
use App\Repositories\TreatmentEndTypeRepository;
use App\Repositories\TreatmentTypeRepository;
use App\Repositories\UnlimitReasonRepository;
use App\Repositories\UserRoomRepository;
use App\Repositories\VaccineTypeRepository;
use App\Repositories\WorkPlaceRepository;

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
        // call back dùng chunk để indexing
        $callback = function($dataBatch) use ($name_table, $client){
            $this->indexing($name_table,$this->arrJsonDecode(),$client,$dataBatch);
        };
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
            case 'atc':
                $results = app(AtcRepository::class)->getDataFromDbToElastic(null);
                event(new CreateAtcIndex($name_table));
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
                $results = app(BedBstyRepository::class)->getDataFromDbToElastic(null);
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
            case 'bid':
                $results = app(BidRepository::class)->getDataFromDbToElastic(null);
                event(new CreateBidIndex($name_table));
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
            case 'group_type':
                $results = app(GroupTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateGroupTypeIndex($name_table));
                break;
            case 'hein_service_type':
                $results = app(HeinServiceTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateHeinServiceTypeIndex($name_table));
                break;
            case 'hospitalize_reason':
                $results = app(HospitalizeReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateHospitalizeReasonIndex($name_table));
                break;
            case 'htu':
                $results = app(HtuRepository::class)->getDataFromDbToElastic(null);
                event(new CreateHtuIndex($name_table));
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
            case 'imp_source':
                $results = app(ImpSourceRepository::class)->getDataFromDbToElastic(null);
                event(new CreateImpSourceIndex($name_table));
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
            case 'material_type_map':
                $results = app(MaterialTypeMapRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMaterialTypeMapIndex($name_table));
                break;
            case 'medical_contract':
                $results = app(MedicalContractRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMedicalContractIndex($name_table));
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
            case 'medi_stock_maty':
                $results = app(MediStockMatyRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMediStockMatyIndex($name_table));
                break;
            case 'medi_stock_mety':
                $results = app(MediStockMetyRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMediStockMetyIndex($name_table));
                break;
            case 'mema_group':
                $results = app(MemaGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMemaGroupIndex($name_table));
                break;
            case 'mest_patient_type':
                $results = app(MestPatientTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMestPatientTypeIndex($name_table));
                break;
            case 'mest_room':
                $results = app(MestRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMestRoomIndex($name_table));
                break;
            case 'military_rank':
                $results = app(MilitaryRankRepository::class)->getDataFromDbToElastic(null);
                event(new CreateMilitaryRankIndex($name_table));
                break;
            case 'module':
                $results = app(ModuleRepository::class)->getDataFromDbToElastic(null);
                event(new CreateModuleIndex($name_table));
                break;
            case 'module_role':
                $results = app(ModuleRoleRepository::class)->getDataFromDbToElastic(null);
                event(new CreateModuleRoleIndex($name_table));
                break;
            case 'national':
                $results = app(NationalRepository::class)->getDataFromDbToElastic(null);
                event(new CreateNationalIndex($name_table));
                break;
            case 'other_pay_source':
                $results = app(OtherPaySourceRepository::class)->getDataFromDbToElastic(null);
                event(new CreateOtherPaySourceIndex($name_table));
                break;
            case 'package':
                $results = app(PackageRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePackageIndex($name_table));
                break;
            case 'packing_type':
                $results = app(PackingTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePackingTypeIndex($name_table));
                break;
            case 'patient_case':
                $results = app(PatientCaseRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePatientCaseIndex($name_table));
                break;
            case 'patient_classify':
                $results = app(PatientClassifyRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePatientClassifyIndex($name_table));
                break;
            case 'patient_type_allow':
                $results = app(PatientTypeAllowRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePatientTypeAllowIndex($name_table));
                break;
            case 'patient_type':
                $results = app(PatientTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePatientTypeIndex($name_table));
                break;
            case 'patient_type_room':
                $results = app(PatientTypeRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePatientTypeRoomIndex($name_table));
                break;
            case 'position':
                $results = app(PositionRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePositionIndex($name_table));
                break;
            case 'preparations_blood':
                $results = app(PreparationsBloodRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePreparationsBloodIndex($name_table));
                break;
            case 'priority_type':
                $results = app(PriorityTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePriorityTypeIndex($name_table));
                break;
            case 'processing_method':
                $results = app(ProcessingMethodRepository::class)->getDataFromDbToElastic(null);
                event(new CreateProcessingMethodIndex($name_table));
                break;
            case 'province':
                $results = app(ProvinceRepository::class)->getDataFromDbToElastic(null);
                event(new CreateProvinceIndex($name_table));
                break;
            case 'pttt_catastrophe':
                $results = app(PtttCatastropheRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePtttCatastropheIndex($name_table));
                break;
            case 'pttt_condition':
                $results = app(PtttConditionRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePtttConditionIndex($name_table));
                break;
            case 'pttt_group':
                $results = app(PtttGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePtttGroupIndex($name_table));
                break;
            case 'pttt_method':
                $results = app(PtttMethodRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePtttMethodIndex($name_table));
                break;
            case 'pttt_table':
                $results = app(PtttTableRepository::class)->getDataFromDbToElastic(null);
                event(new CreatePtttTableIndex($name_table));
                break;
            case 'ration_group':
                $results = app(RationGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRationGroupIndex($name_table));
                break;
            case 'ration_time':
                $results = app(RationTimeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRationTimeIndex($name_table));
                break;
            case 'reception_room':
                $results = app(ReceptionRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateReceptionRoomIndex($name_table));
                break;
            case 'refectory':
                $results = app(RefectoryRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRefectoryIndex($name_table));
                break;
            case 'relation':
                $results = app(RelationRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRelationIndex($name_table));
                break;
            case 'religion':
                $results = app(ReligionRepository::class)->getDataFromDbToElastic(null);
                event(new CreateReligionIndex($name_table));
                break;
            case 'role':
                $results = app(RoleRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRoleIndex($name_table));
                break;
            case 'room':
                $results = app(RoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRoomIndex($name_table));
                break;
            case 'room_group':
                $results = app(RoomGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRoomGroupIndex($name_table));
                break;
            case 'room_type':
                $results = app(RoomTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateRoomTypeIndex($name_table));
                break;
            case 'sale_profit_cfg':
                $results = app(SaleProfitCfgRepository::class)->getDataFromDbToElastic(null);
                event(new CreateSaleProfitCfgIndex($name_table));
                break;
            case 'service_condition':
                $results = app(ServiceConditionRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceConditionIndex($name_table));
                break;
            case 'service':
                $results = app(ServiceRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceIndex($name_table));
                break;
            case 'service_follow':
                $results = app(ServiceFollowRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceFollowIndex($name_table));
                break;
            case 'service_group':
                $results = app(ServiceGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceGroupIndex($name_table));
                break;
            case 'service_machine':
                $results = app(ServiceMachineRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceMachineIndex($name_table));
                break;
            case 'service_paty':
                $results = app(ServicePatyRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServicePatyIndex($name_table));
                break;
            case 'service_req_type':
                $results = app(ServiceReqTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceReqTypeIndex($name_table));
                break;
            case 'service_room':
                $results = app(ServiceRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceRoomIndex($name_table));
                break;
            case 'service_type':
                $results = app(ServiceTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceTypeIndex($name_table));
                break;
            case 'service_unit':
                $results = app(ServiceUnitRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServiceUnitIndex($name_table));
                break;
            case 'serv_segr':
                $results = app(ServSegrRepository::class)->getDataFromDbToElastic(null);
                event(new CreateServSegrIndex($name_table));
                break;
            case 'speciality':
                $results = app(SpecialityRepository::class)->getDataFromDbToElastic(null);
                event(new CreateSpecialityIndex($name_table));
                break;
            case 'storage_condition':
                $results = app(StorageConditionRepository::class)->getDataFromDbToElastic(null);
                event(new CreateStorageConditionIndex($name_table));
                break;
            case 'suim_index':
                $results = app(SuimIndexRepository::class)->getDataFromDbToElastic(null);
                event(new CreateSuimIndexIndex($name_table));
                break;
            case 'suim_index_unit':
                $results = app(SuimIndexUnitRepository::class)->getDataFromDbToElastic(null);
                event(new CreateSuimIndexUnitIndex($name_table));
                break;
            case 'supplier':
                $results = app(SupplierRepository::class)->getDataFromDbToElastic(null);
                event(new CreateSupplierIndex($name_table));
                break;
            case 'test_index':
                $results = app(TestIndexRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTestIndexIndex($name_table));
                break;
            case 'test_index_group':
                $results = app(TestIndexGroupRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTestIndexGroupIndex($name_table));
                break;
            case 'test_index_unit':
                $results = app(TestIndexUnitRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTestIndexUnitIndex($name_table));
                break;
            case 'test_sample_type':
                $results = app(TestSampleTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTestSampleTypeIndex($name_table));
                break;
            case 'test_type':
                $results = app(TestTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTestTypeIndex($name_table));
                break;
            case 'tran_pati_tech':
                $results = app(TranPatiTechRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTranPatiTechIndex($name_table));
                break;
            case 'treatment_end_type':
                $results = app(TreatmentEndTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTreatmentEndTypeIndex($name_table));
                break;
            case 'treatment_type':
                $results = app(TreatmentTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateTreatmentTypeIndex($name_table));
                break;
            case 'unlimit_reason':
                $results = app(UnlimitReasonRepository::class)->getDataFromDbToElastic(null);
                event(new CreateUnlimitReasonIndex($name_table));
                break;
            case 'vaccine_type':
                $results = app(VaccineTypeRepository::class)->getDataFromDbToElastic(null);
                event(new CreateVaccineTypeIndex($name_table));
                break;
            case 'work_place':
                $results = app(WorkPlaceRepository::class)->getDataFromDbToElastic(null);
                event(new CreateWorkPlaceIndex($name_table));
                break;


            // No Cache
            case 'user_room':
                $results = app(UserRoomRepository::class)->getDataFromDbToElastic(null);
                event(new CreateUserRoomIndex($name_table));
                break;
            case 'debate':
                $results = app(DebateRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDebateIndex($name_table));
                break;
            case 'debate_user':
                $results = app(DebateUserRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDebateUserIndex($name_table));
                break;
            case 'debate_ekip_user':
                $results = app(DebateEkipUserRepository::class)->getDataFromDbToElastic(null);
                event(new CreateDebateEkipUserIndex($name_table));
                break;
            case 'service_req':
                event(new CreateServiceReqIndex($name_table));
                $results = app(ServiceReqRepository::class)->getDataFromDbToElastic($callback, 50000, null);
                return response()->json(['message' => 'Ok.']);
            default:
                // Xử lý mặc định hoặc xử lý khi không có bảng khớp
                $this->error('Không có dữ liệu của bảng ' . $name_table . '.');
                break;
        }


        
        $this->indexing($name_table, $this->arrJsonDecode(), $client, $results);
    }
    public function arrJsonDecode(){
        // Danh sách các bảng dùng with cần phải decode trước khi thêm vào elastic
        $arr_json_decode = [
            'medi_stock',
            'patient_type',
            'pttt_group',
            'reception_room',
            'role',
            'service',
            'debate',
        ];
        return  $arr_json_decode;
    }
    public function indexing($name_table, $arr_json_decode, $client, $results){
        if (isset($results)) {
            // Dùng Bulk
            $bulkData = [];
            $batchSize = 50000; // Số lượng bản ghi mỗi batch, bạn có thể điều chỉnh
            foreach ($results as $result) {
                // Chuẩn bị dữ liệu cho mỗi bản ghi
                $data = [];
                // Decode và đổi tên trường về mặc định các bảng có dùng with
                if (in_array($name_table, $arr_json_decode)) {
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
