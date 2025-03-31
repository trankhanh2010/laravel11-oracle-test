<?php

namespace App\Http\Controllers\BaseControllers;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\InsertAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\InsertAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\InsertAccidentLocationIndex;
use App\Events\Elastic\AccountBookVView\InsertAccountBookVViewIndex;
use App\Events\Elastic\AgeType\InsertAgeTypeIndex;
use App\Events\Elastic\Area\InsertAreaIndex;
use App\Events\Elastic\Atc\InsertAtcIndex;
use App\Events\Elastic\AtcGroup\InsertAtcGroupIndex;
use App\Events\Elastic\Awareness\InsertAwarenessIndex;
use App\Events\Elastic\Bed\InsertBedIndex;
use App\Events\Elastic\BedBsty\InsertBedBstyIndex;
use App\Events\Elastic\BedRoom\InsertBedRoomIndex;
use App\Events\Elastic\BedType\InsertBedTypeIndex;
use App\Events\Elastic\BhytBlacklist\InsertBhytBlacklistIndex;
use App\Events\Elastic\BhytParam\InsertBhytParamIndex;
use App\Events\Elastic\BhytWhitelist\InsertBhytWhitelistIndex;
use App\Events\Elastic\Bid\InsertBidIndex;
use App\Events\Elastic\BidType\InsertBidTypeIndex;
use App\Events\Elastic\BloodGroup\InsertBloodGroupIndex;
use App\Events\Elastic\BloodVolume\InsertBloodVolumeIndex;
use App\Events\Elastic\BodyPart\InsertBodyPartIndex;
use App\Events\Elastic\BornPosition\InsertBornPositionIndex;
use App\Events\Elastic\Branch\InsertBranchIndex;
use App\Events\Elastic\CancelReason\InsertCancelReasonIndex;
use App\Events\Elastic\Career\InsertCareerIndex;
use App\Events\Elastic\CareerTitle\InsertCareerTitleIndex;
use App\Events\Elastic\CashierRoom\InsertCashierRoomIndex;
use App\Events\Elastic\Commune\InsertCommuneIndex;
use App\Events\Elastic\Contraindication\InsertContraindicationIndex;
use App\Events\Elastic\DataStore\InsertDataStoreIndex;
use App\Events\Elastic\DeathCause\InsertDeathCauseIndex;
use App\Events\Elastic\DeathWithin\InsertDeathWithinIndex;
use App\Events\Elastic\Debate\InsertDebateIndex;
use App\Events\Elastic\DebateEkipUser\InsertDebateEkipUserIndex;
use App\Events\Elastic\DebateReason\InsertDebateReasonIndex;
use App\Events\Elastic\DebateType\InsertDebateTypeIndex;
use App\Events\Elastic\DebateUser\InsertDebateUserIndex;
use App\Events\Elastic\DebateVView\InsertDebateVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Events\Elastic\Department\InsertDepartmentIndex;
use App\Events\Elastic\Dhst\InsertDhstIndex;
use App\Events\Elastic\DiimType\InsertDiimTypeIndex;
use App\Events\Elastic\District\InsertDistrictIndex;
use App\Events\Elastic\DocumentType\InsertDocumentTypeIndex;
use App\Events\Elastic\DosageForm\InsertDosageFormIndex;
use App\Events\Elastic\EmotionlessMethod\InsertEmotionlessMethodIndex;
use App\Events\Elastic\Employee\InsertEmployeeIndex;
use App\Events\Elastic\EmrCoverType\InsertEmrCoverTypeIndex;
use App\Events\Elastic\EmrForm\InsertEmrFormIndex;
use App\Events\Elastic\Ethnic\InsertEthnicIndex;
use App\Events\Elastic\ExecuteGroup\InsertExecuteGroupIndex;
use App\Events\Elastic\ExecuteRole\InsertExecuteRoleIndex;
use App\Events\Elastic\ExecuteRoleUser\InsertExecuteRoleUserIndex;
use App\Events\Elastic\ExecuteRoom\InsertExecuteRoomIndex;
use App\Events\Elastic\ExeServiceModule\InsertExeServiceModuleIndex;
use App\Events\Elastic\ExpMestReason\InsertExpMestReasonIndex;
use App\Events\Elastic\ExroRoom\InsertExroRoomIndex;
use App\Events\Elastic\FileType\InsertFileTypeIndex;
use App\Events\Elastic\FilmSize\InsertFilmSizeIndex;
use App\Events\Elastic\FuexType\InsertFuexTypeIndex;
use App\Events\Elastic\Gender\InsertGenderIndex;
use App\Events\Elastic\Group\InsertGroupIndex;
use App\Events\Elastic\GroupType\InsertGroupTypeIndex;
use App\Events\Elastic\HeinServiceType\InsertHeinServiceTypeIndex;
use App\Events\Elastic\HospitalizeReason\InsertHospitalizeReasonIndex;
use App\Events\Elastic\Htu\InsertHtuIndex;
use App\Events\Elastic\Icd\InsertIcdIndex;
use App\Events\Elastic\IcdCm\InsertIcdCmIndex;
use App\Events\Elastic\IcdGroup\InsertIcdGroupIndex;
use App\Events\Elastic\IcdListVView\InsertIcdListVViewIndex;
use App\Events\Elastic\ImpSource\InsertImpSourceIndex;
use App\Events\Elastic\InteractionReason\InsertInteractionReasonIndex;
use App\Events\Elastic\LicenseClass\InsertLicenseClassIndex;
use App\Events\Elastic\LocationStore\InsertLocationStoreIndex;
use App\Events\Elastic\Machine\InsertMachineIndex;
use App\Events\Elastic\Manufacturer\InsertManufacturerIndex;
use App\Events\Elastic\MaterialType\InsertMaterialTypeIndex;
use App\Events\Elastic\MaterialTypeMap\InsertMaterialTypeMapIndex;
use App\Events\Elastic\MedicalContract\InsertMedicalContractIndex;
use App\Events\Elastic\Medicine\InsertMedicineIndex;
use App\Events\Elastic\MedicineGroup\InsertMedicineGroupIndex;
use App\Events\Elastic\MedicineLine\InsertMedicineLineIndex;
use App\Events\Elastic\MedicinePaty\InsertMedicinePatyIndex;
use App\Events\Elastic\MedicineType\InsertMedicineTypeIndex;
use App\Events\Elastic\MedicineTypeAcin\InsertMedicineTypeAcinIndex;
use App\Events\Elastic\MedicineUseForm\InsertMedicineUseFormIndex;
use App\Events\Elastic\MediOrg\InsertMediOrgIndex;
use App\Events\Elastic\MediRecordType\InsertMediRecordTypeIndex;
use App\Events\Elastic\MediStock\InsertMediStockIndex;
use App\Events\Elastic\MediStockMaty\InsertMediStockMatyIndex;
use App\Events\Elastic\MediStockMety\InsertMediStockMetyIndex;
use App\Events\Elastic\MemaGroup\InsertMemaGroupIndex;
use App\Events\Elastic\MestPatientType\InsertMestPatientTypeIndex;
use App\Events\Elastic\MestRoom\InsertMestRoomIndex;
use App\Events\Elastic\MilitaryRank\InsertMilitaryRankIndex;
use App\Events\Elastic\Module\InsertModuleIndex;
use App\Events\Elastic\ModuleRole\InsertModuleRoleIndex;
use App\Events\Elastic\National\InsertNationalIndex;
use App\Events\Elastic\OtherPaySource\InsertOtherPaySourceIndex;
use App\Events\Elastic\Package\InsertPackageIndex;
use App\Events\Elastic\PackingType\InsertPackingTypeIndex;
use App\Events\Elastic\PatientCase\InsertPatientCaseIndex;
use App\Events\Elastic\PatientClassify\InsertPatientClassifyIndex;
use App\Events\Elastic\PatientType\InsertPatientTypeIndex;
use App\Events\Elastic\PatientTypeAllow\InsertPatientTypeAllowIndex;
use App\Events\Elastic\PatientTypeAlterVView\InsertPatientTypeAlterVViewIndex;
use App\Events\Elastic\PatientTypeRoom\InsertPatientTypeRoomIndex;
use App\Events\Elastic\PayForm\InsertPayFormIndex;
use App\Events\Elastic\Position\InsertPositionIndex;
use App\Events\Elastic\PreparationsBlood\InsertPreparationsBloodIndex;
use App\Events\Elastic\PriorityType\InsertPriorityTypeIndex;
use App\Events\Elastic\ProcessingMethod\InsertProcessingMethodIndex;
use App\Events\Elastic\Province\InsertProvinceIndex;
use App\Events\Elastic\PtttCatastrophe\InsertPtttCatastropheIndex;
use App\Events\Elastic\PtttCondition\InsertPtttConditionIndex;
use App\Events\Elastic\PtttGroup\InsertPtttGroupIndex;
use App\Events\Elastic\PtttMethod\InsertPtttMethodIndex;
use App\Events\Elastic\PtttTable\InsertPtttTableIndex;
use App\Events\Elastic\RationGroup\InsertRationGroupIndex;
use App\Events\Elastic\RationTime\InsertRationTimeIndex;
use App\Events\Elastic\ReceptionRoom\InsertReceptionRoomIndex;
use App\Events\Elastic\Refectory\InsertRefectoryIndex;
use App\Events\Elastic\Relation\InsertRelationIndex;
use App\Events\Elastic\Religion\InsertReligionIndex;
use App\Events\Elastic\Role\InsertRoleIndex;
use App\Events\Elastic\Room\InsertRoomIndex;
use App\Events\Elastic\RoomGroup\InsertRoomGroupIndex;
use App\Events\Elastic\RoomType\InsertRoomTypeIndex;
use App\Events\Elastic\RoomVView\InsertRoomVViewIndex;
use App\Events\Elastic\SaleProfitCfg\InsertSaleProfitCfgIndex;
use App\Events\Elastic\SereServ\InsertSereServIndex;
use App\Events\Elastic\SereServBill\InsertSereServBillIndex;
use App\Events\Elastic\SereServDepositVView\InsertSereServDepositVViewIndex;
use App\Events\Elastic\SereServExt\InsertSereServExtIndex;
use App\Events\Elastic\SereServTein\InsertSereServTeinIndex;
use App\Events\Elastic\SereServTeinVView\InsertSereServTeinVViewIndex;
use App\Events\Elastic\SereServVView4\InsertSereServVView4Index;
use App\Events\Elastic\Service\InsertServiceIndex;
use App\Events\Elastic\ServiceCondition\InsertServiceConditionIndex;
use App\Events\Elastic\ServiceFollow\InsertServiceFollowIndex;
use App\Events\Elastic\ServiceGroup\InsertServiceGroupIndex;
use App\Events\Elastic\ServiceMachine\InsertServiceMachineIndex;
use App\Events\Elastic\ServicePaty\InsertServicePatyIndex;
use App\Events\Elastic\ServiceReqLView\InsertServiceReqLViewIndex;
use App\Events\Elastic\ServiceReqStt\InsertServiceReqSttIndex;
use App\Events\Elastic\ServiceReqType\InsertServiceReqTypeIndex;
use App\Events\Elastic\ServiceRoom\InsertServiceRoomIndex;
use App\Events\Elastic\ServiceType\InsertServiceTypeIndex;
use App\Events\Elastic\ServiceUnit\InsertServiceUnitIndex;
use App\Events\Elastic\ServSegr\InsertServSegrIndex;
use App\Events\Elastic\SeseDepoRepayVView\InsertSeseDepoRepayVViewIndex;
use App\Events\Elastic\Speciality\InsertSpecialityIndex;
use App\Events\Elastic\StorageCondition\InsertStorageConditionIndex;
use App\Events\Elastic\SuimIndex\InsertSuimIndexIndex;
use App\Events\Elastic\SuimIndexUnit\InsertSuimIndexUnitIndex;
use App\Events\Elastic\Supplier\InsertSupplierIndex;
use App\Events\Elastic\TestIndex\InsertTestIndexIndex;
use App\Events\Elastic\TestIndexGroup\InsertTestIndexGroupIndex;
use App\Events\Elastic\TestIndexUnit\InsertTestIndexUnitIndex;
use App\Events\Elastic\TestSampleType\InsertTestSampleTypeIndex;
use App\Events\Elastic\TestServiceReqListVView\InsertTestServiceReqListVViewIndex;
use App\Events\Elastic\TestType\InsertTestTypeIndex;
use App\Events\Elastic\Tracking\InsertTrackingIndex;
use App\Events\Elastic\TranPatiForm\InsertTranPatiFormIndex;
use App\Events\Elastic\TranPatiTech\InsertTranPatiTechIndex;
use App\Events\Elastic\TransactionType\InsertTransactionTypeIndex;
use App\Events\Elastic\TreatmentBedRoomLView\InsertTreatmentBedRoomLViewIndex;
use App\Events\Elastic\TreatmentEndType\InsertTreatmentEndTypeIndex;
use App\Events\Elastic\TreatmentFeeView\InsertTreatmentFeeViewIndex;
use App\Events\Elastic\TreatmentLView\InsertTreatmentLViewIndex;
use App\Events\Elastic\TreatmentResult\InsertTreatmentResultIndex;
use App\Events\Elastic\TreatmentType\InsertTreatmentTypeIndex;
use App\Events\Elastic\UnlimitReason\InsertUnlimitReasonIndex;
use App\Events\Elastic\UserRoomVView\InsertUserRoomVViewIndex;
use App\Events\Elastic\VaccineType\InsertVaccineTypeIndex;
use App\Events\Elastic\WorkPlace\InsertWorkPlaceIndex;
use App\Http\Controllers\Controller;
use App\Http\Resources\Elastic\ElasticMappingResource;
use App\Http\Resources\Elastic\ElasticResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;
use Carbon\Carbon;
use App\Http\Controllers\BaseControllers\BaseApiCacheController;
use ArrayObject;

class ElasticSearchController extends BaseApiCacheController
{
    protected $client;
    protected $all_table;

    public function __construct(Request $request)
    {
        parent::__construct($request); // Gọi constructor của BaseController
        $this->client = app('Elasticsearch');
        $this->all_table = config('params')['elastic']['all_table'];
    }
    public function getAllName(Request $request)
    {
        // Chỉ trả về key
        $data = $this->all_table;
        return returnDataSuccess([], $data);
    }
    public function indexRecordsToElasticsearch(Request $request)
    {
        $output = '';
        // Tăng thời gian chờ lên 
        set_time_limit(3600);

        if ($this->checkParam()) {
            return $this->checkParam();
        }

        // Nếu có all 
        if (in_array('all', $this->table)) {
            // Gọi command với Artisan::call
            Artisan::call('app:index-records-to-elasticsearch', [
                '--table' => 'all'
            ]);
            // Lấy kết quả từ command 
            $output = Artisan::output();
            return response()->json([
                'status'    => 200,
                'message' => 'Xong!',
                'output' => $output,
            ], 200);
        }

        // Nếu có Ids
        if ($this->ids != null) {
            $this->indexRecordsToElasticsearchByIds();
            return response()->json([
                'status'    => 200,
                'message' => 'Xong!',
                'output' => '',
            ], 200);
        }

        // Không thì lấy danh sách bảng cần index
        $table = implode(",", $this->table);
        // Gọi command với Artisan::call
        Artisan::call('app:index-records-to-elasticsearch', [
            '--table' => $table
        ]);
        // Lấy kết quả từ command 
        $output = Artisan::output();

        return response()->json([
            'status'    => 200,
            'message' => 'Xong!',
            'output' => $output,
        ], 200);
    }

    public function deleteIndex(Request $request)
    {
        $table = $this->all_table;

        $tables = $this->table;
        if ($this->table == 'all') {
            $tables = $table;
        }
        // Nếu xóa theo ids
        if($this->ids != null){
            $this->deleteIndexRecordsToElasticsearchByIds();
            return response()->json([
                'status'    => 200,
                'success' => true,
                'message' => 'Xong!'
            ], 200);
        }

        // không thì kiểm tra tên bảng và xóa bình thường
        foreach ($tables as $key => $item) {
            if (!in_array($item, $table) && $item != 'all') {
                return response()->json([
                    'status'    => 422,
                    'success' => true,
                    'message' => 'Giá trị ' . $item . ' không hợp lệ!'
                ], 422);
            }
        }
        if ($tables != null) {
            foreach ($tables as $key => $item) {
                $name_table = $item;
                $exists = $this->client->indices()->exists(['index' => $name_table])->asBool();
                if ($exists) {
                    $params = ['index' => $name_table];
                    event(new DeleteCache($name_table));
                    $this->client->indices()->delete($params);
                }
            }
            return response()->json([
                'status'    => 200,
                'success' => true,
                'message' => 'Xong!'
            ], 200);
        }
    }
    public function getMapping(Request $request)
    {
        $params = [
            'index' => $request->index,
        ];
        $response = new ElasticMappingResource($this->client->indices()->getMapping($params)[$request->index]);

        return returnDataSuccess([], $response);
    }
    public function getIndexSettings(Request $request)
    {
        $index = $request->index;
        $detail = $request->detail;
        $params = [
            'index' => $index
        ];

        $response = $this->client->indices()->get($params);
        switch ($detail) {
            case 'stop_filter':
                $response = $response[$index]['settings']['index']['analysis']['filter']['my_stop_filter'];
                break;

            default:
                // Xử lý mặc định hoặc xử lý khi không có bảng khớp
                $response = [];
                break;
        }
        return returnDataSuccess([], $response);
    }
    public function setMaxResultWindow(Request $request)
    {
        $table = $this->all_table;

        $tables = explode(",", $request->tables);
        if ($request->tables == null) {
            $tables = $table;
        }
        foreach ($tables as $key => $item) {
            if (!in_array($item, $table)) {
                return response()->json([
                    'status'    => 422,
                    'success' => true,
                    'message' => 'Giá trị ' . $item . ' không hợp lệ!'
                ], 422);
            }
        }
        $params = [
            'index' => $tables,
            'body' => [
                'index' => [
                    'max_result_window' => $request->max
                ]
            ]
        ];

        // Sử dụng putSettings để thay đổi cài đặt
        $this->client->indices()->putSettings($params);


        $response = [];
        return returnDataSuccess([], $response);
    }
    public function checkNodes()
    {
        try {
            $response = $this->client->nodes()->info(); // Lấy thông tin về các node

            return response()->json([
                'status' => 'success',
                'data' => $response->asArray(), // Chuyển đổi phản hồi sang dạng mảng
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
    public function ping()
    {
        // Ghi nhận thời gian bắt đầu
        $startTime = Carbon::now();

        try {
            // Khởi tạo client Elasticsearch
            $client = $this->client;

            // Kiểm tra kết nối với Elasticsearch (Ping request)
            $response = $client->ping();
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Không thể kết nối Elasticsearch: ' . $e->getMessage()
            ], 500);
        }

        // Ghi nhận thời gian kết thúc
        $endTime = Carbon::now();

        // Tính thời gian kết nối
        $elapsedTime = $startTime->diffInMilliseconds($endTime); // Thời gian tính bằng mili giây

        return response()->json([
            'status' => 'Elasticsearch connected successfully',
            'elapsed_time_ms' => $elapsedTime . ' ms',
        ]);
    }
    public function getDocsCount()
    {
        $response = $this->client->cat()->indices([
            'h' => 'index,docs.count',
            'format' => 'json',
        ]);
        
        $data = json_decode($response->getBody(), true);

        return returnDataSuccess([], $data);
    }
    public function indexRecordsToElasticsearchByIds()
    {
        foreach ($this->ids as $key => $item) {
            // Gọi event để thêm index vào elastic
            $data = new ArrayObject([
                'id' => $item,
            ], ArrayObject::ARRAY_AS_PROPS);
            $this->eventInsertIndex($data);
        }
        // Gọi event để xóa cache
        event(new DeleteCache($this->table[0]));
    }
    public function deleteIndexRecordsToElasticsearchByIds(){
        foreach ($this->ids as $key => $item) {
            // Gọi event để thêm index vào elastic
            $data = new ArrayObject([
                'id' => $item,
            ], ArrayObject::ARRAY_AS_PROPS);
            // Gọi event để xóa
            event(new DeleteIndex($data, $this->table[0]));
        }
        // Gọi event để xóa cache
        event(new DeleteCache($this->table[0]));
    }
    public function eventInsertIndex($data)
    {
        switch ($this->table[0]) {
            case 'accident_body_part':
                event(new InsertAccidentBodyPartIndex($data, $this->table[0]));
                break;
            case 'accident_care':
                event(new InsertAccidentCareIndex($data, $this->table[0]));
                break;
            case 'accident_hurt_type':
                event(new InsertAccidentHurtTypeIndex($data, $this->table[0]));
                break;
            case 'accident_location':
                event(new InsertAccidentLocationIndex($data, $this->table[0]));
                break;
            case 'age_type':
                event(new InsertAgeTypeIndex($data, $this->table[0]));
                break;
            case 'area':
                event(new InsertAreaIndex($data, $this->table[0]));
                break;
            case 'atc':
                event(new InsertAtcIndex($data, $this->table[0]));
                break;
            case 'atc_group':
                event(new InsertAtcGroupIndex($data, $this->table[0]));
                break;
            case 'awareness':
                event(new InsertAwarenessIndex($data, $this->table[0]));
                break;
            case 'bed_bsty':
                event(new InsertBedBstyIndex($data, $this->table[0]));
                break;
            case 'bed':
                event(new InsertBedIndex($data, $this->table[0]));
                break;
            case 'bed_room':
                event(new InsertBedRoomIndex($data, $this->table[0]));
                break;
            case 'bed_type':
                event(new InsertBedTypeIndex($data, $this->table[0]));
                break;
            case 'bhyt_blacklist':
                event(new InsertBhytBlacklistIndex($data, $this->table[0]));
                break;
            case 'bhyt_param':
                event(new InsertBhytParamIndex($data, $this->table[0]));
                break;
            case 'bhyt_whitelist':
                event(new InsertBhytWhitelistIndex($data, $this->table[0]));
                break;
            case 'bid':
                event(new InsertBidIndex($data, $this->table[0]));
                break;
            case 'bid_type':
                event(new InsertBidTypeIndex($data, $this->table[0]));
                break;
            case 'blood_group':
                event(new InsertBloodGroupIndex($data, $this->table[0]));
                break;
            case 'blood_volume':
                event(new InsertBloodVolumeIndex($data, $this->table[0]));
                break;
            case 'body_part':
                event(new InsertBodyPartIndex($data, $this->table[0]));
                break;
            case 'born_position':
                event(new InsertBornPositionIndex($data, $this->table[0]));
                break;
            case 'branch':
                event(new InsertBranchIndex($data, $this->table[0]));
                break;
            case 'cancel_reason':
                event(new InsertCancelReasonIndex($data, $this->table[0]));
                break;
            case 'career':
                event(new InsertCareerIndex($data, $this->table[0]));
                break;
            case 'career_title':
                event(new InsertCareerTitleIndex($data, $this->table[0]));
                break;
            case 'cashier_room':
                event(new InsertCashierRoomIndex($data, $this->table[0]));
                break;
            case 'commune':
                event(new InsertCommuneIndex($data, $this->table[0]));
                break;
            case 'contraindication':
                event(new InsertContraindicationIndex($data, $this->table[0]));
                break;
            case 'data_store':
                event(new InsertDataStoreIndex($data, $this->table[0]));
                break;
            case 'death_within':
                event(new InsertDeathWithinIndex($data, $this->table[0]));
                break;
            case 'debate_reason':
                event(new InsertDebateReasonIndex($data, $this->table[0]));
                break;
            case 'debate_type':
                event(new InsertDebateTypeIndex($data, $this->table[0]));
                break;
            case 'department':
                event(new InsertDepartmentIndex($data, $this->table[0]));
                break;
            case 'diim_type':
                event(new InsertDiimTypeIndex($data, $this->table[0]));
                break;
            case 'district':
                event(new InsertDistrictIndex($data, $this->table[0]));
                break;
            case 'dosage_form':
                event(new InsertDosageFormIndex($data, $this->table[0]));
                break;
            case 'emotionless_method':
                event(new InsertEmotionlessMethodIndex($data, $this->table[0]));
                break;
            case 'employee':
                event(new InsertEmployeeIndex($data, $this->table[0]));
                break;
            case 'ethnic':
                event(new InsertEthnicIndex($data, $this->table[0]));
                break;
            case 'execute_group':
                event(new InsertExecuteGroupIndex($data, $this->table[0]));
                break;
            case 'execute_role':
                event(new InsertExecuteRoleIndex($data, $this->table[0]));
                break;
            case 'execute_role_user':
                event(new InsertExecuteRoleUserIndex($data, $this->table[0]));
                break;
            case 'execute_room':
                event(new InsertExecuteRoomIndex($data, $this->table[0]));
                break;
            case 'exe_service_module':
                event(new InsertExeServiceModuleIndex($data, $this->table[0]));
                break;
            case 'exp_mest_reason':
                event(new InsertExpMestReasonIndex($data, $this->table[0]));
                break;
            case 'exro_room':
                event(new InsertExroRoomIndex($data, $this->table[0]));
                break;
            case 'file_type':
                event(new InsertFileTypeIndex($data, $this->table[0]));
                break;
            case 'film_size':
                event(new InsertFilmSizeIndex($data, $this->table[0]));
                break;
            case 'fuex_type':
                event(new InsertFuexTypeIndex($data, $this->table[0]));
                break;
            case 'gender':
                event(new InsertGenderIndex($data, $this->table[0]));
                break;
            case 'group':
                event(new InsertGroupIndex($data, $this->table[0]));
                break;
            case 'group_type':
                event(new InsertGroupTypeIndex($data, $this->table[0]));
                break;
            case 'hein_service_type':
                event(new InsertHeinServiceTypeIndex($data, $this->table[0]));
                break;
            case 'hospitalize_reason':
                event(new InsertHospitalizeReasonIndex($data, $this->table[0]));
                break;
            case 'htu':
                event(new InsertHtuIndex($data, $this->table[0]));
                break;
            case 'icd_cm':
                event(new InsertIcdCmIndex($data, $this->table[0]));
                break;
            case 'icd':
                event(new InsertIcdIndex($data, $this->table[0]));
                break;
            case 'icd_group':
                event(new InsertIcdGroupIndex($data, $this->table[0]));
                break;
            case 'imp_source':
                event(new InsertImpSourceIndex($data, $this->table[0]));
                break;
            case 'interaction_reason':
                event(new InsertInteractionReasonIndex($data, $this->table[0]));
                break;
            case 'license_class':
                event(new InsertLicenseClassIndex($data, $this->table[0]));
                break;
            case 'location_store':
                event(new InsertLocationStoreIndex($data, $this->table[0]));
                break;
            case 'machine':
                event(new InsertMachineIndex($data, $this->table[0]));
                break;
            case 'manufacturer':
                event(new InsertManufacturerIndex($data, $this->table[0]));
                break;
            case 'material_type':
                event(new InsertMaterialTypeIndex($data, $this->table[0]));
                break;
            case 'material_type_map':
                event(new InsertMaterialTypeMapIndex($data, $this->table[0]));
                break;
            case 'medical_contract':
                event(new InsertMedicalContractIndex($data, $this->table[0]));
                break;
            case 'medicine':
                event(new InsertMedicineIndex($data, $this->table[0]));
                break;
            case 'medicine_group':
                event(new InsertMedicineGroupIndex($data, $this->table[0]));
                break;
            case 'medicine_line':
                event(new InsertMedicineLineIndex($data, $this->table[0]));
                break;
            case 'medicine_paty':
                event(new InsertMedicinePatyIndex($data, $this->table[0]));
                break;
            case 'medicine_type_acin':
                event(new InsertMedicineTypeAcinIndex($data, $this->table[0]));
                break;
            case 'medicine_type':
                event(new InsertMedicineTypeIndex($data, $this->table[0]));
                break;
            case 'medicine_use_form':
                event(new InsertMedicineUseFormIndex($data, $this->table[0]));
                break;
            case 'medi_org':
                event(new InsertMediOrgIndex($data, $this->table[0]));
                break;
            case 'medi_record_type':
                event(new InsertMediRecordTypeIndex($data, $this->table[0]));
                break;
            case 'medi_stock':
                event(new InsertMediStockIndex($data, $this->table[0]));
                break;
            case 'medi_stock_maty':
                event(new InsertMediStockMatyIndex($data, $this->table[0]));
                break;
            case 'medi_stock_mety':
                event(new InsertMediStockMetyIndex($data, $this->table[0]));
                break;
            case 'mema_group':
                event(new InsertMemaGroupIndex($data, $this->table[0]));
                break;
            case 'mest_patient_type':
                event(new InsertMestPatientTypeIndex($data, $this->table[0]));
                break;
            case 'mest_room':
                event(new InsertMestRoomIndex($data, $this->table[0]));
                break;
            case 'military_rank':
                event(new InsertMilitaryRankIndex($data, $this->table[0]));
                break;
            case 'module':
                event(new InsertModuleIndex($data, $this->table[0]));
                break;
            case 'module_role':
                event(new InsertModuleRoleIndex($data, $this->table[0]));
                break;
            case 'national':
                event(new InsertNationalIndex($data, $this->table[0]));
                break;
            case 'other_pay_source':
                event(new InsertOtherPaySourceIndex($data, $this->table[0]));
                break;
            case 'package':
                event(new InsertPackageIndex($data, $this->table[0]));
                break;
            case 'packing_type':
                event(new InsertPackingTypeIndex($data, $this->table[0]));
                break;
            case 'patient_case':
                event(new InsertPatientCaseIndex($data, $this->table[0]));
                break;
            case 'patient_classify':
                event(new InsertPatientClassifyIndex($data, $this->table[0]));
                break;
            case 'patient_type_allow':
                event(new InsertPatientTypeAllowIndex($data, $this->table[0]));
                break;
            case 'patient_type':
                event(new InsertPatientTypeIndex($data, $this->table[0]));
                break;
            case 'patient_type_room':
                event(new InsertPatientTypeRoomIndex($data, $this->table[0]));
                break;
            case 'pay_form':
                event(new InsertPayFormIndex($data, $this->table[0]));
                break;
            case 'position':
                event(new InsertPositionIndex($data, $this->table[0]));
                break;
            case 'preparations_blood':
                event(new InsertPreparationsBloodIndex($data, $this->table[0]));
                break;
            case 'priority_type':
                event(new InsertPriorityTypeIndex($data, $this->table[0]));
                break;
            case 'processing_method':
                event(new InsertProcessingMethodIndex($data, $this->table[0]));
                break;
            case 'province':
                event(new InsertProvinceIndex($data, $this->table[0]));
                break;
            case 'pttt_catastrophe':
                event(new InsertPtttCatastropheIndex($data, $this->table[0]));
                break;
            case 'pttt_condition':
                event(new InsertPtttConditionIndex($data, $this->table[0]));
                break;
            case 'pttt_group':
                event(new InsertPtttGroupIndex($data, $this->table[0]));
                break;
            case 'pttt_method':
                event(new InsertPtttMethodIndex($data, $this->table[0]));
                break;
            case 'pttt_table':
                event(new InsertPtttTableIndex($data, $this->table[0]));
                break;
            case 'ration_group':
                event(new InsertRationGroupIndex($data, $this->table[0]));
                break;
            case 'ration_time':
                event(new InsertRationTimeIndex($data, $this->table[0]));
                break;
            case 'reception_room':
                event(new InsertReceptionRoomIndex($data, $this->table[0]));
                break;
            case 'refectory':
                event(new InsertRefectoryIndex($data, $this->table[0]));
                break;
            case 'relation':
                event(new InsertRelationIndex($data, $this->table[0]));
                break;
            case 'religion':
                event(new InsertReligionIndex($data, $this->table[0]));
                break;
            case 'role':
                event(new InsertRoleIndex($data, $this->table[0]));
                break;
            case 'room':
                event(new InsertRoomIndex($data, $this->table[0]));
                break;
            case 'room_group':
                event(new InsertRoomGroupIndex($data, $this->table[0]));
                break;
            case 'room_type':
                event(new InsertRoomTypeIndex($data, $this->table[0]));
                break;
            case 'sale_profit_cfg':
                event(new InsertSaleProfitCfgIndex($data, $this->table[0]));
                break;
            case 'service_condition':
                event(new InsertServiceConditionIndex($data, $this->table[0]));
                break;
            case 'service':
                event(new InsertServiceIndex($data, $this->table[0]));
                break;
            case 'service_follow':
                event(new InsertServiceFollowIndex($data, $this->table[0]));
                break;
            case 'service_group':
                event(new InsertServiceGroupIndex($data, $this->table[0]));
                break;
            case 'service_machine':
                event(new InsertServiceMachineIndex($data, $this->table[0]));
                break;
            case 'service_paty':
                event(new InsertServicePatyIndex($data, $this->table[0]));
                break;
            case 'service_req_type':
                event(new InsertServiceReqTypeIndex($data, $this->table[0]));
                break;
            case 'service_req_stt':
                event(new InsertServiceReqSttIndex($data, $this->table[0]));
                break;
            case 'service_room':
                event(new InsertServiceRoomIndex($data, $this->table[0]));
                break;
            case 'service_type':
                event(new InsertServiceTypeIndex($data, $this->table[0]));
                break;
            case 'service_unit':
                event(new InsertServiceUnitIndex($data, $this->table[0]));
                break;
            case 'serv_segr':
                event(new InsertServSegrIndex($data, $this->table[0]));
                break;
            case 'speciality':
                event(new InsertSpecialityIndex($data, $this->table[0]));
                break;
            case 'storage_condition':
                event(new InsertStorageConditionIndex($data, $this->table[0]));
                break;
            case 'suim_index':
                event(new InsertSuimIndexIndex($data, $this->table[0]));
                break;
            case 'suim_index_unit':
                event(new InsertSuimIndexUnitIndex($data, $this->table[0]));
                break;
            case 'supplier':
                event(new InsertSupplierIndex($data, $this->table[0]));
                break;
            case 'test_index':
                event(new InsertTestIndexIndex($data, $this->table[0]));
                break;
            case 'test_index_group':
                event(new InsertTestIndexGroupIndex($data, $this->table[0]));
                break;
            case 'test_index_unit':
                event(new InsertTestIndexUnitIndex($data, $this->table[0]));
                break;
            case 'test_sample_type':
                event(new InsertTestSampleTypeIndex($data, $this->table[0]));
                break;
            case 'test_type':
                event(new InsertTestTypeIndex($data, $this->table[0]));
                break;
            case 'tran_pati_tech':
                event(new InsertTranPatiTechIndex($data, $this->table[0]));
                break;
            case 'transaction_type':
                event(new InsertTransactionTypeIndex($data, $this->table[0]));
                break;
            case 'treatment_end_type':
                event(new InsertTreatmentEndTypeIndex($data, $this->table[0]));
                break;
            case 'treatment_type':
                event(new InsertTreatmentTypeIndex($data, $this->table[0]));
                break;
            case 'unlimit_reason':
                event(new InsertUnlimitReasonIndex($data, $this->table[0]));
                break;
            case 'vaccine_type':
                event(new InsertVaccineTypeIndex($data, $this->table[0]));
                break;
            case 'work_place':
                event(new InsertWorkPlaceIndex($data, $this->table[0]));
                break;
            case 'emr_cover_type':
                event(new InsertEmrCoverTypeIndex($data, $this->table[0]));
                break;
            case 'emr_form':
                event(new InsertEmrFormIndex($data, $this->table[0]));
                break;
            case 'death_cause':
                event(new InsertDeathCauseIndex($data, $this->table[0]));
                break;
            case 'treatment_result':
                event(new InsertTreatmentResultIndex($data, $this->table[0]));
                break;
            case 'tran_pati_form':
                event(new InsertTranPatiFormIndex($data, $this->table[0]));
                break;
            case 'document_type':
                event(new InsertDocumentTypeIndex($data, $this->table[0]));
                break;
            /// No Cache
            case 'tracking':
                event(new InsertTrackingIndex($data, $this->table[0]));
                break;
            case 'service_req_l_view':
                event(new InsertServiceReqLViewIndex($data, $this->table[0]));
                break;
            case 'test_service_req_list_v_view':
                event(new InsertTestServiceReqListVViewIndex($data, $this->table[0]));
                break;
            case 'debate':
                event(new InsertDebateIndex($data, $this->table[0]));
                break;
            case 'debate_v_view':
                event(new InsertDebateVViewIndex($data, $this->table[0]));
                break;
            case 'user_room_v_view':
                event(new InsertUserRoomVViewIndex($data, $this->table[0]));
                break;
            case 'debate_user':
                event(new InsertDebateUserIndex($data, $this->table[0]));
                break;
            case 'debate_ekip_user':
                event(new InsertDebateEkipUserIndex($data, $this->table[0]));
                break;
            case 'sere_serv':
                event(new InsertSereServIndex($data, $this->table[0]));
                break;
            case 'sere_serv_v_view_4':
                event(new InsertSereServVView4Index($data, $this->table[0]));
                break;
            case 'patient_type_alter_v_view':
                event(new InsertPatientTypeAlterVViewIndex($data, $this->table[0]));
                break;
            case 'treatment_l_view':
                event(new InsertTreatmentLViewIndex($data, $this->table[0]));
                break;
            case 'treatment_fee_view':
                event(new InsertTreatmentFeeViewIndex($data, $this->table[0]));
                break;
            case 'treatment_bed_room_l_view':
                event(new InsertTreatmentBedRoomLViewIndex($data, $this->table[0]));
                break;
            case 'dhst':
                event(new InsertDhstIndex($data, $this->table[0]));
                break;
            case 'sere_serv_ext':
                event(new InsertSereServExtIndex($data, $this->table[0]));
                break;
            case 'sere_serv_tein':
                event(new InsertSereServTeinIndex($data, $this->table[0]));
                break;
            case 'sere_serv_tein_v_view':
                event(new InsertSereServTeinVViewIndex($data, $this->table[0]));
                break;
            case 'sere_serv_bill':
                event(new InsertSereServBillIndex($data, $this->table[0]));
                break;
            case 'sere_serv_deposit_v_view':
                event(new InsertSereServDepositVViewIndex($data, $this->table[0]));
                break;
            case 'sese_depo_repay_v_view':
                event(new InsertSeseDepoRepayVViewIndex($data, $this->table[0]));
                break;
            case 'account_book_v_view':
                event(new InsertAccountBookVViewIndex($data, $this->table[0]));
                break;
            case 'room_v_view':
                event(new InsertRoomVViewIndex($data, $this->table[0]));
                break;
            case 'icd_list_v_view':
                event(new InsertIcdListVViewIndex($data, $this->table[0]));
                break;
            default:
                break;
        }
    }
}
