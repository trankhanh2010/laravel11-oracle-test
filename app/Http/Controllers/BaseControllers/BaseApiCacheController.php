<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Http\Resources\Elastic\ElasticResource;
use App\Models\ACS\Module;
use App\Models\ACS\Role;
use App\Models\HIS\ActiveIngredient;
use App\Models\HIS\Bed;
use App\Models\HIS\BedRoom;
use App\Models\HIS\Branch;
use App\Models\HIS\Debate;
use App\Models\HIS\Department;
use App\Models\HIS\Employee;
use App\Models\HIS\ExecuteRole;
use App\Models\HIS\ExecuteRoom;
use App\Models\HIS\Machine;
use App\Models\HIS\MaterialType;
use App\Models\HIS\MedicineType;
use App\Models\HIS\MediStock;
use App\Models\HIS\Package;
use App\Models\HIS\PatientType;
use App\Models\HIS\Room;
use App\Models\HIS\RoomType;
use App\Models\HIS\SereServ;
use App\Models\HIS\Service;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\ServiceReqStt;
use App\Models\HIS\ServiceReqType;
use Illuminate\Http\Request;
use App\Models\HIS\ServiceType;
use App\Models\HIS\TestIndex;
use App\Models\HIS\Transaction;
use App\Models\HIS\TransactionType;
use App\Models\HIS\Treatment;
use App\Models\HIS\TreatmentType;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BaseApiCacheController extends Controller
{
    protected $errors = [];
    protected $data = [];
    protected $lastId;
    protected $lastIdName = 'LastId';
    protected $cursorPaginate;
    protected $cursorPaginateName = 'CursorPaginate';
    protected $time;
    protected $date;
    protected $dateName = 'Date';
    protected $columnsTime;
    protected $arrLimit;
    protected $start;
    protected $startName = 'Start';
    protected $limit;
    protected $limitName = 'Limit';
    protected $orderBy;
    protected $orderByName = 'OrderBy';
    protected $orderByString;
    protected $orderByRequest;
    protected $orderByElastic;
    protected $orderByJoin;
    protected $onlyActive;
    protected $onlyActiveName = 'OnlyActive';
    protected $id;
    protected $idName = 'Id';
    protected $testIndexIds;
    protected $testIndexIdsName = 'TestIndexIds';
    protected $tdlTreatmentId;
    protected $tdlTreatmentIdName = 'TdlTreatmentId';
    protected $serviceTypeIds;
    protected $serviceTypeIdsName = 'ServiceTypeIds';
    protected $patientTypeIds;
    protected $patientTypeIdsName = 'PatientTypeIds';
    protected $serviceIds;
    protected $serviceIdsName = 'ServiceIds';
    protected $patientCode;
    protected $patientCodeName = 'PatientCode';
    protected $serviceIdsString;
    protected $machineIds;
    protected $machineIdsName = 'MachineIds';
    protected $machineIdsString;
    protected $roomIds;
    protected $roomIdsName = 'RoomIds';
    protected $serviceFollowIds;
    protected $serviceFollowIdsName = 'ServiceFollowIds';
    protected $bedIds;
    protected $bedIdsName = 'BedIds';
    protected $serviceId;
    protected $serviceIdName = 'ServiceId';
    protected $machineId;
    protected $machineIdName = 'MachineId';
    protected $htu;
    protected $htuName = 'htu';
    protected $packageId;
    protected $packageIdName = 'PackageId';
    protected $departmentId;
    protected $departmentIdName = 'DepartmentId';
    protected $keyword;
    protected $keywordName = 'Keyword';
    protected $getAll;
    protected $getAllName = 'GetAll';
    protected $countName = 'Count';
    protected $perPage;
    protected $page;
    protected $paramRequest;
    protected $isActive;
    protected $isActiveName = 'IsActive';
    protected $isDelete;
    protected $isDeleteName = 'IsDelete';
    protected $effective;
    protected $effectiveName = 'Effective';
    protected $roomTypeId;
    protected $roomTypeIdName = 'RoomTypeId';
    protected $debateId;
    protected $debateIdName = 'DebateId';
    protected $isAddition;
    protected $isAdditionName = 'IsAddition';
    protected $serviceTypeId;
    protected $serviceTypeIdName = 'ServiceTypeId';
    protected $loginname;
    protected $loginnameName = 'Loginname';
    protected $executeRoleId;
    protected $executeRoleIdName = 'ExecuteRoleId';
    protected $moduleId;
    protected $moduleIdName = 'ModuleId';
    protected $roleId;
    protected $roleIdName = 'RoleId';
    protected $billId;
    protected $billIdName = 'BillId';
    protected $billCode;
    protected $billCodeName = 'BillCode';
    protected $mediStockId;
    protected $mediStockIdName = 'MediStockId';
    protected $fromTime;
    protected $fromTimeName = 'FromTime';
    protected $toTime;
    protected $toTimeName = 'ToTime';
    protected $logTimeTo;
    protected $logTimeToName = 'LogTimeTo';
    protected $executeDepartmentCode;
    protected $executeDepartmentCodeName = 'ExecuteDepartmentCode';
    protected $isSpecimen;
    protected $isSpecimenName = 'IsSpecimen';
    protected $isNoExcute;
    protected $isNoExcuteName = 'IsNoExcute';
    protected $patientTypeId;
    protected $serviceReqCode;
    protected $serviceReqCodeName = 'ServiceReqCode';
    protected $patientId;
    protected $patientIdName = 'PatientId';
    protected $patientTypeIdName = 'PatientTypeId';
    protected $medicineTypeId;
    protected $medicineTypeIdName = 'MedicineTypeId';
    protected $materialTypeId;
    protected $materialTypeIdName = 'MaterialTypeId';
    protected $transactionTypeIds;
    protected $transactionTypeIdsName = 'TransactionTypeIds';
    protected $roomId;
    protected $roomIdName = 'RoomId';
    protected $isForDeposit;
    protected $isForDepositName = 'IsForDeposit';
    protected $isForRepay;
    protected $isForRepayName = 'IsForRepay';
    protected $isForBill;   
    protected $isForBillName = 'isForBill';   
    protected $serviceReqIds;
    protected $serviceReqIdsName = 'ServiceReqIds';
    protected $atc;
    protected $atcName = 'atc';
    protected $tdlTreatmentTypeIds;
    protected $tdlTreatmentTypeIdsName = 'TdlTreatmentTypeIds';
    protected $branchId;
    protected $branchIdName = 'BranchId';
    protected $inDateFrom;
    protected $inDateFromName = 'InDateFrom';
    protected $inDateTo;
    protected $inDateToName = 'InDateTo';
    protected $isApproveStore;
    protected $isApproveStoreName = 'IsApproveStore';
    protected $executeRoomId;
    protected $executeRoomIdName = 'ExecuteRoomId';
    protected $patientTypeAllowId;
    protected $patientTypeAllowIdName = 'PatientTypeAllowId';
    protected $activeIngredientId;
    protected $activeIngredientIdName = 'ActiveIngredientId';
    protected $testServiceTypeId;
    protected $testServiceTypeIdName = 'TestServiceTypeId';
    protected $treatmentId;
    protected $treatmentIdName = 'TreatmentId';
    protected $treatmentCode;
    protected $treatmentCodeName = 'TreatmentCode';
    protected $departmentIds;
    protected $departmentIdsName = 'DepartmentIds';
    protected $serviceReqSttIds;
    protected $serviceReqSttIdsName = 'ServiceReqSttIds';
    protected $isNotKskRequriedAprovalOrIsKskApprove;
    protected $isNotKskRequriedAprovalOrIsKskApproveName = 'IsNotKskRequriedAprovalOrIsKskApprove';
    protected $status;
    protected $statusName = 'Status';
    protected $patientPhone;
    protected $patientPhoneName = 'PatientPhone';
    protected $hasExecute;
    protected $hasExecuteName = 'HasExecute';
    protected $intructionTimeTo;
    protected $intructionTimeToName = 'IntructionTimeTo';
    protected $intructionTimeFrom;
    protected $intructionTimeFromName = 'IntructionTimeFrom';
    protected $tdlPatientTypeIds;
    protected $tdlPatientTypeIdsName = 'TdlPatientTypeIds';
    protected $notInServiceReqTypeIds;
    protected $notInServiceReqTypeIdsName = 'NotInServiceReqTypeIds';
    protected $serviceReqId;
    protected $serviceReqIdName = 'ServiceReqId';
    protected $sereServIds;
    protected $sereServIdsName = 'SereServIds';
    protected $bedRoomIds;
    protected $bedRoomIdsName = 'BedRoomIds';
    protected $addTimeTo;
    protected $addTimeToName = 'AddTimeTo';
    protected $addTimeFrom;
    protected $addTimeFromName = 'AddTimeFrom';
    protected $isInRoom;
    protected $isInRoomName = 'IsInRoom';
    protected $patientTypeIdsString;
    protected $serviceTypeIdsString;
    protected $relations;
    protected $relationsName = 'realtions';
    protected $transactionCode;
    protected $transactionCodeName = 'TransactionCode';

    // Khai báo các biến mặc định model
    protected $appCreator = "MOS_v2";
    protected $appModifier = "MOS_v2";
    // Khai báo các biến model
    protected $department;
    protected $departmentName = "department";
    protected $bedRoom;
    protected $bedRoomName = "bed_room";
    protected $storageCondition;
    protected $storageConditionName = 'storage_condition';
    protected $executeRoom;
    protected $executeRoomName = "execute_room";
    protected $room;
    protected $roomName = "room";
    protected $speciality;
    protected $specialityName = "speciality";
    protected $treatmentType;
    protected $treatmentTypeName = "treatment_type";
    protected $mediOrg;
    protected $mediOrgName = "medi_org";
    protected $testIndexGroup;
    protected $testIndexGroupName = 'test_index_group';
    protected $branch;
    protected $branchName = "branch";
    protected $treatmentBedRoomLView;
    protected $treatmentBedRoomLViewName = 'treatment_bed_room_l_view';
    protected $district;
    protected $districtName = "district";
    protected $mediStock;
    protected $mediStockName = "medi_stock";
    protected $receptionRoom;
    protected $receptionRoomName = "reception_room";
    protected $suimIndexUnit;
    protected $suimIndexUnitName = "suim_index_unit";
    protected $area;
    protected $areaName = "area";
    protected $debateVView;
    protected $debateVViewName = 'debate_v_view';
    protected $materialTypeMap;
    protected $materialTypeMapName = 'material_type_map';
    protected $refectory;
    protected $refectoryName = "refectory";
    protected $executeGroup;
    protected $executeGroupName = "execute_group";
    protected $cashierRoom;
    protected $cashierRoomName = "cashier_room";
    protected $national;
    protected $nationalName = "national";
    protected $province;
    protected $provinceName = "province";
    protected $dataStore;
    protected $dataStoreName = "data_store";
    protected $executeRole;
    protected $executeRoleName = "execute_role";
    protected $commune;
    protected $communeName = "commune";
    protected $service;
    protected $serviceName = "service";
    protected $sereServVView4;
    protected $sereServVView4Name = 'sere_serv_v_view_4';
    protected $servicePaty;
    protected $servicePatyName = 'service_paty';
    protected $serviceMachine;
    protected $serviceMachineName = 'service_machine';
    protected $machine;
    protected $machineName = 'machine';
    protected $serviceRoom;
    protected $serviceRoomName = 'service_room';
    protected $serviceFollow;
    protected $serviceFollowName = 'service_follow';
    protected $bed;
    protected $bedName = 'bed';
    protected $bedBsty;
    protected $bedBstyName = 'bed_bsty';
    protected $bedType;
    protected $bedTypeName = 'bed_type';
    protected $servSegr;
    protected $servSegrName = 'serv_segr';
    protected $serviceGroup;
    protected $serviceGroupName = 'service_group';
    protected $employee;
    protected $employeeName = 'employee';
    protected $executeRoleUser;
    protected $executeRoleUserName = 'execute_role_user';
    protected $role;
    protected $roleName = 'role';
    protected $module;
    protected $moduleName = 'module';
    protected $ethnic;
    protected $ethnicName = 'ethnic';
    protected $patientType;
    protected $patientTypeName = 'patient_type';
    protected $priorityType;
    protected $priorityTypeName = 'priority_type';
    protected $career;
    protected $careerName = 'career';
    protected $patientClassify;
    protected $patientClassifyName = 'patient_classify';
    protected $userRoomVView;
    protected $userRoomVViewName = 'user_room_v_view';
    protected $religion;
    protected $religionName = 'religion';
    protected $impSource;
    protected $impSourceName = 'imp_source';
    protected $bid;
    protected $bidName = 'bid';
    protected $serviceUnit;
    protected $serviceUnitName = 'service_unit';
    protected $serviceType;
    protected $serviceTypeName = 'service_type';
    protected $rationGroup;
    protected $rationGroupName = 'ration_group';
    protected $serviceReqType;
    protected $serviceReqTypeName = 'service_req_type';
    protected $rationTime;
    protected $rationTimeName = 'ration_time';
    protected $relationList;
    protected $relationListName = 'relation_list';
    protected $relation;
    protected $relationName = 'relation';
    protected $moduleRole;
    protected $moduleRoleName = 'module_role';
    protected $medicalContract;
    protected $medicalContractName = 'medical_contract';
    protected $mestPatientType;
    protected $mestPatientTypeName = 'mest_patient_type';
    protected $patientTypeAlterVView;
    protected $patientTypeAlterVViewName = 'patient_type_alter_v_view';
    protected $mediStockMetyList;
    protected $mediStockMetyListName = 'medi_stock_mety';
    protected $user;
    protected $userName = 'user';
    protected $medicineType;
    protected $medicineTypeName = 'medicine_type';
    protected $mediStockMatyList;
    protected $mediStockMatyListName = 'medi_stock_maty';
    protected $materialType;
    protected $materialTypeName = 'material_type';
    protected $treatmentFeeView;
    protected $treatmentFeeViewName = 'treatment_fee_view';
    protected $mestExportRoom;
    protected $mestExportRoomName = 'mest_export_room';
    protected $exroRoom;
    protected $exroRoomName = 'exro_room';
    protected $patientTypeRoom;
    protected $patientTypeRoomName = 'patient_type_room';
    protected $saleProfitCfg;
    protected $saleProfitCfgName = 'sale_profit_cfg';
    protected $patientTypeAllow;
    protected $patientTypeAllowName = 'patient_type_allow';
    protected $position;
    protected $positionName = 'position';
    protected $workPlace;
    protected $workPlaceName = 'work_place';
    protected $bornPosition;
    protected $bornPositionName = 'born_position';
    protected $patientCase;
    protected $patientCaseName = 'patient_case';
    protected $bhytWhitelist;
    protected $bhytWhitelistName = 'bhyt_whitelist';
    protected $heinServiceType;
    protected $heinServiceTypeName = 'hein_service_type';
    protected $bhytParam;
    protected $bhytParamName = 'bhyt_param';
    protected $serviceReqLView;
    protected $serviceReqLViewName = 'service_req_l_view';
    protected $groupType;
    protected $groupTypeName = 'group_type';
    protected $bhytBlacklist;
    protected $bhytBlacklistName = 'bhyt_blacklist';
    protected $medicinePaty;
    protected $medicinePatyName = 'medicine_paty';
    protected $accidentBodyPart;
    protected $accidentBodyPartName = 'accident_body_part';
    protected $memaGroup;
    protected $memaGroupName = 'mema_group';
    protected $preparationsBlood;
    protected $preparationsBloodName = 'preparations_blood';
    protected $contraindication;
    protected $contraindicationName = 'contraindication';
    protected $dosageForm;
    protected $dosageFormName = 'dosage_form';
    protected $accidentLocation;
    protected $accidentLocationName = 'accident_location';
    protected $licenseClass;
    protected $licenseClassName = 'license_class';
    protected $manufacturer;
    protected $manufacturerName = 'manufacturer';
    protected $icd;
    protected $icdName = 'icd';
    protected $mediRecordType;
    protected $mediRecordTypeName = 'medi_record_type';
    protected $fileType;
    protected $fileTypeName = 'file_type';
    protected $treatmentEndType;
    protected $treatmentEndTypeName = 'treatment_end_type';
    protected $tranPatiTech;
    protected $tranPatiTechName = 'tran_pati_tech';
    protected $debateReason;
    protected $debateReasonName = 'debate_reason';
    protected $cancelReason;
    protected $cancelReasonName = 'cancel_reason';
    protected $interactionReason;
    protected $interactionReasonName = 'interaction_reason';
    protected $unlimitReason;
    protected $unlimitReasonName = 'unlimit_reason';
    protected $hospitalizeReason;
    protected $hospitalizeReasonName = 'hospitalize_reason';
    protected $expMestReason;
    protected $expMestReasonName = 'exp_mest_reason';
    protected $careerTitle;
    protected $careerTitleName = 'career_title';
    protected $accidentHurtType;
    protected $accidentHurtTypeName = 'accident_hurt_type';
    protected $supplier;
    protected $supplierName = 'supplier';
    protected $vaccineType;
    protected $vaccineTypeName = 'vaccine_type';
    protected $processingMethod;
    protected $processingMethodName = 'processing_method';
    protected $deathWithin;
    protected $deathWithinName = 'death_within';
    protected $locationStore;
    protected $locationStoreName = 'location_store';
    protected $accidentCare;
    protected $accidentCareName = 'accident_care';
    protected $ptttTable;
    protected $ptttTableName = 'pttt_table';
    protected $ptttGroup;
    protected $ptttGroupName = 'pttt_group';
    protected $packingType;
    protected $packingTypeName = 'packing_type';
    protected $ptttMethod;
    protected $ptttMethodName = 'pttt_method';
    protected $emotionlessMethod;
    protected $emotionlessMethodName = 'emotionless_method';
    protected $ptttCatastrophe;
    protected $ptttCatastropheName = 'pttt_catastrophe';
    protected $ptttCondition;
    protected $ptttConditionName = 'pttt_condition';
    protected $awareness;
    protected $awarenessName = 'awareness';
    protected $medicineLine;
    protected $medicineLineName = 'medicine_line';
    protected $bloodVolume;
    protected $bloodVolumeName = 'blood_volume';
    protected $medicineUseForm;
    protected $medicineUseFormName = 'medicine_use_form';
    protected $bidType;
    protected $bidTypeName = 'bid_type';
    protected $medicineTypeAcin;
    protected $medicineTypeAcinName = 'medicine_type_acin';
    protected $activeIngredient;
    protected $activeIngredientName = 'active_ingredient';
    protected $atcGroup;
    protected $atcGroupName = 'atc_group';
    protected $bloodGroup;
    protected $bloodGroupName = 'blood_group';
    protected $medicineGroup;
    protected $medicineGroupName = 'medicine_group';
    protected $testIndex;
    protected $testIndexName = 'test_index';
    protected $testIndexUnit;
    protected $testIndexUnitName = 'test_index_unit';
    protected $testSampleType;
    protected $testSampleTypeName = 'test_sample_type';
    protected $userRoom;
    protected $userRoomName = 'user_room';
    protected $debate;
    protected $debateName = 'debate';
    protected $debateUser;
    protected $debateUserName = 'debate_user';
    protected $debateEkipUser;
    protected $debateEkipUserName = 'debate_ekip_user';
    protected $debateType;
    protected $debateTypeName = 'debate_type';
    protected $treatment;
    protected $treatmentName = 'treatment';
    protected $tracking;
    protected $trackingName = 'tracking';
    protected $debateInviteUser;
    protected $debateInviteUserName = 'debate_invite_user';
    protected $serviceReq;
    protected $serviceReqName = 'service_req';
    protected $expMest;
    protected $expMestName = 'exp_mest';
    protected $expMestMedicine;
    protected $expMestMedicineName = 'exp_mest_medicine';
    protected $expMestMaterial;
    protected $expMestMaterialName = 'exp_mest_material';
    protected $testServiceReqListVView;
    protected $testServiceReqListVViewName = 'test_service_req_list_v_view';
    protected $testServiceReqListVView2;
    protected $testServiceReqListVView2Name = 'test_service_req_list_v_view_2';
    protected $impMest;
    protected $impMestName = 'imp_mest';
    protected $treatmentLView;
    protected $treatmentLViewName = 'treatment_l_view';
    protected $sereServExt;
    protected $sereServExtName = 'sere_serv_ext';
    protected $sereServ;
    protected $sereServName = 'sere_serv';
    protected $dhst;
    protected $dhstName = 'dhst';
    protected $care;
    protected $careName = 'care';
    protected $patientTypeAlter;
    protected $patientTypeAlterName = 'patient_type_alter';
    protected $treatmentBedRoom;
    protected $treatmentBedRoomName = 'treatment_bed_room';
    protected $sereServTein;
    protected $sereServTeinName = 'sere_serv_tein';
    protected $group;
    protected $groupName = 'group';
    protected $roomType;
    protected $roomTypeName = 'room_type';
    protected $testType;
    protected $testTypeName = 'test_type';
    protected $roomGroup;
    protected $roomGroupName = 'room_group';
    protected $moduleGroup;
    protected $moduleGroupName = 'module_group';
    protected $otherPaySource;
    protected $otherPaySourceName = 'other_pay_source';
    protected $militaryRank;
    protected $militaryRankName = 'military_rank';
    protected $icdCm;
    protected $icdCmName = 'icd_cm';
    protected $diimType;
    protected $diimTypeName = 'diim_type';
    protected $fuexType;
    protected $fuexTypeName = 'fuex_type';
    protected $filmSize;
    protected $filmSizeName = 'film_size';
    protected $gender;
    protected $genderName = 'gender';
    protected $bodyPart;
    protected $bodyPartName = 'body_part';
    protected $exeServiceModule;
    protected $exeServiceModuleName = 'exe_service_module';
    protected $suimIndex;
    protected $suimIndexName = 'suim_index';
    protected $package;
    protected $packageName = 'package';
    protected $serviceCondition;
    protected $serviceConditionName = 'service_condition';
    protected $token;
    protected $tokenName = 'token';
    protected $mediStockMety;
    protected $mediStockMetyName = 'medi_stock_mety';
    protected $mediStockMaty;
    protected $mediStockMatyName = 'medi_stock_maty';
    protected $mestRoom;
    protected $mestRoomName = 'mest_room';
    protected $icdGroup;
    protected $icdGroupName = 'icd_group';
    protected $ageType;
    protected $ageTypeName = 'age_type';
    protected $medicine;
    protected $medicineName = 'medicine';
    protected $payForm;
    protected $payFormName = 'pay_form';
    protected $sereServTeinVView;
    protected $sereServTeinVViewName = 'sere_serv_tein_v_view';
    protected $sereServBill;
    protected $sereServBillName = 'sere_serv_bill';
    protected $transactionTTDetailVView;
    protected $transactionTTDetailVViewName = 'TransactionTTDetailVView';
    protected $sereServDepositVView;
    protected $sereServDepositVViewName = 'sere_serv_deposit_v_view';
    protected $seseDepoRepayVView;
    protected $seseDepoRepayVViewName = 'sese_depo_repay_v_view';
    protected $accountBookVView;
    protected $accountBookVViewName = 'account_book_v_view';
    protected $transactionType;
    protected $transactionTypeName = 'transaction_type';
    protected $transaction;
    protected $transactionName = 'transaction';
    protected $treatmentFeeDetailVView;
    protected $treatmentFeeDetailVViewName = 'treatment_fee_detail_v_view';
    protected $trackingData;
    protected $trackingDataName = 'tracking_data';
    protected $treatmentWithPatientTypeInfoSdo;
    protected $treatmentWithPatientTypeInfoSdoName = 'treatment_with_patient_type_info_sdo';
    protected $testServiceTypeListVView;
    protected $testServiceTypeListVViewName = 'test_service_type_list_v_view';
    protected $treatmentFeeListVView;
    protected $treatmentFeeListVViewName = 'treatment_fee_list_v_view';
    protected $transactionListVView;
    protected $transactionListVViewName = 'transaction_list_v_view';
    // Thanh toán
    protected $paymentMethod; // Hình thức thanh toán MoMo VNPay
    protected $paymentMethodName = 'PaymentMethod';
    protected $transactionTypeCode; // Tên loại giao dịch
    protected $transactionTypeCodeName = 'PaymentMethod';
    protected $paymentOption; // Phương thức thanh toán QR Code Thẻ ngân hàng
    protected $paymentOptionName = 'PaymentOption';
    // Khai báo các biến cho Elastic
    protected $elasticSearchService;
    protected $client;
    protected $elastic;
    protected $elasticName = 'Elastic';
    protected $cache;
    protected $cacheName = 'Cache';
    protected $elasticIsActive;
    protected $elasticSearchTypeArr = ['match', 'term', 'wildcard', 'query_string', 'multi_match', 'match_phrase', 'prefix', 'bool', 'custom'];
    protected $elasticSearchTypeMustShouldMustNot = ['match', 'term', 'wildcard', 'match_phrase', 'prefix', 'query_string', 'range'];
    protected $elasticRangeArr = ['gt', 'gte', 'lt', 'lte', 'format'];
    protected $elasticSearchType;
    protected $elasticSearchTypeName = 'ElasticSearchType';
    protected $elasticCustom;
    protected $elasticField;
    protected $elasticFieldName = 'ElasticField';
    protected $elasticFields;
    protected $elasticFieldsName = 'ElasticFields';
    protected $elasticFieldRequest;
    protected $elasticFieldsRequest;
    protected $elasticOperatorArr = ['AND', 'OR'];
    protected $elasticOperator;
    protected $elasticOperatorName = 'ElasticOperator';
    protected $elasticShould;
    protected $elasticShouldName = 'ElasticShould';
    protected $elasticMust;
    protected $elasticMustName = 'ElasticMust';
    protected $elasticMustNot;
    protected $elasticMustNotName = 'ElasticMustNot';
    protected $elasticFilter;
    protected $elasticFilterName = 'ElasticFilter';
    // Thông báo lỗi
    protected $messFormat;
    protected $messOrderByName;
    protected $messRecordId;
    protected $messDecodeParam;
    protected $line;
    protected $lineName = 'Line';

    // Function kiểm tra lỗi và lấy thông báo lỗi
    protected function hasErrors()
    {
        return !empty($this->errors);
    }

    protected function getErrors()
    {
        return $this->errors;
    }

    protected function checkParam()
    {
        if ($this->hasErrors()) {
            return return400($this->getErrors());
        }
        return null;
    }
    protected function checkId($id, $model, $name)
    {
        if ($this->isActive !== null) {
            $data = Cache::remember($name . '_check_id_' . $id , $this->time, function () use ($id, $model) {
                return $model->where('id', $id)->exists();
            });
        } else {
            $data = Cache::remember($name . '_check_id_' . $id, $this->time, function () use ($id, $model) {
                return $model->where('id', $id)->exists();
            });
        }
        if (!$data) {
            return returnNotRecord($id);
        }
        return null;
    }
    protected function validateAndCheckId($id, $model, $modelName)
{
    if (!is_numeric($id)) {
        return returnIdError($id);
    }

    $checkId = $this->checkId($id, $model, $modelName);
    if ($checkId) {
        return $checkId;
    }

    return null; // Trả về null nếu không có lỗi
}

    protected function getColumnsTable($table, $isView = false)
    {
        $parts = explode('_', $table->getTable());
        if($isView){
            $conn = strtolower($parts[1]);
        }else{
            $conn = strtolower($parts[0]);
        }
        $columnsTable = Cache::remember('columns_' . $table->getTable(), $this->columnsTime, function () use ($table, $conn) {
            return  Schema::connection('oracle_' . $conn)->getColumnListing($table->getTable()) ?? [];
        });
        return $columnsTable;
    }
    protected function checkOrderBy($orderBy, $columns, $orderByJoin)
    {
        foreach ($orderBy as $key => $item) {
            if (!in_array($key, $orderByJoin)) {
                if ((!in_array($key, $columns))) {
                    $this->errors[snakeToCamel($key)] = $this->messOrderByName;
                    unset($this->orderByRequest[camelCaseFromUnderscore($key)]);
                    unset($this->orderBy[$key]);
                }
            }
        }
        return $orderBy;
    }

    public function __construct(Request $request)
    {
        // Khai báo các biến
        try{
            $this->client = app('Elasticsearch');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['connection'], $e);
        }
        // Thời gian tồn tại của cache
        $this->time = now()->addMinutes(10080);
        $this->columnsTime = now()->addMinutes(20000);

        // Thông báo lỗi 
        $this->messFormat = config('keywords')['error']['format'];
        $this->messOrderByName = config('keywords')['error']['order_by_name'];
        $this->messRecordId = config('keywords')['error']['record_id'];
        $this->messDecodeParam = config('keywords')['error']['decode_param'];


        // Param json gửi từ client
        if ($request->input('param') !== null) {
            // Thay thế dấu + và / nếu bị thay đổi thành khoảng trắng hoặc các ký tự khác
            $encodedParam  = str_replace([' ', '+', '/'], ['+', '+', '/'], $request->input('param'));
            $this->paramRequest = json_decode(base64_decode($encodedParam ), true) ?? null;
            if ($this->paramRequest === null) {
                $this->errors['param'] = $this->messDecodeParam;
            }
        }

        // Gán và kiểm tra các tham số được gửi lên
        $this->perPage = $request->query('perPage', 10);
        $this->page = $request->query('page', 1);
        $this->start = $this->paramRequest['CommonParam']['Start'] ?? intval($request->start) ?? 0;
        $this->limit = $this->paramRequest['CommonParam']['Limit'] ?? intval($request->limit) ?? 100;
        if ($this->limit <= 0) {
            $this->limit = 10;
        }
        $this->arrLimit = [10, 20, 50, 100, 200, 500, 1000, 2000, 4000];
        if (($this->limit < 0) || (!in_array($this->limit, $this->arrLimit))) {
            $this->errors[$this->limitName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->arrLimit);
            $this->limit = 10;
        }
        if ($this->start != null) {
            if ((!is_numeric($this->start)) || (!is_int($this->start)) || ($this->start < 0)) {
                $this->errors[$this->startName] = $this->messFormat;
                $this->start = 0;
            }
        }
        $this->lastId = $this->paramRequest['CommonParam']['LastId'] ?? 0;
        if($this->lastId !== null){
            if (!is_int($this->lastId)) {
                $this->errors[$this->lastIdName] = $this->messFormat;
            }
        }
        $this->cursorPaginate = $this->paramRequest['CommonParam']['CursorPaginate'] ?? false;

        // if (($this->limit != null) || ($this->start != null)) {
        //     if ((!is_numeric($this->limit)) || (!is_int($this->limit)) || ($this->limit > 4000) || ($this->limit <= 0)) {
        //         $this->errors[$this->limit_name] = $this->mess_format;
        //         $this->limit = 100;
        //     }
        // }
        $this->keyword = $this->paramRequest['ApiData']['KeyWord'] ?? $request->keyword ?? "";
        if ($this->keyword !== null) {
            if (!is_string($this->keyword)) {
                $this->errors[$this->keywordName] = $this->messFormat;
                $this->keyword = null;
            }
        }
        $this->line = $this->paramRequest['ApiData']['Line'] ?? null;
        if($this->line !== null){
            if (!is_int($this->line)) {
                $this->errors[$this->lineName] = $this->messFormat;
            }
        }
        $this->date = $this->paramRequest['ApiData']['Date'] ?? null;
        if($this->date !== null){
            if (!is_string($this->date)) {
                $this->errors[$this->dateName] = $this->messFormat;
            }
        }
        
        $this->getAll = $this->paramRequest['CommonParam']['GetAll'] ?? false;
        if (!is_bool($this->getAll)) {
            $this->errors[$this->getAllName] = $this->messFormat;
            $this->getAll = false;
        }
        $this->orderBy = $this->paramRequest['ApiData']['OrderBy'] ?? null;
        $this->orderByRequest = $this->paramRequest['ApiData']['OrderBy'] ?? null;
        if ($this->orderBy != null) {
            $this->orderBy = convertArrayKeysToSnakeCase($this->orderBy);
            foreach ($this->orderBy as $key => $item) {
                if (!in_array($item, ['asc', 'desc'])) {
                    $this->errors[$this->orderByName] = $this->messFormat;
                }
            }
            $this->orderByString = arrayToCustomString($this->orderBy);
        }
        $this->orderByElastic = $this->orderBy;
        /// Thanh toán
        $this->paymentMethod = $this->paramRequest['ApiData']['PaymentMethod'] ?? null;
        if($this->paymentMethod !== null){
            if (!is_string($this->paymentMethod)) {
                $this->errors[$this->paymentMethodName] = $this->messFormat;
            }
        }
        $this->paymentOption = $this->paramRequest['ApiData']['PaymentOption'] ?? null;
        if($this->paymentOption !== null){
            if (!is_string($this->paymentOption)) {
                $this->errors[$this->paymentOptionName] = $this->messFormat;
            }
        }
        $this->transactionTypeCode = $this->paramRequest['ApiData']['TransactionTypeCode'] ?? null;
        if($this->transactionTypeCode !== null){
            if (!is_string($this->transactionTypeCode)) {
                $this->errors[$this->transactionTypeCodeName] = $this->messFormat;
            }
        }
        $this->isActive = $this->paramRequest['ApiData']['IsActive'] ?? null;
        if ($this->isActive !== null) {
            if (!in_array($this->isActive, [0, 1])) {
                $this->errors[$this->isActiveName] = $this->messFormat;
                $this->isActive = 1;
            }
        }

        $this->isDelete = $this->paramRequest['ApiData']['IsDelete'] ?? null;
        if ($this->isDelete !== null) {
            if (!in_array($this->isDelete, [0, 1])) {
                $this->errors[$this->isDeleteName] = $this->messFormat;
                $this->isDelete = 1;
            }
        }

        $this->status = $this->paramRequest['ApiData']['Status']?? null;
        if ($this->status !== null) {
            if (!is_string($this->status)) {
                $this->errors[$this->statusName] = $this->messFormat;
                $this->status = null;
            }
        }

        $this->transactionCode = $this->paramRequest['ApiData']['TransactionCode']?? null;
        if ($this->transactionCode !== null) {
            if (!is_string($this->transactionCode)) {
                $this->errors[$this->transactionCodeName] = $this->messFormat;
                $this->transactionCode = null;
            }
        }

        $this->patientPhone = $this->paramRequest['ApiData']['PatientPhone']?? null;
        if ($this->patientPhone !== null) {
            if (!is_string($this->patientPhone)) {
                $this->errors[$this->patientPhoneName] = $this->messFormat;
                $this->patientPhone = null;
            }
        }

        $this->serviceReqCode = $this->paramRequest['ApiData']['ServiceReqCode']?? null;
        if ($this->serviceReqCode !== null) {
            if (!is_string($this->serviceReqCode)) {
                $this->errors[$this->serviceReqCodeName] = $this->messFormat;
                $this->serviceReqCode = null;
            }
        }

        $this->billCode = $this->paramRequest['ApiData']['BillCode']?? null;
        if ($this->billCode !== null) {
            if (!is_string($this->billCode)) {
                $this->errors[$this->billCodeName] = $this->messFormat;
                $this->billCode = null;
            }
        }

        $this->onlyActive = $this->paramRequest['ApiData']['OnlyActive'] ?? false;
        if (!is_bool($this->onlyActive)) {
            $this->errors[$this->onlyActiveName] = $this->messFormat;
            $this->onlyActive = false;
        }

        // Elastic Search
        $this->elastic = $this->paramRequest['CommonParam']['Elastic'] ?? true;
        if (!is_bool($this->elastic)) {
            $this->errors[$this->elasticName] = $this->messFormat;
            $this->elastic = true;
        }

        $this->cache = $this->paramRequest['CommonParam']['Cache'] ?? false;
        if (!is_bool($this->cache)) {
            $this->errors[$this->cacheName] = $this->messFormat;
            $this->cache = false;
        }

        $this->elasticSearchType = ($this->paramRequest['ApiData']['ElasticSearchType'] ?? null);
        if ($this->elasticSearchType != null) {
            $this->elasticSearchType = strtolower($this->elasticSearchType);
            if (!in_array($this->elasticSearchType, $this->elasticSearchTypeArr)) {
                $this->errors[$this->elasticSearchTypeName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticSearchTypeArr);
            }
        }
        $this->elasticCustom = ($this->paramRequest['ApiData']['ElasticSearchCustom'] ?? null);

        $this->elasticOperator = ($this->paramRequest['ApiData']['ElasticOperator'] ?? null);
        if ($this->elasticOperator != null) {
            $this->elasticOperator = strtoupper($this->elasticOperator);
            if (!in_array($this->elasticOperator, $this->elasticOperatorArr)) {
                $this->errors[$this->elasticOperatorName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticOperatorArr);
            }
        }
        $this->elasticField = $this->paramRequest['ApiData']['ElasticField'] ?? null;
        $this->elasticFieldRequest = $this->paramRequest['ApiData']['ElasticField'] ?? null;
        if ($this->elasticField != null) {
            $this->elasticField = Str::snake($this->elasticField);
        }

        $this->elasticFields = $this->paramRequest['ApiData']['ElasticFields'] ?? null;
        $this->elasticFieldsRequest = $this->paramRequest['ApiData']['ElasticFields'] ?? null;
        if ($this->elasticFields != null) {
            $this->elasticFields = convertArrayKeysToSnakeCase($this->elasticFields);
        }

        $this->elasticMust = $this->paramRequest['ApiData']['ElasticMust'] ?? null;
        if ($this->elasticMust != null) {
            foreach ($this->elasticMust as $key => $item) {
                foreach ($this->elasticMust[$key] as $key1 => $item) {
                    if (!in_array($key1, $this->elasticSearchTypeMustShouldMustNot)) {
                        $this->errors[$this->elasticMustName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticSearchTypeMustShouldMustNot);
                    }
                    foreach($this->elasticMust[$key][$key1] as $oldKey => $oldValue){
                        if($key1 == 'range'){
                            foreach($oldValue as $key2 => $item2){
                                if (!in_array($key2, $this->elasticRangeArr)) {
                                    $this->errors[$this->elasticMustName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticRangeArr);
                                }
                            }
                        }
                        unset( $this->elasticMust[$key][$key1][$oldKey]);
                        $this->elasticMust[$key][$key1][camelToSnake($oldKey)] = $oldValue;
                    }
                }
            }
        }

        $this->elasticShould = $this->paramRequest['ApiData']['ElasticShould'] ?? null;
        if ($this->elasticShould != null) {
            foreach ($this->elasticShould as $key => $item) {
                foreach ($this->elasticShould[$key] as $key1 => $item) {
                    if (!in_array($key1, $this->elasticSearchTypeMustShouldMustNot)) {
                        $this->errors[$this->elasticShouldName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticSearchTypeMustShouldMustNot);
                    }
                    foreach($this->elasticShould[$key][$key1] as $oldKey => $oldValue){
                        if($key1 == 'range'){
                            foreach($oldValue as $key2 => $item2){
                                if (!in_array($key2, $this->elasticRangeArr)) {
                                    $this->errors[$this->elasticShouldName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticRangeArr);
                                }
                            }
                        }
                        unset( $this->elasticShould[$key][$key1][$oldKey]);
                        $this->elasticShould[$key][$key1][camelToSnake($oldKey)] = $oldValue;
                    }
                }
            }
        }

        $this->elasticMustNot = $this->paramRequest['ApiData']['ElasticMustNot'] ?? null;
        if ($this->elasticMustNot != null) {
            foreach ($this->elasticMustNot as $key => $item) {
                foreach ($this->elasticMustNot[$key] as $key1 => $item) {
                    if (!in_array($key1, $this->elasticSearchTypeMustShouldMustNot)) {
                        $this->errors[$this->elasticMustNotName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticSearchTypeMustShouldMustNot);
                    }
                    foreach($this->elasticMustNot[$key][$key1] as $oldKey => $oldValue){
                        if($key1 == 'range'){
                            foreach($oldValue as $key2 => $item2){
                                if (!in_array($key2, $this->elasticRangeArr)) {
                                    $this->errors[$this->elasticMustNotName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticRangeArr);
                                }
                            }
                        }
                        unset( $this->elasticMustNot[$key][$key1][$oldKey]);
                        $this->elasticMustNot[$key][$key1][camelToSnake($oldKey)] = $oldValue;
                    }
                }
            }
        }
        if(isset($this->elasticMust) && isset($this->elasticMust[0]['term']['is_active'])){
            $this->elasticIsActive = $this->elasticMust[0]['term']['is_active'];
        }else{
            $this->elasticIsActive = $this->isActive;
        }
        $this->serviceTypeIds = $this->paramRequest['ApiData']['ServiceTypeIds'] ?? null;
        if ($this->serviceTypeIds != null) {
            foreach ($this->serviceTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->serviceTypeIdsName] = $this->messFormat;
                    unset($this->serviceTypeIds[$key]);
                } else {
                    if (!ServiceType::where('id', $item)->exists()) {
                        $this->errors[$this->serviceTypeIdsName] = $this->messRecordId;
                        unset($this->serviceTypeIds[$key]);
                    }
                }
            }
        }
        if ($this->serviceTypeIds != null) {
            $this->serviceTypeIdsString = arrayToCustomStringNotKey($this->serviceTypeIds);
        }
        $this->patientTypeIds = $this->paramRequest['ApiData']['PatientTypeIds'] ?? null;
        if ($this->patientTypeIds != null) {
            foreach ($this->patientTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->patientTypeIdsName] = $this->messFormat;
                    unset($this->patientTypeIds[$key]);
                } else {
                    if (!PatientType::where('id', $item)->exists()) {
                        $this->errors[$this->patientTypeIdsName] = $this->messRecordId;
                        unset($this->patientTypeIds[$key]);
                    }
                }
            }
        }
        if ($this->patientTypeIds !=  null) {
            $this->patientTypeIdsString = arrayToCustomStringNotKey($this->patientTypeIds);
        }
        $this->testIndexIds = $this->paramRequest['ApiData']['TestIndexIds'] ?? null;
        if ($this->testIndexIds != null) {
            foreach ($this->testIndexIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->testIndexIdsName] = $this->messFormat;
                    unset($this->testIndexIds[$key]);
                } else {
                    if (!TestIndex::where('id', $item)->exists()) {
                        $this->errors[$this->testIndexIdsName] = $this->messRecordId;
                        unset($this->testIndexIds[$key]);
                    }
                }
            }
        }
        $this->serviceReqIds = $this->paramRequest['ApiData']['ServiceReqIds'] ?? null;
        if ($this->serviceReqIds != null) {
            foreach ($this->serviceReqIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->serviceReqIdsName] = $this->messFormat;
                    unset($this->serviceReqIds[$key]);
                } else {
                    if (!ServiceReq::where('id', $item)->exists()) {
                        $this->errors[$this->serviceReqIdsName] = $this->messRecordId;
                        unset($this->serviceReqIds[$key]);
                    }
                }
            }
        }
        $this->relations = $this->paramRequest['ApiData']['Relations'] ?? [];
        $this->sereServIds = $this->paramRequest['ApiData']['SereServIds'] ?? null;
        if ($this->sereServIds != null) {
            foreach ($this->sereServIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->sereServIdsName] = $this->messFormat;
                    unset($this->sereServIds[$key]);
                } else {
                    if (!SereServ::where('id', $item)->exists()) {
                        $this->errors[$this->sereServIdsName] = $this->messRecordId;
                        unset($this->sereServIds[$key]);
                    }
                }
            }
        }
        $this->bedRoomIds = $this->paramRequest['ApiData']['BedRoomIds'] ?? null;
        if ($this->bedRoomIds != null) {
            foreach ($this->bedRoomIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->bedRoomIdsName] = $this->messFormat;
                    unset($this->bedRoomIds[$key]);
                } else {
                    if (!BedRoom::where('id', $item)->exists()) {
                        $this->errors[$this->bedRoomIdsName] = $this->messRecordId;
                        unset($this->bedRoomIds[$key]);
                    }
                }
            }
        }
        $this->isForBill = $this->paramRequest['ApiData']['IsForBill'] ?? null;
        if ($this->isForBill !== null) {
            if (!in_array($this->isForBill, [0, 1])) {
                $this->errors[$this->isForBillName] = $this->messFormat;
                $this->isForBill = null;
            }
        }
        $this->isForRepay = $this->paramRequest['ApiData']['IsForRepay'] ?? null;
        if ($this->isForRepay !== null) {
            if (!in_array($this->isForRepay, [0, 1])) {
                $this->errors[$this->isForRepayName] = $this->messFormat;
                $this->isForRepay = null;
            }
        }
        $this->isForDeposit = $this->paramRequest['ApiData']['IsForDeposit'] ?? null;
        if ($this->isForDeposit !== null) {
            if (!in_array($this->isForDeposit, [0, 1])) {
                $this->errors[$this->isForDepositName] = $this->messFormat;
                $this->isForDeposit = null;
            }
        }
        $this->addTimeTo = $this->paramRequest['ApiData']['AddTimeTo'] ?? null;
        if($this->addTimeTo != null){
            if(!preg_match('/^\d{14}$/',  $this->addTimeTo)){
                $this->errors[$this->addTimeToName] = $this->messFormat;
                $this->addTimeTo = null;
            }
        }
        $this->addTimeFrom = $this->paramRequest['ApiData']['AddTimeFrom'] ?? null;
        if($this->addTimeFrom != null){
            if(!preg_match('/^\d{14}$/',  $this->addTimeFrom)){
                $this->errors[$this->addTimeFromName] = $this->messFormat;
                $this->addTimeFrom = null;
            }
        }
        $this->isInRoom = $this->paramRequest['ApiData']['IsInRoom'] ?? null;
        if($this->isInRoom !== null){
            if (!is_bool($this->isInRoom)) {
                $this->errors[$this->isInRoomName] = $this->messFormat;
                $this->isInRoom = null;
            }
        }
        $this->serviceId = $this->paramRequest['ApiData']['ServiceId'] ?? null;
        if ($this->serviceId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->serviceId)) {
                $this->errors[$this->serviceIdName] = $this->messFormat;
                $this->serviceId = null;
            } else {
                if (!Service::where('id', $this->serviceId)->exists()) {
                    $this->errors[$this->serviceIdName] = $this->messRecordId;
                    $this->serviceId = null;
                }
            }
        }
        $this->billId = $this->paramRequest['ApiData']['BillId'] ?? null;
        if ($this->billId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->billId)) {
                $this->errors[$this->billIdName] = $this->messFormat;
                $this->billId = null;
            } else {
                if (!Transaction::where('id', $this->billId)->exists()) {
                    $this->errors[$this->billIdName] = $this->messRecordId;
                    $this->billId = null;
                }
            }
        }
        $this->machineId = $this->paramRequest['ApiData']['MachineId'] ?? null;
        if ($this->machineId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->machineId)) {
                $this->errors[$this->machineIdName] = $this->messFormat;
                $this->machineId = null;
            } else {
                if (!Machine::where('id', $this->machineId)->exists()) {
                    $this->errors[$this->machineIdName] = $this->messRecordId;
                    $this->machineId = null;
                }
            }
        }
        $this->transactionTypeIds = $this->paramRequest['ApiData']['TransactionTypeIds'] ?? null;
        if ($this->transactionTypeIds != null) {
            foreach ($this->transactionTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->transactionTypeIdsName] = $this->messFormat;
                    unset($this->transactionTypeIds[$key]);
                } else {
                    if (!TransactionType::where('id', $item)->exists()) {
                        $this->errors[$this->transactionTypeIdsName] = $this->messRecordId;
                        unset($this->transactionTypeIds[$key]);
                    }
                }
            }
        }
        $this->packageId = $this->paramRequest['ApiData']['PackageId'] ?? null;
        if ($this->packageId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->packageId)) {
                $this->errors[$this->packageIdName] = $this->messFormat;
                $this->packageId = null;
            } else {
                if (!Package::where('id', $this->packageId)->exists()) {
                    $this->errors[$this->packageIdName] = $this->messRecordId;
                    $this->packageId = null;
                }
            }
        }
        $this->departmentId = $this->paramRequest['ApiData']['DepartmentId'] ?? null;
        if ($this->departmentId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->departmentId)) {
                $this->errors[$this->departmentIdName] = $this->messFormat;
                $this->departmentId = null;
            } else {
                if (!Department::where('id', $this->departmentId)->exists()) {
                    $this->errors[$this->departmentIdName] = $this->messRecordId;
                    $this->departmentId = null;
                }
            }
        }
        $this->tdlTreatmentId = $this->paramRequest['ApiData']['TdlTreatmentId'] ?? null;
        if ($this->tdlTreatmentId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->tdlTreatmentId)) {
                $this->errors[$this->tdlTreatmentIdName] = $this->messFormat;
                $this->tdlTreatmentId = null;
            } else {
                if (!Treatment::where('id', $this->tdlTreatmentId)->exists()) {
                    $this->errors[$this->tdlTreatmentIdName] = $this->messRecordId;
                    $this->tdlTreatmentId = null;
                }
            }
        }
        $this->isActive = $this->paramRequest['ApiData']['IsActive'] ?? null;
        if ($this->isActive !== null) {
            if (!in_array($this->isActive, [0, 1])) {
                $this->errors[$this->isActiveName] = $this->messFormat;
                $this->isActive = 1;
            }
        }
        $this->effective = $this->paramRequest['ApiData']['Effective'] ?? false;
        if (!is_bool($this->effective)) {
            $this->errors[$this->effectiveName] = $this->messFormat;
            $this->effective = false;
        }
        $this->roomTypeId = $this->paramRequest['ApiData']['RoomTypeId'] ?? null;
        if ($this->roomTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->roomTypeId)) {
                $this->errors[$this->roomTypeIdName] = $this->messFormat;
                $this->roomTypeId = null;
            } else {
                if (!RoomType::where('id', $this->roomTypeId)->exists()) {
                    $this->errors[$this->roomTypeIdName] = $this->messRecordId;
                    $this->roomTypeId = null;
                }
            }
        }
        $this->departmentIds = $this->paramRequest['ApiData']['DepartmentIds'] ?? null;
        if ($this->departmentIds != null) {
            foreach ($this->departmentIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->departmentIdsName] = $this->messFormat;
                    unset($this->departmentIds[$key]);
                } else {
                    if (!Department::where('id', $item)->exists()) {
                        $this->errors[$this->departmentIdsName] = $this->messRecordId;
                        unset($this->departmentIds[$key]);
                    }
                }
            }
        }
        $this->treatmentCode = $this->paramRequest['ApiData']['TreatmentCode'] ?? null;
        if($this->treatmentCode !== null){
            if (!is_string ($this->treatmentCode)) {
                $this->errors[$this->treatmentCodeName] = $this->messFormat;
                $this->treatmentCode = null;
            }
        }
        $this->patientCode = $this->paramRequest['ApiData']['PatientCode'] ?? null;
        if($this->patientCode !== null){
            if (!is_string ($this->patientCode)) {
                $this->errors[$this->patientCodeName] = $this->messFormat;
                $this->patientCode = null;
            }
        }
        $this->debateId = $this->paramRequest['ApiData']['DebateId'] ?? null;
        if ($this->debateId != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debateId)) {
                $this->errors[$this->debateIdName] = $this->messFormat;
                $this->debateId = null;
            } else {
                if (!Debate::where('id', $this->debateId)->exists()) {
                    $this->errors[$this->debateIdName] = $this->messRecordId;
                    $this->debateId = null;
                }
            }
        }
        $this->serviceReqSttIds = $this->paramRequest['ApiData']['ServiceReqSttIds'] ?? null;
        if ($this->serviceReqSttIds != null) {
            foreach ($this->serviceReqSttIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->serviceReqSttIdsName] = $this->messFormat;
                    unset($this->serviceReqSttIds[$key]);
                } else {
                    if (!ServiceReqStt::where('id', $item)->exists()) {
                        $this->errors[$this->serviceReqSttIdsName] = $this->messRecordId;
                        unset($this->serviceReqSttIds[$key]);
                    }
                }
            }
        }
        $this->hasExecute = $this->paramRequest['ApiData']['HasExecute'] ?? true;
        if (!is_bool ($this->hasExecute)) {
            $this->errors[$this->hasExecuteName] = $this->messFormat;
            $this->hasExecute = true;
        }
        $this->fromTime = $this->paramRequest['ApiData']['FromTime'] ?? null;
        if($this->fromTime != null){
            if(!preg_match('/^\d{14}$/',  $this->fromTime)){
                $this->errors[$this->fromTimeName] = $this->messFormat;
                $this->fromTime = null;
            }
        }
        $this->toTime = $this->paramRequest['ApiData']['ToTime'] ?? null;
        if($this->toTime != null){
            if(!preg_match('/^\d{14}$/',  $this->toTime)){
                $this->errors[$this->toTimeName] = $this->messFormat;
                $this->toTime = null;
            }
        }
        $this->logTimeTo = $this->paramRequest['ApiData']['LogTimeTo'] ?? null;
        if($this->logTimeTo != null){
            if(!preg_match('/^\d{14}$/',  $this->logTimeTo)){
                $this->errors[$this->logTimeToName] = $this->messFormat;
                $this->logTimeTo = null;
            }
        }
        $this->executeDepartmentCode = $this->paramRequest['ApiData']['ExecuteDepartmentCode'] ?? null;
        if($this->executeDepartmentCode != null){
            if(!preg_match('/^.{0,20}$/',  $this->executeDepartmentCode)){
                $this->errors[$this->executeDepartmentCodeName] = $this->messFormat;
                $this->executeDepartmentCode = null;
            }
        }
        $this->isNoExcute = $this->paramRequest['ApiData']['IsNoExcute'] ?? null;
        if($this->isNoExcute !== null){
            if (!is_bool($this->isNoExcute)) {
                $this->errors[$this->isNoExcuteName] = $this->messFormat;
                $this->isNoExcute = null;
            }
        }
        $this->isSpecimen = $this->paramRequest['ApiData']['IsSpecimen'] ?? null;
        if($this->isSpecimen !== null){
            if (!is_bool($this->isSpecimen)) {
                $this->errors[$this->isSpecimenName] = $this->messFormat;
                $this->isSpecimen = null;
            }
        }
        $this->intructionTimeTo = $this->paramRequest['ApiData']['IntructionTimeTo'] ?? null;
        if($this->intructionTimeTo != null){
            if(!preg_match('/^\d{14}$/',  $this->intructionTimeTo)){
                $this->errors[$this->intructionTimeToName] = $this->messFormat;
                $this->intructionTimeTo = null;
            }
        }
        $this->intructionTimeFrom = $this->paramRequest['ApiData']['IntructionTimeFrom'] ?? null;
        if($this->intructionTimeFrom != null){
            if(!preg_match('/^\d{14}$/',  $this->intructionTimeFrom)){
                $this->errors[$this->intructionTimeFromName] = $this->messFormat;
                $this->intructionTimeFrom = null;
            }
        }
        $this->inDateFrom = $this->paramRequest['ApiData']['InDateFrom'] ?? null;
        if($this->inDateFrom != null){
            if(!preg_match('/^\d{14}$/',  $this->inDateFrom)){
                $this->errors[$this->inDateFromName] = $this->messFormat;
                $this->inDateFrom = null;
            }
        }
        $this->inDateTo = $this->paramRequest['ApiData']['InDateTo'] ?? null;
        if($this->inDateTo != null){
            if(!preg_match('/^\d{14}$/',  $this->inDateTo)){
                $this->errors[$this->inDateToName] = $this->messFormat;
                $this->inDateTo = null;
            }
        }
        $this->tdlPatientTypeIds = $this->paramRequest['ApiData']['TdlPatientTypeIds'] ?? null;
        if ($this->tdlPatientTypeIds != null) {
            foreach ($this->tdlPatientTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->tdlPatientTypeIdsName] = $this->messFormat;
                    unset($this->tdlPatientTypeIds[$key]);
                } else {
                    if (!PatientType::where('id', $item)->exists()) {
                        $this->errors[$this->tdlPatientTypeIdsName] = $this->messRecordId;
                        unset($this->tdlPatientTypeIds[$key]);
                    }
                }
            }
        }
        $this->tdlTreatmentTypeIds = $this->paramRequest['ApiData']['TdlTreatmentTypeIds'] ?? null;
        if ($this->tdlTreatmentTypeIds != null) {
            foreach ($this->tdlTreatmentTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->tdlTreatmentTypeIdsName] = $this->messFormat;
                    unset($this->tdlTreatmentTypeIds[$key]);
                } else {
                    if (!TreatmentType::where('id', $item)->exists()) {
                        $this->errors[$this->tdlTreatmentTypeIdsName] = $this->messRecordId;
                        unset($this->tdlTreatmentTypeIds[$key]);
                    }
                }
            }
        }
        $this->notInServiceReqTypeIds = $this->paramRequest['ApiData']['NotInServiceReqTypeIds'] ?? null;
        if ($this->notInServiceReqTypeIds != null) {
            foreach ($this->notInServiceReqTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->notInServiceReqTypeIdsName] = $this->messFormat;
                    unset($this->notInServiceReqTypeIds[$key]);
                } else {
                    if (!ServiceReqType::where('id', $item)->exists()) {
                        $this->errors[$this->notInServiceReqTypeIdsName] = $this->messRecordId;
                        unset($this->notInServiceReqTypeIds[$key]);
                    }
                }
            }
        }
        $this->isNotKskRequriedAprovalOrIsKskApprove = $this->paramRequest['ApiData']['IsNotKskRequriedAproval_Or_IsKskApprove'] ?? true;
        if (!is_bool ($this->isNotKskRequriedAprovalOrIsKskApprove)) {
            $this->errors[$this->isNotKskRequriedAprovalOrIsKskApproveName] = $this->messFormat;
            $this->isNotKskRequriedAprovalOrIsKskApprove = true;
        }
        $this->serviceReqId = $this->paramRequest['ApiData']['ServiceReqId'] ?? null;
        if ($this->serviceReqId != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->serviceReqId)) {
                $this->errors[$this->serviceReqIdName] = $this->messFormat;
                $this->serviceReqId = null;
            } else {
                if (!ServiceReq::where('id', $this->serviceReqId)->exists()) {
                    $this->errors[$this->serviceReqIdName] = $this->messRecordId;
                    $this->serviceReqId = null;
                }
            }
        }
        $this->isAddition = $this->paramRequest['ApiData']['IsAddition'] ?? null;
        if ($this->isAddition !== null) {
            if (!in_array($this->isAddition, [0, 1])) {
                $this->errors[$this->isAdditionName] = $this->messFormat;
                $this->isAddition = 1;
            }
        }
        $this->serviceTypeId = $this->paramRequest['ApiData']['ServiceTypeId'] ?? null;
        if ($this->serviceTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->serviceTypeId)) {
                $this->errors[$this->serviceTypeIdName] = $this->messFormat;
                $this->serviceTypeId = null;
            } else {
                if (!ServiceType::where('id', $this->serviceTypeId)->exists()) {
                    $this->errors[$this->serviceTypeIdName] = $this->messRecordId;
                    $this->serviceTypeId = null;
                }
            }
        }
        $this->branchId = $this->paramRequest['ApiData']['BranchId'] ?? null;
        if ($this->branchId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->branchId)) {
                $this->errors[$this->branchIdName] = $this->messFormat;
                $this->branchId = null;
            } else {
                if (!Branch::where('id', $this->branchId)->exists()) {
                    $this->errors[$this->branchIdName] = $this->messRecordId;
                    $this->branchId = null;
                }
            }
        }
        $this->isApproveStore = $this->paramRequest['ApiData']['IsApproveStore'] ?? null;
        if($this->isApproveStore !== null){
            if (!is_bool($this->isApproveStore)) {
                $this->errors[$this->isApproveStoreName] = $this->messFormat;
                $this->isApproveStore = null;
            }
        }
        $this->serviceIds = $this->paramRequest['ApiData']['ServiceIds'] ?? null;
        if ($this->serviceIds != null) {
            foreach ($this->serviceIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->serviceIdsName] = $this->messFormat;
                    unset($this->serviceIds[$key]);
                } else {
                    if (!Service::where('id', $item)->exists()) {
                        $this->errors[$this->serviceIdsName] = $this->messRecordId;
                        unset($this->serviceIds[$key]);
                    }
                }
            }
        }
        if ($this->serviceIds != null) {
            $this->serviceIdsString = arrayToCustomStringNotKey($this->serviceIds);
        }
        $this->machineIds = $this->paramRequest['ApiData']['MachineIds'] ?? null;
        if ($this->machineIds != null) {
            foreach ($this->machineIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->machineIdsName] = $this->messFormat;
                    unset($this->machineIds[$key]);
                } else {
                    if (!Machine::where('id', $item)->exists()) {
                        $this->errors[$this->machineIdsName] = $this->messRecordId;
                        unset($this->machineIds[$key]);
                    }
                }
            }
        }
        if ($this->machineIds != null) {
            $this->machineIdsString = arrayToCustomStringNotKey($this->machineIds);
        }
        $this->roomIds = $this->paramRequest['ApiData']['RoomIds'] ?? null;
        if ($this->roomIds != null) {
            foreach ($this->roomIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->roomIdsName] = $this->messFormat;
                    unset($this->roomIds[$key]);
                } else {
                    if (!Room::where('id', $item)->exists()) {
                        $this->errors[$this->roomIdsName] = $this->messRecordId;
                        unset($this->roomIds[$key]);
                    }
                }
            }
        }
        $this->serviceFollowIds = $this->paramRequest['ApiData']['ServiceFollowIds'] ?? null;
        if ($this->serviceFollowIds != null) {
            foreach ($this->serviceFollowIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->serviceFollowIdsName] = $this->messFormat;
                    unset($this->serviceFollowIds[$key]);
                } else {
                    if (!Service::where('id', $item)->exists()) {
                        $this->errors[$this->serviceFollowIdsName] = $this->messRecordId;
                        unset($this->serviceFollowIds[$key]);
                    }
                }
            }
        }
        $this->bedIds = $this->paramRequest['ApiData']['BedIds'] ?? null;
        if ($this->bedIds != null) {
            foreach ($this->bedIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->bedIdsName] = $this->messFormat;
                    unset($this->bedIds[$key]);
                } else {
                    if (!Bed::where('id', $item)->exists()) {
                        $this->errors[$this->bedIdsName] = $this->messRecordId;
                        unset($this->bedIds[$key]);
                    }
                }
            }
        }
        $this->loginname = $this->paramRequest['ApiData']['Loginname'] ?? null;
        if ($this->loginname !== null) {
            // Kiểm tra 
            if (!is_string($this->loginname)) {
                $this->errors[$this->loginnameName] = $this->messFormat;
                $this->loginname = null;
            } 
        }
        $this->executeRoleId = $this->paramRequest['ApiData']['ExecuteRoleId'] ?? null;
        if ($this->executeRoleId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->executeRoleId)) {
                $this->errors[$this->executeRoleIdName] = $this->messFormat;
                $this->executeRoleId = null;
            } else {
                if (!ExecuteRole::where('id', $this->executeRoleId)->exists()) {
                    $this->errors[$this->executeRoleIdName] = $this->messRecordId;
                    $this->executeRoleId = null;
                }
            }
        }
        $this->moduleId = $this->paramRequest['ApiData']['ModuleId'] ?? null;
        if ($this->moduleId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->moduleId)) {
                $this->errors[$this->moduleIdName] = $this->messFormat;
                $this->moduleId = null;
            } else {
                if (!Module::where('id', $this->moduleId)->exists()) {
                    $this->errors[$this->moduleIdName] = $this->messRecordId;
                    $this->moduleId = null;
                }
            }
        }
        $this->roleId = $this->paramRequest['ApiData']['RoleId'] ?? null;
        if ($this->roleId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->roleId)) {
                $this->errors[$this->roleIdName] = $this->messFormat;
                $this->roleId = null;
            } else {
                if (!Role::where('id', $this->roleId)->exists()) {
                    $this->errors[$this->roleIdName] = $this->messRecordId;
                    $this->roleId = null;
                }
            }
        }
        $this->mediStockId = $this->paramRequest['ApiData']['MediStockId'] ?? null;
        if ($this->mediStockId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->mediStockId)) {
                $this->errors[$this->mediStockIdName] = $this->messFormat;
                $this->mediStockId = null;
            } else {
                if (!MediStock::where('id', $this->mediStockId)->exists()) {
                    $this->errors[$this->mediStockIdName] = $this->messRecordId;
                    $this->mediStockId = null;
                }
            }
        }
        $this->patientId = $this->paramRequest['ApiData']['PatientId'] ?? 0;

        $this->patientTypeId = $this->paramRequest['ApiData']['PatientTypeId'] ?? null;
        if ($this->patientTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patientTypeId)) {
                $this->errors[$this->patientTypeIdName] = $this->messFormat;
                $this->patientTypeId = null;
            } else {
                if (!PatientType::where('id', $this->patientTypeId)->exists()) {
                    $this->errors[$this->patientTypeIdName] = $this->messRecordId;
                    $this->patientTypeId = null;
                }
            }
        }
        $this->medicineTypeId = $this->paramRequest['ApiData']['MedicineTypeId'] ?? null;
        if ($this->medicineTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->medicineTypeId)) {
                $this->errors[$this->medicineTypeIdName] = $this->messFormat;
                $this->medicineTypeId = null;
            } else {
                if (!MedicineType::where('id', $this->medicineTypeId)->exists()) {
                    $this->errors[$this->medicineTypeIdName] = $this->messRecordId;
                    $this->medicineTypeId = null;
                }
            }
        }
        $this->materialTypeId = $this->paramRequest['ApiData']['MaterialTypeId'] ?? null;
        if ($this->materialTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->materialTypeId)) {
                $this->errors[$this->materialTypeIdName] = $this->messFormat;
                $this->materialTypeId = null;
            } else {
                if (!MaterialType::where('id', $this->materialTypeId)->exists()) {
                    $this->errors[$this->materialTypeIdName] = $this->messRecordId;
                    $this->materialTypeId = null;
                }
            }
        }
        $this->treatmentId = $this->paramRequest['ApiData']['TreatmentId'] ?? null;
        if ($this->treatmentId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->treatmentId)) {
                $this->errors[$this->treatmentIdName] = $this->messFormat;
                $this->treatmentId = null;
            } else {
                if (!Treatment::where('id', $this->treatmentId)->exists()) {
                    $this->errors[$this->treatmentIdName] = $this->messRecordId;
                    $this->treatmentId = null;
                }
            }
        }
        $this->roomId = $this->paramRequest['ApiData']['RoomId'] ?? null;
        if ($this->roomId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->roomId)) {
                $this->errors[$this->roomIdName] = $this->messFormat;
                $this->roomId = null;
            } else {
                if (!Room::where('id', $this->roomId)->exists()) {
                    $this->errors[$this->roomIdName] = $this->messRecordId;
                    $this->roomId = null;
                }
            }
        }
        $this->executeRoomId = $this->paramRequest['ApiData']['ExecuteRoomId'] ?? null;
        if ($this->executeRoomId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->executeRoomId)) {
                $this->errors[$this->executeRoomIdName] = $this->messFormat;
                $this->executeRoomId = null;
            } else {
                if (!ExecuteRoom::where('id', $this->executeRoomId)->exists()) {
                    $this->errors[$this->executeRoomIdName] = $this->messRecordId;
                    $this->executeRoomId = null;
                }
            }
        }
        $this->patientTypeAllowId = $this->paramRequest['ApiData']['PatientTypeAllowId'] ?? null;
        if ($this->patientTypeAllowId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patientTypeAllowId)) {
                $this->errors[$this->patientTypeAllowIdName] = $this->messFormat;
                $this->patientTypeAllowId = null;
            } else {
                if (!PatientType::where('id', $this->patientTypeAllowId)->exists()) {
                    $this->errors[$this->patientTypeAllowIdName] = $this->messRecordId;
                    $this->patientTypeAllowId = null;
                }
            }
        }
        $this->activeIngredientId = $this->paramRequest['ApiData']['ActiveIngredientId'] ?? null;
        if ($this->activeIngredientId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->activeIngredientId)) {
                $this->errors[$this->activeIngredientIdName] = $this->messFormat;
                $this->activeIngredientId = null;
            } else {
                if (!ActiveIngredient::where('id', $this->activeIngredientId)->exists()) {
                    $this->errors[$this->activeIngredientIdName] = $this->messRecordId;
                    $this->activeIngredientId = null;
                }
            }
        }
        $this->testServiceTypeId = $this->paramRequest['ApiData']['TestServiceTypeId'] ?? null;
        if ($this->testServiceTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->testServiceTypeId)) {
                $this->errors[$this->testServiceTypeIdName] = $this->messFormat;
                $this->testServiceTypeId = null;
            } else {
                if (!Service::leftJoin('his_service_type as service_type', 'service_type.id', '=', 'his_service.service_type_id')
                    ->where('his_service.id', $this->testServiceTypeId)
                    ->where('service_type.service_type_code', 'XN')->exists()) {
                    $this->errors[$this->testServiceTypeIdName] = $this->messRecordId;
                    $this->testServiceTypeId = null;
                }
            }
        }
    }
}
