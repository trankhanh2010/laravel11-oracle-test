<?php

namespace App\Jobs\ElasticSearch\Index;

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
use App\Repositories\ServiceReqLViewRepository;
use App\Repositories\TestServiceReqListVViewRepository;
use App\Repositories\TrackingRepository;
use App\Repositories\UserRoomVViewRepository;
use App\Repositories\SereServVView4Repository;
use App\Repositories\ServiceConditionRepository;
use App\Repositories\ServiceFollowRepository;
use App\Repositories\ServiceGroupRepository;
use App\Repositories\ServiceMachineRepository;
use App\Repositories\ServicePatyRepository;
use App\Repositories\ServiceRepository;
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
use App\Repositories\TestTypeRepository;
use App\Repositories\TranPatiTechRepository;
use App\Repositories\TreatmentBedRoomLViewRepository;
use App\Repositories\TreatmentEndTypeRepository;
use App\Repositories\TreatmentFeeViewRepository;
use App\Repositories\TreatmentLViewRepository;
use App\Repositories\TreatmentTypeRepository;
use App\Repositories\UnlimitReasonRepository;
use App\Repositories\VaccineTypeRepository;
use App\Repositories\WorkPlaceRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessElasticIndexingJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $startId;
    protected $endId;
    protected $batchSize;
    protected $name;
    protected $nameTable;
    protected $paramWith;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $nameTable, $startId, $endId, $batchSize, $paramWith = null)
    {
        $this->name = $name;
        $this->nameTable = $nameTable;
        $this->startId = $startId;
        $this->endId = $endId;
        $this->batchSize = $batchSize;
        $this->paramWith = $paramWith;
    }

    /**
     * Execute the job.
     */
    // public function handle()
    // {
    //     $batchData = [];
    //     $count = 0;
    //     $repository = $this->repository($this->name);
    //     $query = $repository->applyJoins()
    //         ->whereBetween($this->nameTable . '.id', [$this->startId, $this->endId]);
    //     foreach ($query->cursor() as $item) {
    //         if ($this->paramWith != null) {
    //             $item->load($this->paramWith);
    //             $attributes = $item;
    //         }else{
    //             $attributes = $item->getAttributes();
    //         }
    //         $batchData[] = $attributes;
    //         $count++;

    //         if ($count % $this->batchSize == 0) {
    //             $this->indexing($this->name, $batchData);
    //             $batchData = [];
    //         }
    //     }
    //     // Gửi các bản ghi còn lại
    //     if (!empty($batchData)) {
    //         $this->indexing($this->name, $batchData);
    //     }
    // }

    public function handle()
    {
        try {
            // $client = app('Elasticsearch');
            // // Tạm thời tắt làm mới khi có bản ghi mới
            // $this->setRefreshInterval(-1, $this->name, $client);
            $batchData = [];
            $count = 0;
            $repository = $this->repository($this->name);
            $query = $repository->applyJoins()
                ->whereBetween($this->nameTable . '.id', [$this->startId, $this->endId]);
            if ($this->paramWith != null) {
                $query->with($this->paramWith)->chunkById($this->batchSize, function ($items) use (&$batchData, &$count) {
                    $this->indexing($this->name, $items);
                });
            } else {
                foreach ($query->cursor() as $item) {
                    $attributes = $item->getAttributes();
                    $batchData[] = $attributes;
                    $count++;

                    if ($count % $this->batchSize == 0) {
                        $this->indexing($this->name, $batchData);
                        $batchData = [];
                    }
                }
            }
            // Gửi các bản ghi còn lại
            if (!empty($batchData)) {
                $this->indexing($this->name, $batchData);
            }
        } catch (\Exception $e) {
        } finally {
            // // Đặt lại thời gian làm mới
            // $this->setRefreshInterval(1, $this->name, $client);
            DB::disconnect();
        }
    }

    public function repository($name)
    {
        $repository = null;
        switch ($name) {
            case 'accident_body_part':
                $repository = app(AccidentBodyPartRepository::class);
                break;
            case 'accident_care':
                $repository = app(AccidentCareRepository::class);
                break;    
            case 'accident_hurt_type':
                $repository = app(AccidentHurtTypeRepository::class);
                break;        
            case 'accident_location':
                $repository = app(AccidentLocationRepository::class);
                break;
            case 'age_type':
                $repository = app(AgeTypeRepository::class);
                break;
            case 'area':
                $repository = app(AreaRepository::class);
                break;
            case 'atc':
                $repository = app(AtcRepository::class);
                break;
            case 'atc_group':
                $repository = app(AtcGroupRepository::class);
                break;
            case 'awareness':
                $repository = app(AwarenessRepository::class);
                break;
            case 'bed_bsty':
                $repository = app(BedBstyRepository::class);
                break;
            case 'bed':
                $repository = app(BedRepository::class);
                break;
            case 'bed_room':
                $repository = app(BedRoomRepository::class);
                break;
            case 'bed_type':
                $repository = app(BedTypeRepository::class);
                break;
            case 'bhyt_blacklist':
                $repository = app(BhytBlacklistRepository::class);
                break;
            case 'bhyt_param':
                $repository = app(BhytParamRepository::class);
                break;
            case 'bhyt_whitelist':
                $repository = app(BhytWhitelistRepository::class);
                break;
            case 'bid':
                $repository = app(BidRepository::class);
                break;
            case 'bid_type':
                $repository = app(BidTypeRepository::class);
                break;
            case 'blood_group':
                $repository = app(BloodGroupRepository::class);
                break;
            case 'blood_volume':
                $repository = app(BloodVolumeRepository::class);
                break;
            case 'body_part':
                $repository = app(BodyPartRepository::class);
                break;
            case 'born_position':
                $repository = app(BornPositionRepository::class);
                break;
            case 'branch':
                $repository = app(BranchRepository::class);
                break;
            case 'cancel_reason':
                $repository = app(CancelReasonRepository::class);
                break;
            case 'career':
                $repository = app(CareerRepository::class);
                break;
            case 'career_title':
                $repository = app(CareerTitleRepository::class);
                break;
            case 'cashier_room':
                $repository = app(CashierRoomRepository::class);
                break;
            case 'commune':
                $repository = app(CommuneRepository::class);
                break;
            case 'contraindication':
                $repository = app(ContraindicationRepository::class);
                break;
            case 'data_store':
                $repository = app(DataStoreRepository::class);
                break;
            case 'death_within':
                $repository = app(DeathWithinRepository::class);
                break;
            case 'debate_reason':
                $repository = app(DebateReasonRepository::class);
                break;
            case 'debate_type':
                $repository = app(DebateTypeRepository::class);
                break;
            case 'department':
                $repository = app(DepartmentRepository::class);
                break;
            case 'diim_type':
                $repository = app(DiimTypeRepository::class);
                break;
            case 'district':
                $repository = app(DistrictRepository::class);
                break;
            case 'dosage_form':
                $repository = app(DosageFormRepository::class);
                break;
            case 'emotionless_method':
                $repository = app(EmotionlessMethodRepository::class);
                break;
            case 'employee':                
                $repository = app(EmployeeRepository::class);
                break;
            case 'ethnic':
                $repository = app(EthnicRepository::class);
                break;
            case 'execute_group':
                $repository = app(ExecuteGroupRepository::class);
                break;
            case 'execute_role':
                $repository = app(ExecuteRoleRepository::class);
                break;
            case 'execute_role_user':
                $repository = app(ExecuteRoleUserRepository::class);
                break;
            case 'execute_room':
                $repository = app(ExecuteRoomRepository::class);
                break;
            case 'exe_service_module':
                $repository = app(ExeServiceModuleRepository::class);
                break;
            case 'exp_mest_reason':
                $repository = app(ExpMestReasonRepository::class);
                break;
            case 'exro_room':
                $repository = app(ExroRoomRepository::class);
                break;
            case 'file_type':
                $repository = app(FileTypeRepository::class);
                break;
            case 'film_size':
                $repository = app(FilmSizeRepository::class);
                break;
            case 'fuex_type':
                $repository = app(FuexTypeRepository::class);
                break;
            case 'gender':
                $repository = app(GenderRepository::class);
                break;
            case 'group':
                $repository = app(GroupRepository::class);
                break;
            case 'group_type':
                $repository = app(GroupTypeRepository::class);
                break;
            case 'hein_service_type':
                $repository = app(HeinServiceTypeRepository::class);
                break;
            case 'hospitalize_reason':
                $repository = app(HospitalizeReasonRepository::class);
                break;
            case 'htu':
                $repository = app(HtuRepository::class);
                break;
            case 'icd_cm':
                $repository = app(IcdCmRepository::class);
                break;
            case 'icd':
                $repository = app(IcdRepository::class);
                break;
            case 'icd_group':
                $repository = app(IcdGroupRepository::class);
                break;
            case 'imp_source':
                $repository = app(ImpSourceRepository::class);
                break;
            case 'interaction_reason':
                $repository = app(InteractionReasonRepository::class);
                break;
            case 'license_class':
                $repository = app(LicenseClassRepository::class);
                break;
            case 'location_store':
                $repository = app(LocationStoreRepository::class);
                break;
            case 'machine':
                $repository = app(MachineRepository::class);
                break;
            case 'manufacturer':
                $repository = app(ManufacturerRepository::class);
                break;
            case 'material_type':
                $repository = app(MaterialTypeRepository::class);
                break;
            case 'material_type_map':
                $repository = app(MaterialTypeMapRepository::class);
                break;
            case 'medical_contract':
                $repository = app(MedicalContractRepository::class);
                break;
            case 'medicine':
                $repository = app(MedicineRepository::class);
                break;
            case 'medicine_group':
                $repository = app(MedicineGroupRepository::class);
                break;
            case 'medicine_line':
                $repository = app(MedicineLineRepository::class);
                break;
            case 'medicine_paty':
                $repository = app(MedicinePatyRepository::class);
                break;
            case 'medicine_type_acin':
                $repository = app(MedicineTypeAcinRepository::class);
                break;
            case 'medicine_type':
                $repository = app(MedicineTypeRepository::class);
                break;
            case 'medicine_use_form':
                $repository = app(MedicineUseFormRepository::class);
                break;
            case 'medi_org':
                $repository = app(MediOrgRepository::class);
                break;
            case 'medi_record_type':
                $repository = app(MediRecordTypeRepository::class);
                break;
            case 'medi_stock':
                $repository = app(MediStockRepository::class);
                break;
            case 'medi_stock_maty':
                $repository = app(MediStockMatyRepository::class);
                break;
            case 'medi_stock_mety':
                $repository = app(MediStockMetyRepository::class);
                break;
            case 'mema_group':
                $repository = app(MemaGroupRepository::class);
                break;
            case 'mest_patient_type':
                $repository = app(MestPatientTypeRepository::class);
                break;
            case 'mest_room':
                $repository = app(MestRoomRepository::class);
                break;
            case 'military_rank':
                $repository = app(MilitaryRankRepository::class);
                break;
            case 'module':
                $repository = app(ModuleRepository::class);
                break;
            case 'module_role':
                $repository = app(ModuleRoleRepository::class);
                break;
            case 'national':
                $repository = app(NationalRepository::class);
                break;
            case 'other_pay_source':
                $repository = app(OtherPaySourceRepository::class);
                break;
            case 'package':
                $repository = app(PackageRepository::class);
                break;
            case 'packing_type':
                $repository = app(PackingTypeRepository::class);
                break;
            case 'patient_case':
                $repository = app(PatientCaseRepository::class);
                break;
            case 'patient_classify':
                $repository = app(PatientClassifyRepository::class);
                break;
            case 'patient_type_allow':
                $repository = app(PatientTypeAllowRepository::class);
                break;
            case 'patient_type':
                $repository = app(PatientTypeRepository::class);
                break;
            case 'patient_type_room':
                $repository = app(PatientTypeRoomRepository::class);
                break;
            case 'position':
                $repository = app(PositionRepository::class);
                break;
            case 'preparations_blood':
                $repository = app(PreparationsBloodRepository::class);
                break;
            case 'priority_type':
                $repository = app(PriorityTypeRepository::class);
                break;
            case 'processing_method':
                $repository = app(ProcessingMethodRepository::class);
                break;
            case 'province':
                $repository = app(ProvinceRepository::class);
                break;
            case 'pttt_catastrophe':
                $repository = app(PtttCatastropheRepository::class);
                break;
            case 'pttt_condition':
                $repository = app(PtttConditionRepository::class);
                break;
            case 'pttt_group':
                $repository = app(PtttGroupRepository::class);
                break;
            case 'pttt_method':
                $repository = app(PtttMethodRepository::class);
                break;
            case 'pttt_table':
                $repository = app(PtttTableRepository::class);
                break;
            case 'ration_group':
                $repository = app(RationGroupRepository::class);
                break;
            case 'ration_time':
                $repository = app(RationTimeRepository::class);
                break;
            case 'reception_room':
                $repository = app(ReceptionRoomRepository::class);
                break;
            case 'refectory':
                $repository = app(RefectoryRepository::class);
                break;
            case 'relation':
                $repository = app(RelationRepository::class);
                break;
            case 'religion':
                $repository = app(ReligionRepository::class);
                break;
            case 'role':
                $repository = app(RoleRepository::class);
                break;
            case 'room':
                $repository = app(RoomRepository::class);
                break;
            case 'room_group':
                $repository = app(RoomGroupRepository::class);
                break;
            case 'room_type':
                $repository = app(RoomTypeRepository::class);
                break;
            case 'sale_profit_cfg':
                $repository = app(SaleProfitCfgRepository::class);
                break;
            case 'service_condition':
                $repository = app(ServiceConditionRepository::class);
                break;
            case 'service':
                $repository = app(ServiceRepository::class);
                break;
            case 'service_follow':
                $repository = app(ServiceFollowRepository::class);
                break;
            case 'service_group':
                $repository = app(ServiceGroupRepository::class);
                break;
            case 'service_machine':
                $repository = app(ServiceMachineRepository::class);
                break;
            case 'service_paty':
                $repository = app(ServicePatyRepository::class);
                break;
            case 'service_req_type':
                $repository = app(ServiceReqTypeRepository::class);
                break;
            case 'service_room':
                $repository = app(ServiceRoomRepository::class);
                break;
            case 'service_type':
                $repository = app(ServiceTypeRepository::class);
                break;
            case 'service_unit':
                $repository = app(ServiceUnitRepository::class);
                break;
            case 'serv_segr':
                $repository = app(ServSegrRepository::class);
                break;
            case 'speciality':
                $repository = app(SpecialityRepository::class);
                break;
            case 'storage_condition':
                $repository = app(StorageConditionRepository::class);
                break;
            case 'suim_index':
                $repository = app(SuimIndexRepository::class);
                break;
            case 'suim_index_unit':
                $repository = app(SuimIndexUnitRepository::class);
                break;
            case 'supplier':
                $repository = app(SupplierRepository::class);
                break;
            case 'test_index':
                $repository = app(TestIndexRepository::class);
                break;
            case 'test_index_group':
                $repository = app(TestIndexGroupRepository::class);
                break;
            case 'test_index_unit':
                $repository = app(TestIndexUnitRepository::class);
                break;
            case 'test_sample_type':
                $repository = app(TestSampleTypeRepository::class);
                break;
            case 'test_type':
                $repository = app(TestTypeRepository::class);
                break;
            case 'tran_pati_tech':
                $repository = app(TranPatiTechRepository::class);
                break;
            case 'treatment_end_type':
                $repository = app(TreatmentEndTypeRepository::class);
                break;
            case 'treatment_type':
                $repository = app(TreatmentTypeRepository::class);
                break;
            case 'unlimit_reason':
                $repository = app(UnlimitReasonRepository::class);
                break;
            case 'vaccine_type':
                $repository = app(VaccineTypeRepository::class);
                break;
            case 'work_place':
                $repository = app(WorkPlaceRepository::class);
                break;

            /// No Cache
            case 'tracking':
                $repository = app(TrackingRepository::class);
                break;
            case 'service_req_l_view':
                $repository = app(ServiceReqLViewRepository::class);
                break;
            case 'test_service_req_list_v_view':
                $repository = app(TestServiceReqListVViewRepository::class);
                break;
            case 'debate':
                $repository = app(DebateRepository::class);
                break;
            case 'debate_v_view':
                $repository = app(DebateVViewRepository::class);
                break;
            case 'user_room_v_view':
                $repository = app(UserRoomVViewRepository::class);
                break;
            case 'debate_user':
                $repository = app(DebateUserRepository::class);
                break;
            case 'debate_ekip_user':
                $repository = app(DebateEkipUserRepository::class);
                break;
            case 'sere_serv':
                $repository = app(SereServRepository::class);
                break;
            case 'sere_serv_v_view_4':
                $repository = app(SereServVView4Repository::class);
                break;
            case 'patient_type_alter_v_view':
                $repository = app(PatientTypeAlterVViewRepository::class);
                break;
            case 'treatment_l_view':
                $repository = app(TreatmentLViewRepository::class);
                break;
            case 'treatment_fee_view':
                $repository = app(TreatmentFeeViewRepository::class);
                break;
            case 'treatment_bed_room_l_view':
                $repository = app(TreatmentBedRoomLViewRepository::class);
                break;
            case 'dhst':
                $repository = app(DhstRepository::class);
                break;
            case 'sere_serv_ext':
                $repository = app(SereServExtRepository::class);
                break;
            case 'sere_serv_tein':
                $repository = app(SereServTeinRepository::class);
                break;
            case 'sere_serv_tein_v_view':
                $repository = app(SereServTeinVViewRepository::class);
                break;
            case 'sere_serv_bill':
                $repository = app(SereServBillRepository::class);
                break;
            case 'sere_serv_deposit_v_view':
                $repository = app(SereServDepositVViewRepository::class);
                break;
            case 'sese_depo_repay_v_view':
                $repository = app(SeseDepoRepayVViewRepository::class);
                break;
            case 'account_book_v_view':
                $repository = app(AccountBookVViewRepository::class);
                break;
            default:
                break;
        }
        return $repository;
    }

    public function indexing($name_table, $results)
    {
        // Khởi tạo kết nối đến Elastic
        $client = app('Elasticsearch');
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
                if (in_array($name_table, config('params')['elastic']['json_decode'])) {
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
