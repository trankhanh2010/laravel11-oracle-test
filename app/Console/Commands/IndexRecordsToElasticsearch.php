<?php

namespace App\Console\Commands;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\CreateAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\CreateAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\CreateAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\CreateAccidentLocationIndex;
use App\Events\Elastic\AccountBookVView\CreateAccountBookVViewIndex;
use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use App\Events\Elastic\Area\CreateAreaIndex;
use App\Events\Elastic\Atc\CreateAtcIndex;
use App\Events\Elastic\AtcGroup\CreateAtcGroupIndex;
use App\Events\Elastic\Awareness\CreateAwarenessIndex;
use Illuminate\Console\Command;
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
use App\Events\Elastic\DebateVView\CreateDebateVViewIndex;
use App\Events\Elastic\Department\CreateDepartmentIndex;
use App\Events\Elastic\Dhst\CreateDhstIndex;
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
use App\Events\Elastic\PatientTypeAlterVView\CreatePatientTypeAlterVViewIndex;
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
use App\Events\Elastic\SereServ\CreateSereServIndex;
use App\Events\Elastic\SereServBill\CreateSereServBillIndex;
use App\Events\Elastic\SereServDepositVView\CreateSereServDepositVViewIndex;
use App\Events\Elastic\SereServExt\CreateSereServExtIndex;
use App\Events\Elastic\SereServTein\CreateSereServTeinIndex;
use App\Events\Elastic\SereServTeinVView\CreateSereServTeinVViewIndex;
use App\Events\Elastic\SereServVView4\CreateSereServVView4Index;
use App\Events\Elastic\Service\CreateServiceIndex;
use App\Events\Elastic\ServiceCondition\CreateServiceConditionIndex;
use App\Events\Elastic\ServiceFollow\CreateServiceFollowIndex;
use App\Events\Elastic\ServiceGroup\CreateServiceGroupIndex;
use App\Events\Elastic\ServiceMachine\CreateServiceMachineIndex;
use App\Events\Elastic\ServicePaty\CreateServicePatyIndex;
use App\Events\Elastic\ServiceReqLView\CreateServiceReqLViewIndex;
use App\Events\Elastic\ServiceReqType\CreateServiceReqTypeIndex;
use App\Events\Elastic\ServiceRoom\CreateServiceRoomIndex;
use App\Events\Elastic\ServiceType\CreateServiceTypeIndex;
use App\Events\Elastic\ServiceUnit\CreateServiceUnitIndex;
use App\Events\Elastic\ServSegr\CreateServSegrIndex;
use App\Events\Elastic\SeseDepoRepayVView\CreateSeseDepoRepayVViewIndex;
use App\Events\Elastic\Speciality\CreateSpecialityIndex;
use App\Events\Elastic\StorageCondition\CreateStorageConditionIndex;
use App\Events\Elastic\SuimIndex\CreateSuimIndexIndex;
use App\Events\Elastic\SuimIndexUnit\CreateSuimIndexUnitIndex;
use App\Events\Elastic\Supplier\CreateSupplierIndex;
use App\Events\Elastic\TestIndex\CreateTestIndexIndex;
use App\Events\Elastic\TestIndexGroup\CreateTestIndexGroupIndex;
use App\Events\Elastic\TestIndexUnit\CreateTestIndexUnitIndex;
use App\Events\Elastic\TestSampleType\CreateTestSampleTypeIndex;
use App\Events\Elastic\TestServiceReq\CreateTestServiceReqIndex;
use App\Events\Elastic\TestServiceReqListVView\CreateTestServiceReqListVViewIndex;
use App\Events\Elastic\TestType\CreateTestTypeIndex;
use App\Events\Elastic\Tracking\CreateTrackingIndex;
use App\Events\Elastic\TranPatiTech\CreateTranPatiTechIndex;
use App\Events\Elastic\TransactionType\CreateTransactionTypeIndex;
use App\Events\Elastic\TreatmentBedRoomLView\CreateTreatmentBedRoomLViewIndex;
use App\Events\Elastic\TreatmentEndType\CreateTreatmentEndTypeIndex;
use App\Events\Elastic\TreatmentFeeView\CreateTreatmentFeeViewIndex;
use App\Events\Elastic\TreatmentLView\CreateTreatmentLViewIndex;
use App\Events\Elastic\TreatmentType\CreateTreatmentTypeIndex;
use App\Events\Elastic\UnlimitReason\CreateUnlimitReasonIndex;
use App\Events\Elastic\UserRoomVView\CreateUserRoomVViewIndex;
use App\Events\Elastic\VaccineType\CreateVaccineTypeIndex;
use App\Events\Elastic\WorkPlace\CreateWorkPlaceIndex;
use App\Repositories\AccidentBodyPartRepository;
use App\Repositories\AccidentCareRepository;
use App\Repositories\AccidentHurtTypeRepository;
use App\Repositories\AccidentLocationRepository;
use App\Repositories\AccountBookVViewRepository;
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
use App\Repositories\DebateVViewRepository;
use App\Repositories\DepartmentRepository;
use App\Repositories\DhstRepository;
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
use App\Repositories\PatientTypeAlterVViewRepository;
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
use App\Repositories\SereServBillRepository;
use App\Repositories\SereServDepositVViewRepository;
use App\Repositories\SereServExtRepository;
use App\Repositories\SereServRepository;
use App\Repositories\SereServTeinRepository;
use App\Repositories\SereServTeinVViewRepository;
use App\Repositories\SereServVView4Repository;
use App\Repositories\ServiceConditionRepository;
use App\Repositories\ServiceFollowRepository;
use App\Repositories\ServiceGroupRepository;
use App\Repositories\ServiceMachineRepository;
use App\Repositories\ServicePatyRepository;
use App\Repositories\ServiceRepository;
use App\Repositories\ServiceReqLViewRepository;
use App\Repositories\ServiceReqTypeRepository;
use App\Repositories\ServiceRoomRepository;
use App\Repositories\ServiceTypeRepository;
use App\Repositories\ServiceUnitRepository;
use App\Repositories\ServSegrRepository;
use App\Repositories\SeseDepoRepayVViewRepository;
use App\Repositories\SpecialityRepository;
use App\Repositories\StorageConditionRepository;
use App\Repositories\SuimIndexRepository;
use App\Repositories\SuimIndexUnitRepository;
use App\Repositories\SupplierRepository;
use App\Repositories\TestIndexGroupRepository;
use App\Repositories\TestIndexRepository;
use App\Repositories\TestIndexUnitRepository;
use App\Repositories\TestSampleTypeRepository;
use App\Repositories\TestServiceReqListVViewRepository;
use App\Repositories\TestServiceReqRepository;
use App\Repositories\TestTypeRepository;
use App\Repositories\TrackingRepository;
use App\Repositories\TranPatiTechRepository;
use App\Repositories\TransactionTypeRepository;
use App\Repositories\TreatmentBedRoomLViewRepository;
use App\Repositories\TreatmentEndTypeRepository;
use App\Repositories\TreatmentFeeViewRepository;
use App\Repositories\TreatmentLViewRepository;
use App\Repositories\TreatmentTypeRepository;
use App\Repositories\UnlimitReasonRepository;
use App\Repositories\UserRoomVViewRepository;
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

        $table = $table_arr;
        if ($param != 'all' && $param != null) {
            $table = explode(',', $param);
        }

        if ($table !== null) {
            foreach ($table as $key => $item) {
                if (in_array($item, $table_arr)) {
                    $this->processAndIndexData($item);
                    event(new DeleteCache($item));
                    $this->info('Đã tạo Job để tạo Index cho bảng ' . $item . '.');
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
        $batchSize = 5000;
        $results = null;
        // // Tùy chỉnh thời gian làm mới
        // $this->setRefreshInterval(-1, '*', $client);
        // call back dùng chunk để indexing
        $callback = function ($dataBatch) use ($name_table, $client) {
            $this->indexing($name_table, $this->arrJsonDecode(), $client, $dataBatch);
        };
        switch ($name_table) {
            case 'accident_body_part':
                event(new CreateAccidentBodyPartIndex($name_table));
                app(AccidentBodyPartRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'accident_care':
                event(new CreateAccidentCareIndex($name_table));
                app(AccidentCareRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'accident_hurt_type':
                event(new CreateAccidentHurtTypeIndex($name_table));
                app(AccidentHurtTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'accident_location':
                event(new CreateAccidentLocationIndex($name_table));
                app(AccidentLocationRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'age_type':
                event(new CreateAgeTypeIndex($name_table));
                app(AgeTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'area':
                event(new CreateAreaIndex($name_table));
                app(AreaRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'atc':
                event(new CreateAtcIndex($name_table));
                app(AtcRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'atc_group':
                event(new CreateAtcGroupIndex($name_table));
                app(AtcGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'awareness':
                event(new CreateAwarenessIndex($name_table));
                app(AwarenessRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bed_bsty':
                event(new CreateBedBstyIndex($name_table));
                app(BedBstyRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bed':
                event(new CreateBedIndex($name_table));
                app(BedRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bed_room':
                event(new CreateBedRoomIndex($name_table));
                app(BedRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bed_type':
                event(new CreateBedTypeIndex($name_table));
                app(BedTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bhyt_blacklist':
                event(new CreateBhytBlacklistIndex($name_table));
                app(BhytBlacklistRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bhyt_param':
                event(new CreateBhytParamIndex($name_table));
                app(BhytParamRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bhyt_whitelist':
                event(new CreateBhytWhitelistIndex($name_table));
                app(BhytWhitelistRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bid':
                event(new CreateBidIndex($name_table));
                app(BidRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'bid_type':
                event(new CreateBidTypeIndex($name_table));
                app(BidTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'blood_group':
                event(new CreateBloodGroupIndex($name_table));
                app(BloodGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'blood_volume':
                event(new CreateBloodVolumeIndex($name_table));
                app(BloodVolumeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'body_part':
                event(new CreateBodyPartIndex($name_table));
                app(BodyPartRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'born_position':
                event(new CreateBornPositionIndex($name_table));
                app(BornPositionRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'branch':
                event(new CreateBranchIndex($name_table));
                app(BranchRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'cancel_reason':
                event(new CreateCancelReasonIndex($name_table));
                app(CancelReasonRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'career':
                event(new CreateCareerIndex($name_table));
                app(CareerRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'career_title':
                event(new CreateCareerTitleIndex($name_table));
                app(CareerTitleRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'cashier_room':
                event(new CreateCashierRoomIndex($name_table));
                app(CashierRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'commune':
                event(new CreateCommuneIndex($name_table));
                app(CommuneRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'contraindication':
                event(new CreateContraindicationIndex($name_table));
                app(ContraindicationRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'data_store':
                event(new CreateDataStoreIndex($name_table));
                app(DataStoreRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'death_within':
                event(new CreateDeathWithinIndex($name_table));
                app(DeathWithinRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'debate_reason':
                event(new CreateDebateReasonIndex($name_table));
                app(DebateReasonRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'debate_type':
                event(new CreateDebateTypeIndex($name_table));
                app(DebateTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'department':
                event(new CreateDepartmentIndex($name_table));
                app(DepartmentRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'diim_type':
                event(new CreateDiimTypeIndex($name_table));
                app(DiimTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'district':
                event(new CreateDistrictIndex($name_table));
                app(DistrictRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'dosage_form':
                event(new CreateDosageFormIndex($name_table));
                app(DosageFormRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'emotionless_method':
                event(new CreateEmotionlessMethodIndex($name_table));
                app(EmotionlessMethodRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'employee':                
                event(new CreateEmployeeIndex($name_table));
                app(EmployeeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'ethnic':
                event(new CreateEthnicIndex($name_table));
                app(EthnicRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'execute_group':
                event(new CreateExecuteGroupIndex($name_table));
                app(ExecuteGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'execute_role':
                event(new CreateExecuteRoleIndex($name_table));
                app(ExecuteRoleRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'execute_role_user':
                event(new CreateExecuteRoleUserIndex($name_table));
                app(ExecuteRoleUserRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'execute_room':
                event(new CreateExecuteRoomIndex($name_table));
                app(ExecuteRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'exe_service_module':
                event(new CreateExeServiceModuleIndex($name_table));
                app(ExeServiceModuleRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'exp_mest_reason':
                event(new CreateExpMestReasonIndex($name_table));
                app(ExpMestReasonRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'exro_room':
                event(new CreateExroRoomIndex($name_table));
                app(ExroRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'file_type':
                event(new CreateFileTypeIndex($name_table));
                app(FileTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'film_size':
                event(new CreateFilmSizeIndex($name_table));
                app(FilmSizeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'fuex_type':
                event(new CreateFuexTypeIndex($name_table));
                app(FuexTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'gender':
                event(new CreateGenderIndex($name_table));
                app(GenderRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'group':
                event(new CreateGroupIndex($name_table));
                app(GroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'group_type':
                event(new CreateGroupTypeIndex($name_table));
                app(GroupTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'hein_service_type':
                event(new CreateHeinServiceTypeIndex($name_table));
                app(HeinServiceTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'hospitalize_reason':
                event(new CreateHospitalizeReasonIndex($name_table));
                app(HospitalizeReasonRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'htu':
                event(new CreateHtuIndex($name_table));
                app(HtuRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'icd_cm':
                event(new CreateIcdCmIndex($name_table));
                app(IcdCmRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'icd':
                event(new CreateIcdIndex($name_table));
                app(IcdRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'icd_group':
                event(new CreateIcdGroupIndex($name_table));
                app(IcdGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'imp_source':
                event(new CreateImpSourceIndex($name_table));
                app(ImpSourceRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'interaction_reason':
                event(new CreateInteractionReasonIndex($name_table));
                app(InteractionReasonRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'license_class':
                event(new CreateLicenseClassIndex($name_table));
                app(LicenseClassRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'location_store':
                event(new CreateLocationStoreIndex($name_table));
                app(LocationStoreRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'machine':
                event(new CreateMachineIndex($name_table));
                app(MachineRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'manufacturer':
                event(new CreateManufacturerIndex($name_table));
                app(ManufacturerRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'material_type':
                event(new CreateMaterialTypeIndex($name_table));
                app(MaterialTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'material_type_map':
                event(new CreateMaterialTypeMapIndex($name_table));
                app(MaterialTypeMapRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medical_contract':
                event(new CreateMedicalContractIndex($name_table));
                app(MedicalContractRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medicine':
                event(new CreateMedicineIndex($name_table));
                app(MedicineRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medicine_group':
                event(new CreateMedicineGroupIndex($name_table));
                app(MedicineGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medicine_line':
                event(new CreateMedicineLineIndex($name_table));
                app(MedicineLineRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medicine_paty':
                event(new CreateMedicinePatyIndex($name_table));
                app(MedicinePatyRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medicine_type_acin':
                event(new CreateMedicineTypeAcinIndex($name_table));
                app(MedicineTypeAcinRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medicine_type':
                event(new CreateMedicineTypeIndex($name_table));
                app(MedicineTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medicine_use_form':
                event(new CreateMedicineUseFormIndex($name_table));
                app(MedicineUseFormRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medi_org':
                event(new CreateMediOrgIndex($name_table));
                app(MediOrgRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medi_record_type':
                event(new CreateMediRecordTypeIndex($name_table));
                app(MediRecordTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medi_stock':
                event(new CreateMediStockIndex($name_table));
                app(MediStockRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medi_stock_maty':
                event(new CreateMediStockMatyIndex($name_table));
                app(MediStockMatyRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'medi_stock_mety':
                event(new CreateMediStockMetyIndex($name_table));
                app(MediStockMetyRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'mema_group':
                event(new CreateMemaGroupIndex($name_table));
                app(MemaGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'mest_patient_type':
                event(new CreateMestPatientTypeIndex($name_table));
                app(MestPatientTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'mest_room':
                event(new CreateMestRoomIndex($name_table));
                app(MestRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'military_rank':
                event(new CreateMilitaryRankIndex($name_table));
                app(MilitaryRankRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'module':
                event(new CreateModuleIndex($name_table));
                app(ModuleRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'module_role':
                event(new CreateModuleRoleIndex($name_table));
                app(ModuleRoleRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'national':
                event(new CreateNationalIndex($name_table));
                app(NationalRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'other_pay_source':
                event(new CreateOtherPaySourceIndex($name_table));
                app(OtherPaySourceRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'package':
                event(new CreatePackageIndex($name_table));
                app(PackageRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'packing_type':
                event(new CreatePackingTypeIndex($name_table));
                app(PackingTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'patient_case':
                event(new CreatePatientCaseIndex($name_table));
                app(PatientCaseRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'patient_classify':
                event(new CreatePatientClassifyIndex($name_table));
                app(PatientClassifyRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'patient_type_allow':
                event(new CreatePatientTypeAllowIndex($name_table));
                app(PatientTypeAllowRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'patient_type':
                event(new CreatePatientTypeIndex($name_table));
                app(PatientTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'patient_type_room':
                event(new CreatePatientTypeRoomIndex($name_table));
                app(PatientTypeRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'position':
                event(new CreatePositionIndex($name_table));
                app(PositionRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'preparations_blood':
                event(new CreatePreparationsBloodIndex($name_table));
                app(PreparationsBloodRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'priority_type':
                event(new CreatePriorityTypeIndex($name_table));
                app(PriorityTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'processing_method':
                event(new CreateProcessingMethodIndex($name_table));
                app(ProcessingMethodRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'province':
                event(new CreateProvinceIndex($name_table));
                app(ProvinceRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'pttt_catastrophe':
                event(new CreatePtttCatastropheIndex($name_table));
                app(PtttCatastropheRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'pttt_condition':
                event(new CreatePtttConditionIndex($name_table));
                app(PtttConditionRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'pttt_group':
                event(new CreatePtttGroupIndex($name_table));
                app(PtttGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'pttt_method':
                event(new CreatePtttMethodIndex($name_table));
                app(PtttMethodRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'pttt_table':
                event(new CreatePtttTableIndex($name_table));
                app(PtttTableRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'ration_group':
                event(new CreateRationGroupIndex($name_table));
                app(RationGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'ration_time':
                event(new CreateRationTimeIndex($name_table));
                app(RationTimeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'reception_room':
                event(new CreateReceptionRoomIndex($name_table));
                app(ReceptionRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'refectory':
                event(new CreateRefectoryIndex($name_table));
                app(RefectoryRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'relation':
                event(new CreateRelationIndex($name_table));
                app(RelationRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'religion':
                event(new CreateReligionIndex($name_table));
                app(ReligionRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'role':
                event(new CreateRoleIndex($name_table));
                app(RoleRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'room':
                event(new CreateRoomIndex($name_table));
                app(RoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'room_group':
                event(new CreateRoomGroupIndex($name_table));
                app(RoomGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'room_type':
                event(new CreateRoomTypeIndex($name_table));
                app(RoomTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sale_profit_cfg':
                event(new CreateSaleProfitCfgIndex($name_table));
                app(SaleProfitCfgRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_condition':
                event(new CreateServiceConditionIndex($name_table));
                app(ServiceConditionRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service':
                event(new CreateServiceIndex($name_table));
                app(ServiceRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_follow':
                event(new CreateServiceFollowIndex($name_table));
                app(ServiceFollowRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_group':
                event(new CreateServiceGroupIndex($name_table));
                app(ServiceGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_machine':
                event(new CreateServiceMachineIndex($name_table));
                app(ServiceMachineRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_paty':
                event(new CreateServicePatyIndex($name_table));
                app(ServicePatyRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_req_type':
                event(new CreateServiceReqTypeIndex($name_table));
                app(ServiceReqTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_room':
                event(new CreateServiceRoomIndex($name_table));
                app(ServiceRoomRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_type':
                event(new CreateServiceTypeIndex($name_table));
                app(ServiceTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'service_unit':
                event(new CreateServiceUnitIndex($name_table));
                app(ServiceUnitRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'serv_segr':
                event(new CreateServSegrIndex($name_table));
                app(ServSegrRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'speciality':
                event(new CreateSpecialityIndex($name_table));
                app(SpecialityRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'storage_condition':
                event(new CreateStorageConditionIndex($name_table));
                app(StorageConditionRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'suim_index':
                event(new CreateSuimIndexIndex($name_table));
                app(SuimIndexRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'suim_index_unit':
                event(new CreateSuimIndexUnitIndex($name_table));
                app(SuimIndexUnitRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'supplier':
                event(new CreateSupplierIndex($name_table));
                app(SupplierRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'test_index':
                event(new CreateTestIndexIndex($name_table));
                app(TestIndexRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'test_index_group':
                event(new CreateTestIndexGroupIndex($name_table));
                app(TestIndexGroupRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'test_index_unit':
                event(new CreateTestIndexUnitIndex($name_table));
                app(TestIndexUnitRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'test_sample_type':
                event(new CreateTestSampleTypeIndex($name_table));
                app(TestSampleTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'test_type':
                event(new CreateTestTypeIndex($name_table));
                app(TestTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'tran_pati_tech':
                event(new CreateTranPatiTechIndex($name_table));
                app(TranPatiTechRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'transaction_type':
                event(new CreateTransactionTypeIndex($name_table));
                app(TransactionTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'treatment_end_type':
                event(new CreateTreatmentEndTypeIndex($name_table));
                app(TreatmentEndTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'treatment_type':
                event(new CreateTreatmentTypeIndex($name_table));
                app(TreatmentTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'unlimit_reason':
                event(new CreateUnlimitReasonIndex($name_table));
                app(UnlimitReasonRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'vaccine_type':
                event(new CreateVaccineTypeIndex($name_table));
                app(VaccineTypeRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'work_place':
                event(new CreateWorkPlaceIndex($name_table));
                app(WorkPlaceRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;

                // No Cache
            case 'service_req_l_view':
                $batchSize = 25000;
                event(new CreateServiceReqLViewIndex($name_table));
                app(ServiceReqLViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'debate':
                $batchSize = 10000;
                event(new CreateDebateIndex($name_table));
                app(DebateRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'debate_v_view':
                $batchSize = 25000;
                event(new CreateDebateVViewIndex($name_table));
                app(DebateVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'user_room_v_view':
                $batchSize = 25000;
                event(new CreateUserRoomVViewIndex($name_table));
                app(UserRoomVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'debate_user':
                $batchSize = 25000;
                event(new CreateDebateUserIndex($name_table));
                app(DebateUserRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'debate_ekip_user':
                $batchSize = 25000;
                event(new CreateDebateEkipUserIndex($name_table));
                app(DebateEkipUserRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'tracking':
                $batchSize = 25000;
                event(new CreateTrackingIndex($name_table));
                app(TrackingRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'test_service_req_list_v_view':
                $batchSize = 10000;
                event(new CreateTestServiceReqListVViewIndex($name_table));
                app(TestServiceReqListVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sere_serv':
                $batchSize = 2000;
                event(new CreateSereServIndex($name_table));
                app(SereServRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sere_serv_v_view_4':
                $batchSize = 10000;
                event(new CreateSereServVView4Index($name_table));
                app(SereServVView4Repository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'patient_type_alter_v_view':
                $batchSize = 25000;
                event(new CreatePatientTypeAlterVViewIndex($name_table));
                app(PatientTypeAlterVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'treatment_l_view':
                $batchSize = 25000;
                event(new CreateTreatmentLViewIndex($name_table));
                app(TreatmentLViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'treatment_fee_view':
                $batchSize = 25000;
                event(new CreateTreatmentFeeViewIndex($name_table));
                app(TreatmentFeeViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'treatment_bed_room_l_view':
                $batchSize = 25000;
                event(new CreateTreatmentBedRoomLViewIndex($name_table));
                app(TreatmentBedRoomLViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'dhst':
                $batchSize = 10000;
                event(new CreateDhstIndex($name_table));
                app(DhstRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sere_serv_ext':
                $batchSize = 25000;
                event(new CreateSereServExtIndex($name_table));
                app(SereServExtRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sere_serv_tein':
                $batchSize = 25000;
                event(new CreateSereServTeinIndex($name_table));
                app(SereServTeinRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sere_serv_tein_v_view':
                $batchSize = 25000;
                event(new CreateSereServTeinVViewIndex($name_table));
                app(SereServTeinVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sere_serv_bill':
                $batchSize = 25000;
                event(new CreateSereServBillIndex($name_table));
                app(SereServBillRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sere_serv_deposit_v_view':
                $batchSize = 25000;
                event(new CreateSereServDepositVViewIndex($name_table));
                app(SereServDepositVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'sese_depo_repay_v_view':
                $batchSize = 25000;
                event(new CreateSeseDepoRepayVViewIndex($name_table));
                app(SeseDepoRepayVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            case 'account_book_v_view':
                $batchSize = 25000;
                event(new CreateAccountBookVViewIndex($name_table));
                app(AccountBookVViewRepository::class)->getDataFromDbToElastic($batchSize, null);
                break;
            default:
                // Xử lý mặc định hoặc xử lý khi không có bảng khớp
                $this->error('Không có dữ liệu của bảng ' . $name_table . '.');
                break;
        }
        $this->indexing($name_table, $this->arrJsonDecode(), $client, $results);
        // // Chỉnh lại thời gian làm mới
        // $this->setRefreshInterval('1s', '*', $client);
    }
    public function arrJsonDecode()
    {
        // Danh sách các bảng dùng with cần phải decode trước khi thêm vào elastic
        $arr_json_decode = config('params')['elastic']['json_decode'];
        return  $arr_json_decode;
    }
    public function indexing($name_table, $arr_json_decode, $client, $results)
    {
        $maxBatchSizeMB = config('database')['connections']['elasticsearch']['bulk']['max_batch_size_mb'];
        if (isset($results)) {
            // Dùng Bulk
            $bulkData = [];
            $currentBatchSizeBytes = 0;
            $maxBatchSizeBytes = $maxBatchSizeMB * 1024 * 1024; // Chuyển đổi MB sang bytes

            foreach ($results as $result) {
                // Chuẩn bị dữ liệu cho mỗi bản ghi
                $data = [];
                // Decode và đổi tên trường về mặc định các bảng có dùng with
                if (in_array($name_table, $arr_json_decode)) {
                    $result = convertKeysToSnakeCase(json_decode($result, true));
                } else {
                    // Nếu không cần decode, giả sử $result là mảng
                    $result = is_string($result) ? json_decode($result, true) : $result;
                }
                foreach ($result as $key => $value) {
                    $data[$key] = $value;
                }
                // Thêm các thông tin cần thiết cho mỗi tài liệu vào bulkData
                $actionMeta = [
                    'index' => [
                        '_index' => $name_table,
                        '_id'    => $result['id'], // Sử dụng id của bản ghi làm id cho Elasticsearch
                    ]
                ];
                // Tính kích thước của actionMeta và data
                $actionMetaSize = strlen(json_encode($actionMeta)) + 1; // Thêm 1 byte cho dấu xuống dòng
                $dataSize = strlen(json_encode($data)) + 1; // Thêm 1 byte cho dấu xuống dòng
                // Kiểm tra nếu thêm vào lô hiện tại vượt quá giới hạn kích thước
                if (($currentBatchSizeBytes + $actionMetaSize + $dataSize) > $maxBatchSizeBytes) {
                    // Thực hiện bulk insert với lô hiện tại
                    if (!empty($bulkData)) {
                        $client->bulk(['body' => $bulkData]);
                        // Reset bulkData và currentBatchSizeBytes sau khi bulk insert
                        $bulkData = [];
                        $currentBatchSizeBytes = 0;
                    }
                }
                // Thêm actionMeta và data vào bulkData
                $bulkData[] = $actionMeta;
                $bulkData[] = $data;
                $currentBatchSizeBytes += $actionMetaSize + $dataSize;
            }
            // Chèn các bản ghi còn lại nếu có
            if (!empty($bulkData)) {
                $client->bulk(['body' => $bulkData]);
            }
        }
    }
    public function setRefreshInterval($time, $index = '*', $client)
    {
        $params = [
            'index' => $index,
            'body' => [
                'settings' => [
                    'refresh_interval' => $time,
                ],
            ]
        ];
        // Sử dụng putSettings để thay đổi cài đặt
        $client->indices()->putSettings($params);
    }
}
