<?php

namespace App\Http\Controllers\BaseControllers;

use App\Http\Controllers\Controller;
use App\Models\HIS\SereServ;
use App\Models\HIS\Service;
use App\Models\HIS\ServiceReq;
use App\Models\HIS\Tracking;
use App\Models\HIS\Treatment;
use App\Models\HIS\TreatmentBedRoom;
use Illuminate\Http\Request;
use App\Models\HIS\UserRoom;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class BaseApiCacheController extends Controller
{
    protected $currentUserLoginRoomIds;
    protected $currentLoginname;
    protected $table;
    protected $tableName = 'Table';
    protected $errors = [];
    protected $data = [];
    protected $param;
    protected $lastId;
    protected $lastIdName = 'LastId';
    protected $cursorPaginate;
    protected $cursorPaginateName = 'CursorPaginate';
    protected $time;
    protected $date;
    protected $dateName = 'Date';
    protected $columnsTime;
    protected $arrLimit;
    protected $isCount;
    protected $isCountName;
    protected $totalPage;
    protected $totalPageName = 'TotalPage';
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
    protected $groupBy;
    protected $groupByName = 'GroupBy';
    protected $groupByString;
    protected $onlyActive;
    protected $onlyActiveName = 'OnlyActive';
    protected $keys;
    protected $keysName = 'Keys';
    protected $noCache;
    protected $noCacheName = 'NoCache';
    protected $id;
    protected $idName = 'Id';
    protected $ids;
    protected $idsName = 'Ids';
    protected $documentIds;
    protected $documentIdsName = 'DocumentIds';
    protected $testIndexIds;
    protected $testIndexIdsName = 'TestIndexIds';
    protected $executeRoomIds;
    protected $executeRoomIdsName = 'ExecuteRoomIds';
    protected $serviceTypeCodes;
    protected $serviceTypeCodesName = 'ServiceTypeCodes';
    protected $serviceReqSttCodes;
    protected $serviceReqSttCodesName = 'ServiceReqSttCodes';
    protected $serviceCodes;
    protected $serviceCodesName = 'ServiceCodes';
    protected $tdlTreatmentId;
    protected $tdlTreatmentIdName = 'TdlTreatmentId';
    protected $documentTypeId;
    protected $documentTypeIdName = 'DocumentTypeId';
    protected $serviceTypeIds;
    protected $serviceTypeIdsName = 'ServiceTypeIds';
    protected $patientTypeIds;
    protected $patientTypeIdsName = 'PatientTypeIds';
    protected $serviceIds;
    protected $serviceIdsName = 'ServiceIds';
    protected $patientCode;
    protected $patientCodeName = 'PatientCode';
    protected $executeRoomCode;
    protected $executeRoomCodeName = 'ExecuteRoomCode';
    protected $serviceTypeCode;
    protected $serviceTypeCodeName = 'ServiceTypeCode';
    protected $departmentCode;
    protected $departmentCodeName = 'DepartmentCode';
    protected $treatmentTypeIds;
    protected $treatmentTypeIdsName = 'TreatmentTypeIds';
    protected $treatmentTypeIdsString;
    protected $isCoTreatDepartment;
    protected $isCoTreatDepartmentName = 'IsCoTreatDepartment';
    protected $patientClassifyIds;
    protected $patientClassifyIdsName = 'PatientClassifyIds';
    protected $patientClassifyIdsString;
    protected $isOut;
    protected $isOutName = 'IsOut';
    protected $addLoginname;
    protected $addLoginnameName = 'AddLoginname';
    protected $depositReqCode;
    protected $depositReqCodeName = 'DepositReqCode';
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
    protected $isDeposit;
    protected $isDepositName = 'IsDeposit';
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
    protected $tab;
    protected $tabName = 'Tab';
    protected $reportTypeCode;
    protected $reportTypeCodeName = 'ReportTypeCode';
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
    protected $trackingId;
    protected $trackingIdName = 'TrackingIdId';
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
    protected $notInTracking;
    protected $notInTrackingName = 'NotInTracking';
    protected $hasExecute;
    protected $hasExecuteName = 'HasExecute';
    protected $intructionTimeTo;
    protected $intructionTimeToName = 'IntructionTimeTo';
    protected $debateTimeTo;
    protected $debateTimeToName = 'DebateTimeTo';
    protected $debateTimeFrom;
    protected $debateTimeFromName = 'DebateTimeFrom';
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
    protected $serviceTypeCodesString;
    protected $treatmentTypeCode;
    protected $treatmentTypeCodeName = 'TreatmentTypeCode';
    protected $inTimeFrom;
    protected $inTimeFromName = 'InTimeFrom';
    protected $inTimeTo;
    protected $inTimeToName = 'InTimeTo';
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
    protected $serviceReqListVView;
    protected $serviceReqListVViewName = 'service_req_list_v_view';
    protected $trackingListVView;
    protected $trackingListVViewName = 'tracking_list_v_view';
    protected $sereServClsListVView;
    protected $sereServClsListVViewName = 'sere_serv_cls_list_v_view';
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
    protected $sereServDetailVView;
    protected $sereServDetailVViewName = 'sere_serv_detail_v_view';
    protected $debateListVView;
    protected $debateListVViewName = 'debate_list_v_view';
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
    protected $roomVView;
    protected $roomVViewName = 'room_v_view';
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
    protected $sereServListVView;
    protected $sereServListVViewName = 'sere_serv_list_v_view';
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
    protected $sereServTeinChartsVView;
    protected $sereServTeinChartsVViewName = 'sere_serv_tein_charts_v_view';
    protected $sereServTeinListVView;
    protected $sereServTeinListVViewName = 'sere_serv_tein_list_v_view';
    protected $groupType;
    protected $groupTypeName = 'group_type';
    protected $bhytBlacklist;
    protected $bhytBlacklistName = 'bhyt_blacklist';
    protected $medicinePaty;
    protected $medicinePatyName = 'medicine_paty';
    protected $accidentBodyPart;
    protected $accidentBodyPartName = 'accident_body_part';
    protected $tranPatiForm;
    protected $tranPatiFormName = 'tran_pati_form';
    protected $deathCause;
    protected $deathCauseName = 'death_cause';
    protected $treatmentResult;
    protected $treatmentResultName = 'treatment_result';
    protected $icdListVView;
    protected $icdListVViewName = 'icd_list_v_view';
    protected $documentType;
    protected $documentTypeName = 'document_type';
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
    protected $serviceReqStt;
    protected $serviceReqSttName = 'service_req_stt';
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
    protected $emrCoverType;
    protected $emrCoverTypeName = 'emr_cover_type';
    protected $emrForm;
    protected $emrFormName = 'emr_form';
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
    protected $resultClsVView;
    protected $resultClsVViewName = 'result_cls_v_view';
    protected $testServiceReqListVView;
    protected $testServiceReqListVViewName = 'test_service_req_list_v_view';
    protected $debateDetailVView;
    protected $debateDetailVViewName = 'debate_detail_v_view';
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
    protected $treatmentListVView;
    protected $treatmentListVViewName = 'treatment_list_v_view';
    protected $documentListVView;
    protected $documentListVViewName = 'document_list_v_view';
    protected $accountBookVView;
    protected $accountBookVViewName = 'account_book_v_view';
    protected $treatmentRoomGroupVView;
    protected $treatmentRoomGroupVViewName = 'treatment_room_group_v_view';
    protected $medicalCaseCoverListVView;
    protected $medicalCaseCoverListVViewName = 'medical_case_cover_list_v_view';
    protected $treatmentBedRoomListVView;
    protected $treatmentBedRoomListVViewName = 'treatment_bed_room_list_v_view';
    protected $signer;
    protected $signerName = 'signer';
    protected $isInBed;
    protected $isInBedName = 'IsInBed';
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
    protected $treatmentExecuteRoomListVView;
    protected $treatmentExecuteRoomListVViewName = 'treatment_execute_room_list_v_view';
    protected $transactionListVView;
    protected $transactionListVViewName = 'transaction_list_v_view';
    protected $depositReqListVView;
    protected $depositReqListVViewName = 'deposit_req_list_v_view';
    // Thanh toán
    protected $paymentMethod; // Hình thức thanh toán MoMo VNPay
    protected $paymentMethodName = 'PaymentMethod';
    protected $transactionTypeCode; // Tên loại giao dịch
    protected $transactionTypeCodeName = 'PaymentMethod';
    protected $paymentOption; // Phương thức thanh toán QR Code Thẻ ngân hàng
    protected $paymentOptionName = 'PaymentOption';
    // OTP
    protected $authOtpName = 'AuthOtp';
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
    protected function userRoom403($roomIds){
        if(is_array($roomIds)){
            return "Tài khoản không có quyền lấy tài nguyên với RoomId: ". implode(', ', $roomIds);
        }else{
            return "Tài khoản không có quyền lấy tài nguyên với RoomId: ".$roomIds;
        }
    } 
    protected function  checkUserRoomCurrent($roomIds){
        if($this->currentUserLoginRoomIds == null) {
            $result = $roomIds;
            return $result;
        }
        if(is_array($roomIds)){
            $result = array_diff($roomIds, $this->currentUserLoginRoomIds);
        }else{
            $check = in_array( $roomIds, $this->currentUserLoginRoomIds);
            $result = $check ? null : $roomIds;
        }
        return $result;
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
            $data = Cache::remember($name . '_check_id_' . $id, $this->time, function () use ($id, $model) {
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

        // $checkId = $this->checkId($id, $model, $modelName);
        // if ($checkId) {
        //     return $checkId;
        // }

        return null; // Trả về null nếu không có lỗi
    }
    protected function validateAndCheckCode($code, $model, $modelName)
    {
        if (!is_string($code)) {
            return returnIdError($code);
        }

        // $checkId = $this->checkId($id, $model, $modelName);
        // if ($checkId) {
        //     return $checkId;
        // }

        return null; // Trả về null nếu không có lỗi
    }

    protected function getColumnsTable($table, $isView = false)
    {
        $tableName = strtolower($table->getTable());
        $parts = explode('_', $tableName);

        if ($parts[0] === 'xa') {
            $conn = strtolower($parts[2] ?? ''); // Tránh lỗi nếu không có phần tử thứ 2
        } else {
            $conn = $isView ? strtolower($parts[1] ?? '') : strtolower($parts[0] ?? '');
        }

        $cacheKey = 'columns_' . $tableName;
        $cacheKeySet = "cache_keys:" . "columns"; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, $this->columnsTime, function () use ($tableName, $conn) {
            return Schema::connection('oracle_' . $conn)->getColumnListing($tableName) ?? [];
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }

    protected function checkOrderBy($orderBy, $columns, $orderByJoin)
    {
        // Lặp qua từng phần tử và kiểm tra
        // foreach ($orderBy as $key => $item) {
        //     if (!in_array($key, $orderByJoin)) {
        //         if ((!in_array($key, $columns))) {
        //             $this->errors[snakeToCamel($key)] = $this->messOrderByName;
        //             unset($this->orderByRequest[camelCaseFromUnderscore($key)]);
        //             unset($this->orderBy[$key]);
        //         }
        //     }
        // }
        return $orderBy;
    }
    public function getBedRoomIdsTreatmentId($treatmentId){
        $cacheKey = 'bed_room_ids_treatment_id_'.$treatmentId;
        $cacheKeySet = "cache_keys:" . $this->currentLoginname; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 600, function () use($treatmentId) {
            $bedRoomIds = TreatmentBedRoom::join('his_bed_room','his_bed_room.id', '=', 'his_treatment_bed_room.bed_room_id')
            ->where('treatment_id', $treatmentId)->pluck('his_bed_room.room_id')->toArray();
            return $bedRoomIds;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }

    public function getExeRoomIdsTreatmentId($treatmentId){
        $cacheKey = 'exe_room_ids_treatment_id_'.$treatmentId;
        $cacheKeySet = "cache_keys:" . $this->currentLoginname; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 600, function () use($treatmentId) {
            $executeRoomIds = ServiceReq::where('treatment_id', $treatmentId)->pluck('execute_room_id')->toArray();
            return $executeRoomIds;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function checkUserRoomTreatmentId($treatmentId){
        if($treatmentId == null || !is_numeric($treatmentId)){
            $this->errors[$this->treatmentIdName] = $this->messFormat;
            return ;
        }
        $bedRoomIds = $this->getBedRoomIdsTreatmentId($treatmentId);
        $executeRoomIds = $this->getExeRoomIdsTreatmentId($treatmentId);

        // Check xem treatmentId này có roomId nào thuộc quyền của user đang lấy data hay không
        $intersectBedRoom = array_intersect($bedRoomIds, $this->currentUserLoginRoomIds);
        $intersectExeRoom = array_intersect($executeRoomIds, $this->currentUserLoginRoomIds);

        $success = false;
        if(!empty($intersectBedRoom) || !empty($intersectExeRoom)){
            $success = true;
        }
        if(!$success){
            $this->errors[$this->treatmentIdName] = 'Không có quyền xem thông tin hồ sơ này';
        }
    }

    public function getBedRoomIdsPatientCode($patientCode){
        $cacheKey = 'bed_room_ids_patient_code_'.$patientCode;
        $cacheKeySet = "cache_keys:" . $this->currentLoginname; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 600, function () use($patientCode) {
            $bedRoomIds = Treatment::join('his_treatment_bed_room','his_treatment_bed_room.treatment_id', '=', 'his_treatment.id')
            ->join('his_bed_room','his_bed_room.id', '=', 'his_treatment_bed_room.bed_room_id')
            ->where('tdl_patient_code', $patientCode)->pluck('his_bed_room.room_id')->toArray();
            return $bedRoomIds;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }

    public function getExeRoomIdsPatientCode($patientCode){
        $cacheKey = 'exe_room_ids_patient_code_'.$patientCode;
        $cacheKeySet = "cache_keys:" . $this->currentLoginname; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 600, function () use($patientCode) {
            $executeRoomIds = ServiceReq::where('tdl_patient_code', $patientCode)->pluck('execute_room_id')->toArray();
            return $executeRoomIds;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function checkUserRoomPatientCode($patientCode){
        if($patientCode == null || !is_string($patientCode)){
            $this->errors[$this->patientCodeName] = $this->messFormat;
            return ;
        }
        $bedRoomIds = $this->getBedRoomIdsPatientCode($patientCode);
        $executeRoomIds = $this->getExeRoomIdsPatientCode($patientCode);

        // Check xem patientCode này có roomId nào thuộc quyền của user đang lấy data hay không
        $intersectBedRoom = array_intersect($bedRoomIds, $this->currentUserLoginRoomIds);
        $intersectExeRoom = array_intersect($executeRoomIds, $this->currentUserLoginRoomIds);

        $success = false;
        if(!empty($intersectBedRoom) || !empty($intersectExeRoom)){
            $success = true;
        }
        if(!$success){
            $this->errors[$this->patientCodeName] = 'Không có quyền xem thông tin bệnh nhân này';
        }
    }
    public function getTreatmentIdByTrackingId($id){
        $cacheKey = 'treatment_id_by_tracking_id_'.$id;
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 600, function () use($id) {
            return Tracking::find($id)->treatment_id ?? null;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function getTreatmentIdByServiceReqId($id){
        $cacheKey = 'treatment_id_by_service_req_id_'.$id;
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 600, function () use($id) {
            return ServiceReq::find($id)->treatment_id ?? null;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function getTreatmentIdBySereServId($id){
        $cacheKey = 'treatment_id_by_sere_serv_id_'.$id;
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, 600, function () use($id) {
            return SereServ::find($id)->tdl_treatment_id ?? null;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function getTreatmentIdByTreatmentCode($code){
        $cacheKey = 'treatment_id_by_treatment_code_'.$code;
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, $this->time, function () use($code) {
            return Treatment::where('treatment_code', $code)->first()->id ?? 0;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function getPatientCodedByTreatmentCode($code){
        $cacheKey = 'patient_code_by_treatment_code_'.$code;
        $cacheKeySet = "cache_keys:" . "setting"; // Set để lưu danh sách key
        $data = Cache::remember($cacheKey, $this->time, function () use($code) {
            return Treatment::where('treatment_code', $code)->first()->tdl_patient_code ?? 0;
        });
        // Lưu key vào Redis Set để dễ xóa sau này
        Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);

        return $data;
    }
    public function __construct(Request $request)
    {
        // Thời gian tồn tại của cache
        $this->time = now()->addMinutes(10080);
        $this->columnsTime = now()->addMinutes(20000);

        // Lấy loginname hiện tại
        $this->currentLoginname = get_loginname_with_token($request->bearerToken(), $this->time);

        // Lấy ra danh sách room id được quyền lấy tài nguyên của tài khoản đang đăng nhập
        if($this->currentLoginname){
            $cacheKey = 'user_login_room_ids_'.$this->currentLoginname;
            $cacheKeySet = "cache_keys:" . $this->currentLoginname; // Set để lưu danh sách key
            $cacheKeySetU = "cache_keys:" . $this->userRoomVViewName; // Set để lưu danh sách key

            $this->currentUserLoginRoomIds = Cache::remember(
                $cacheKey, 
                $this->time, 
                function () {
                    $data = UserRoom::getRoomIdsByLoginname($this->currentLoginname);
                    return base64_encode(gzcompress(serialize($data))); // Nén và mã hóa trước khi lưu
                }
            );
            // Lưu key vào Redis Set để dễ xóa sau này
            Redis::connection('cache')->sadd($cacheKeySet, [$cacheKey]);
            Redis::connection('cache')->sadd($cacheKeySetU, [$cacheKey]);
       }
        // Giải nén dữ liệu khi lấy từ cache
        if ($this->currentUserLoginRoomIds && is_string($this->currentUserLoginRoomIds)) {
            $decompressedData = @gzuncompress(base64_decode($this->currentUserLoginRoomIds));
            $this->currentUserLoginRoomIds = $decompressedData !== false ? unserialize($decompressedData) : [];
        }

        $this->param = $request->input('param');
        // Khai báo các biến
        try {
            $this->client = app('Elasticsearch');
        } catch (\Throwable $e) {
            writeAndThrowError(config('params')['elastic']['error']['connection'], $e);
        }

        // Thông báo lỗi 
        $this->messFormat = config('keywords')['error']['format'];
        $this->messOrderByName = config('keywords')['error']['order_by_name'];
        $this->messRecordId = config('keywords')['error']['record_id'];
        $this->messDecodeParam = config('keywords')['error']['decode_param'];


        // Param json gửi từ client
        if ($request->input('param') !== null) {
            // Thay thế dấu + và / nếu bị thay đổi thành khoảng trắng hoặc các ký tự khác
            $encodedParam  = str_replace([' ', '+', '/'], ['+', '+', '/'], $request->input('param'));
            $this->paramRequest = json_decode(base64_decode($encodedParam), true) ?? null;
            if ($this->paramRequest === null) {
                $this->errors['param'] = $this->messDecodeParam;
            }
        }

        // Gán và kiểm tra các tham số được gửi lên
        $this->perPage = $request->query('perPage', 10);
        $this->page = $this->paramRequest['CommonParam']['Page'] ?? 1;
        $this->start = $this->paramRequest['CommonParam']['Start'] ?? null;
        $this->limit = $this->paramRequest['CommonParam']['Limit'] ?? intval($request->limit) ?? 100;
        if ($this->limit <= 0) {
            $this->limit = 10;
        }
        $this->arrLimit = [10, 20, 50, 100, 200, 500, 1000, 2000, 4000];
        if (($this->limit < 0) || (!in_array($this->limit, $this->arrLimit))) {
            $this->errors[$this->limitName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->arrLimit);
            $this->limit = 10;
        }
        if ($this->start === null) {
            $this->start = ($this->page - 1) * $this->limit;
        }
        if ($this->start != null) {
            if ((!is_numeric($this->start)) || (!is_int($this->start)) || ($this->start < 0)) {
                $this->errors[$this->startName] = $this->messFormat;
                $this->start = 0;
            }
        }
        $this->lastId = $this->paramRequest['CommonParam']['LastId'] ?? 0;
        if ($this->lastId !== null) {
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
            } else {
                $this->keyword = trim($this->keyword); // Loại bỏ khoảng trắng thừa
            }
        }
        $this->line = $this->paramRequest['ApiData']['Line'] ?? null;
        if ($this->line !== null) {
            if (!is_int($this->line)) {
                $this->errors[$this->lineName] = $this->messFormat;
            }
        }
        $this->date = $this->paramRequest['ApiData']['Date'] ?? null;
        if ($this->date !== null) {
            if (!is_string($this->date)) {
                $this->errors[$this->dateName] = $this->messFormat;
            }
        }

        $this->getAll = $this->paramRequest['CommonParam']['GetAll'] ?? false;
        if (!is_bool($this->getAll)) {
            $this->errors[$this->getAllName] = $this->messFormat;
            $this->getAll = false;
        }

        $this->isCount = $this->paramRequest['CommonParam']['IsCount'] ?? false;
        if (!is_bool($this->isCount)) {
            $this->errors[$this->isCountName] = $this->messFormat;
            $this->isCount = false;
        }

        $this->noCache = $this->paramRequest['CommonParam']['NoCache'] ?? false;
        if (!is_bool($this->noCache)) {
            $this->errors[$this->noCacheName] = $this->messFormat;
            $this->noCache = false;
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
        if ($this->paymentMethod !== null) {
            if (!is_string($this->paymentMethod)) {
                $this->errors[$this->paymentMethodName] = $this->messFormat;
            }
        }
        $this->paymentOption = $this->paramRequest['ApiData']['PaymentOption'] ?? null;
        if ($this->paymentOption !== null) {
            if (!is_string($this->paymentOption)) {
                $this->errors[$this->paymentOptionName] = $this->messFormat;
            }
        }
        $this->transactionTypeCode = $this->paramRequest['ApiData']['TransactionTypeCode'] ?? null;
        if ($this->transactionTypeCode !== null) {
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

        $this->status = $this->paramRequest['ApiData']['Status'] ?? null;
        if ($this->status !== null) {
            if (!is_string($this->status)) {
                $this->errors[$this->statusName] = $this->messFormat;
                $this->status = null;
            }
        }

        $this->isDeposit = $this->paramRequest['ApiData']['IsDeposit'] ?? null;
        if ($this->isDeposit !== null) {
            if (!in_array($this->isDeposit, [0, 1])) {
                $this->errors[$this->isDepositName] = $this->messFormat;
                $this->isDeposit = 1;
            }
        }


        $this->transactionCode = $this->paramRequest['ApiData']['TransactionCode'] ?? null;
        if ($this->transactionCode !== null) {
            if (!is_string($this->transactionCode)) {
                $this->errors[$this->transactionCodeName] = $this->messFormat;
                $this->transactionCode = null;
            }
        }

        $this->serviceTypeCode = $this->paramRequest['ApiData']['ServiceTypeCode'] ?? null;
        if ($this->serviceTypeCode !== null) {
            if (!is_string($this->serviceTypeCode)) {
                $this->errors[$this->serviceTypeCodeName] = $this->messFormat;
                $this->serviceTypeCode = null;
            }
        }

        $this->departmentCode = $this->paramRequest['ApiData']['DepartmentCode'] ?? null;
        if ($this->departmentCode !== null) {
            if (!is_string($this->departmentCode)) {
                $this->errors[$this->departmentCodeName] = $this->messFormat;
                $this->departmentCode = null;
            }
        }

        $this->addLoginname = $this->paramRequest['ApiData']['AddLoginname'] ?? null;
        if ($this->addLoginname !== null) {
            if (!is_string($this->addLoginname)) {
                $this->errors[$this->addLoginnameName] = $this->messFormat;
                $this->addLoginname = null;
            }
        }

        $this->patientPhone = $this->paramRequest['ApiData']['PatientPhone'] ?? null;
        if ($this->patientPhone !== null) {
            if (!is_string($this->patientPhone)) {
                $this->errors[$this->patientPhoneName] = $this->messFormat;
                $this->patientPhone = null;
            }
        }

        $this->serviceReqCode = $this->paramRequest['ApiData']['ServiceReqCode'] ?? null;
        if ($this->serviceReqCode !== null) {
            if (!is_string($this->serviceReqCode)) {
                $this->errors[$this->serviceReqCodeName] = $this->messFormat;
                $this->serviceReqCode = null;
            }
        }

        $this->billCode = $this->paramRequest['ApiData']['BillCode'] ?? null;
        if ($this->billCode !== null) {
            if (!is_string($this->billCode)) {
                $this->errors[$this->billCodeName] = $this->messFormat;
                $this->billCode = null;
            }
        }

        $this->depositReqCode = $this->paramRequest['ApiData']['DepositReqCode'] ?? null;
        if ($this->depositReqCode !== null) {
            if (!is_string($this->depositReqCode)) {
                $this->errors[$this->depositReqCodeName] = $this->messFormat;
                $this->depositReqCode = null;
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
                    foreach ($this->elasticMust[$key][$key1] as $oldKey => $oldValue) {
                        if ($key1 == 'range') {
                            foreach ($oldValue as $key2 => $item2) {
                                if (!in_array($key2, $this->elasticRangeArr)) {
                                    $this->errors[$this->elasticMustName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticRangeArr);
                                }
                            }
                        }
                        unset($this->elasticMust[$key][$key1][$oldKey]);
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
                    foreach ($this->elasticShould[$key][$key1] as $oldKey => $oldValue) {
                        if ($key1 == 'range') {
                            foreach ($oldValue as $key2 => $item2) {
                                if (!in_array($key2, $this->elasticRangeArr)) {
                                    $this->errors[$this->elasticShouldName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticRangeArr);
                                }
                            }
                        }
                        unset($this->elasticShould[$key][$key1][$oldKey]);
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
                    foreach ($this->elasticMustNot[$key][$key1] as $oldKey => $oldValue) {
                        if ($key1 == 'range') {
                            foreach ($oldValue as $key2 => $item2) {
                                if (!in_array($key2, $this->elasticRangeArr)) {
                                    $this->errors[$this->elasticMustNotName] = $this->messFormat . ' Chỉ nhận giá trị thuộc mảng sau ' . implode(', ', $this->elasticRangeArr);
                                }
                            }
                        }
                        unset($this->elasticMustNot[$key][$key1][$oldKey]);
                        $this->elasticMustNot[$key][$key1][camelToSnake($oldKey)] = $oldValue;
                    }
                }
            }
        }
        if (isset($this->elasticMust) && isset($this->elasticMust[0]['term']['is_active'])) {
            $this->elasticIsActive = $this->elasticMust[0]['term']['is_active'];
        } else {
            $this->elasticIsActive = $this->isActive;
        }
        $this->serviceTypeIds = $this->paramRequest['ApiData']['ServiceTypeIds'] ?? null;
        if ($this->serviceTypeIds != null) {
            foreach ($this->serviceTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->serviceTypeIdsName] = $this->messFormat;
                    unset($this->serviceTypeIds[$key]);
                } 
            }
        }
        if ($this->serviceTypeIds != null) {
            $this->serviceTypeIdsString = arrayToCustomStringNotKey($this->serviceTypeIds);
        }
        $this->treatmentTypeIds = $this->paramRequest['ApiData']['TreatmentTypeIds'] ?? null;
        if ($this->treatmentTypeIds != null) {
            foreach ($this->treatmentTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->treatmentTypeIdsName] = $this->messFormat;
                    unset($this->treatmentTypeIds[$key]);
                } 
            }
        }
        if ($this->treatmentTypeIds != null) {
            $this->treatmentTypeIdsString = arrayToCustomStringNotKey($this->treatmentTypeIds);
        }

        $this->patientClassifyIds = $this->paramRequest['ApiData']['PatientClassifyIds'] ?? null;
        if ($this->patientClassifyIds != null) {
            foreach ($this->patientClassifyIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->patientClassifyIdsName] = $this->messFormat;
                    unset($this->patientClassifyIds[$key]);
                }
            }
        }
        if ($this->patientClassifyIds != null) {
            $this->patientClassifyIdsString = arrayToCustomStringNotKey($this->patientClassifyIds);
        }

        $this->ids = $this->paramRequest['ApiData']['Ids'] ?? null;
        if ($this->ids != null) {
            foreach ($this->ids as $key => $item) {
                if (!is_numeric($item)) {
                    $this->errors[$this->idsName] = $this->messFormat;
                    unset($this->ids[$key]);
                }
            }
        }

        $this->keys = $this->paramRequest['ApiData']['Keys'] ?? ['all'];
        if ($this->keys != null) {
            foreach ($this->keys as $key => $item) {
                if (!is_string($item)) {
                    $this->errors[$this->keysName] = $this->messFormat;
                    unset($this->keys[$key]);
                } 
            }
        }

        $this->patientTypeIds = $this->paramRequest['ApiData']['PatientTypeIds'] ?? null;
        if ($this->patientTypeIds != null) {
            foreach ($this->patientTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->patientTypeIdsName] = $this->messFormat;
                    unset($this->patientTypeIds[$key]);
                }
            }
        }
        if ($this->patientTypeIds !=  null) {
            $this->patientTypeIdsString = arrayToCustomStringNotKey($this->patientTypeIds);
        }

        $this->serviceTypeCodes = $this->paramRequest['ApiData']['ServiceTypeCodes'] ?? null;
        if ($this->serviceTypeCodes != null) {
            foreach ($this->serviceTypeCodes as $key => $item) {
                if (!is_string($item)) {
                    $this->errors[$this->serviceTypeCodesName] = $this->messFormat;
                    unset($this->serviceTypeCodes[$key]);
                } 
            }
        }
        if ($this->serviceTypeCodes !=  null) {
            $this->serviceTypeCodesString = arrayToCustomStringNotKey($this->serviceTypeCodes);
        }

        $this->serviceReqSttCodes = $this->paramRequest['ApiData']['ServiceReqSttCodes'] ?? null;
        if ($this->serviceReqSttCodes != null) {
            foreach ($this->serviceReqSttCodes as $key => $item) {
                if (!is_string($item)) {
                    $this->errors[$this->serviceReqSttCodesName] = $this->messFormat;
                    unset($this->serviceReqSttCodes[$key]);
                } 
            }
        }

        $this->serviceCodes = $this->paramRequest['ApiData']['ServiceCodes'] ?? null;
        if ($this->serviceCodes != null) {
            foreach ($this->serviceCodes as $key => $item) {
                if (!is_string($item)) {
                    $this->errors[$this->serviceCodesName] = $this->messFormat;
                    unset($this->serviceCodes[$key]);
                } 
            }
        }

        $this->table = $this->paramRequest['ApiData']['Table'] ?? ["all"];
        if ($this->table != null) {
            foreach ($this->table as $key => $item) {
                if (!is_string($item)) {
                    $this->errors[$this->tableName] = $this->messFormat;
                    unset($this->table[$key]);
                } 
            }
        }
        $this->documentIds = $this->paramRequest['ApiData']['DocumentIds'] ?? null;
        if ($this->documentIds != null) {
            foreach ($this->documentIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->documentIdsName] = $this->messFormat;
                    unset($this->documentIds[$key]);
                } 
            }
        }

        $this->testIndexIds = $this->paramRequest['ApiData']['TestIndexIds'] ?? null;
        if ($this->testIndexIds != null) {
            foreach ($this->testIndexIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->testIndexIdsName] = $this->messFormat;
                    unset($this->testIndexIds[$key]);
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
                } 
            }
        }
        $this->tab = $this->paramRequest['ApiData']['Tab']  ?? null;
        if ($this->tab !== null) {
            if (!is_string($this->tab)) {
                $this->errors[$this->tabName] = $this->messFormat;
                $this->tab = null;
            }
        }
        $this->reportTypeCode = $this->paramRequest['ApiData']['ReportTypeCode'] ?? null;
        if ($this->reportTypeCode !== null) {
            if (!is_string($this->reportTypeCode)) {
                $this->errors[$this->reportTypeCodeName] = $this->messFormat;
                $this->reportTypeCode = null;
            }
        }
        $this->bedRoomIds = $this->paramRequest['ApiData']['BedRoomIds'] ?? null;
        if ($this->bedRoomIds != null) {
            foreach ($this->bedRoomIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->bedRoomIdsName] = $this->messFormat;
                    unset($this->bedRoomIds[$key]);
                } 
            }
            $resultCheckUserRoom = $this->checkUserRoomCurrent($this->bedRoomIds);
            if($resultCheckUserRoom){
                $this->errors[$this->bedRoomIdsName] = $this->userRoom403($resultCheckUserRoom);
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
        if ($this->addTimeTo != null) {
            if (!preg_match('/^\d{14}$/',  $this->addTimeTo)) {
                $this->errors[$this->addTimeToName] = $this->messFormat;
                $this->addTimeTo = null;
            }
        }
        $this->addTimeFrom = $this->paramRequest['ApiData']['AddTimeFrom'] ?? null;
        if ($this->addTimeFrom != null) {
            if (!preg_match('/^\d{14}$/',  $this->addTimeFrom)) {
                $this->errors[$this->addTimeFromName] = $this->messFormat;
                $this->addTimeFrom = null;
            }
        }
        $this->inTimeTo = $this->paramRequest['ApiData']['InTimeTo'] ?? null;
        if ($this->inTimeTo != null) {
            if (!preg_match('/^\d{14}$/',  $this->inTimeTo)) {
                $this->errors[$this->inTimeToName] = $this->messFormat;
                $this->inTimeTo = null;
            }
        }
        $this->inTimeFrom = $this->paramRequest['ApiData']['InTimeFrom'] ?? null;
        if ($this->inTimeFrom != null) {
            if (!preg_match('/^\d{14}$/',  $this->inTimeFrom)) {
                $this->errors[$this->inTimeFromName] = $this->messFormat;
                $this->inTimeFrom = null;
            }
        }
        $this->isInRoom = $this->paramRequest['ApiData']['IsInRoom'] ?? null;
        if ($this->isInRoom !== null) {
            if (!is_bool($this->isInRoom)) {
                $this->errors[$this->isInRoomName] = $this->messFormat;
                $this->isInRoom = null;
            }
        }
        $this->notInTracking = $this->paramRequest['ApiData']['NotInTracking'] ?? null;
        if ($this->notInTracking !== null) {
            if (!is_bool($this->notInTracking)) {
                $this->errors[$this->notInTrackingName] = $this->messFormat;
                $this->notInTracking = null;
            }
        }
        $this->isInBed = $this->paramRequest['ApiData']['IsInBed'] ?? null;
        if ($this->isInBed !== null) {
            if (!is_bool($this->isInBed)) {
                $this->errors[$this->isInBedName] = $this->messFormat;
                $this->isInBed = null;
            }
        }
        $this->isCoTreatDepartment = $this->paramRequest['ApiData']['IsCoTreatDepartment'] ?? null;
        if ($this->isCoTreatDepartment !== null) {
            if (!is_bool($this->isCoTreatDepartment)) {
                $this->errors[$this->isCoTreatDepartmentName] = $this->messFormat;
                $this->isCoTreatDepartment = null;
            }
        }
        $this->isOut = $this->paramRequest['ApiData']['IsOut'] ?? null;
        if ($this->isOut !== null) {
            if (!is_bool($this->isOut)) {
                $this->errors[$this->isOutName] = $this->messFormat;
                $this->isOut = null;
            }
        }
        $this->serviceId = $this->paramRequest['ApiData']['ServiceId'] ?? null;
        if ($this->serviceId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->serviceId)) {
                $this->errors[$this->serviceIdName] = $this->messFormat;
                $this->serviceId = null;
            } 
        }
        $this->billId = $this->paramRequest['ApiData']['BillId'] ?? null;
        if ($this->billId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->billId)) {
                $this->errors[$this->billIdName] = $this->messFormat;
                $this->billId = null;
            } 
        }
        $this->machineId = $this->paramRequest['ApiData']['MachineId'] ?? null;
        if ($this->machineId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->machineId)) {
                $this->errors[$this->machineIdName] = $this->messFormat;
                $this->machineId = null;
            } 
        }
        $this->transactionTypeIds = $this->paramRequest['ApiData']['TransactionTypeIds'] ?? null;
        if ($this->transactionTypeIds != null) {
            foreach ($this->transactionTypeIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->transactionTypeIdsName] = $this->messFormat;
                    unset($this->transactionTypeIds[$key]);
                } 
            }
        }
        $this->executeRoomIds = $this->paramRequest['ApiData']['ExecuteRoomIds'] ?? null;
        if ($this->executeRoomIds != null) {
            foreach ($this->executeRoomIds as $key => $item) {
                //
                if (!is_numeric($item)) {
                    $this->errors[$this->executeRoomIdsName] = $this->messFormat;
                    unset($this->executeRoomIds[$key]);
                } 
            }
            $resultCheckUserRoom = $this->checkUserRoomCurrent($this->executeRoomIds);
            if($resultCheckUserRoom){
                $this->errors[$this->executeRoomIdsName] = $this->userRoom403($resultCheckUserRoom);
            }
        }
        $this->packageId = $this->paramRequest['ApiData']['PackageId'] ?? null;
        if ($this->packageId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->packageId)) {
                $this->errors[$this->packageIdName] = $this->messFormat;
                $this->packageId = null;
            } 
        }
        $this->departmentId = $this->paramRequest['ApiData']['DepartmentId'] ?? null;
        if ($this->departmentId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->departmentId)) {
                $this->errors[$this->departmentIdName] = $this->messFormat;
                $this->departmentId = null;
            } 
        }
        $this->tdlTreatmentId = $this->paramRequest['ApiData']['TdlTreatmentId'] ?? null;
        if ($this->tdlTreatmentId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->tdlTreatmentId)) {
                $this->errors[$this->tdlTreatmentIdName] = $this->messFormat;
                $this->tdlTreatmentId = null;
            } 
        }
        $this->documentTypeId = $this->paramRequest['ApiData']['DocumentTypeId'] ?? null;
        if ($this->documentTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->documentTypeId)) {
                $this->errors[$this->documentTypeIdName] = $this->messFormat;
                $this->documentTypeId = null;
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
            } 
        }
        $this->departmentIds = $this->paramRequest['ApiData']['DepartmentIds'] ?? null;
        if ($this->departmentIds != null) {
            foreach ($this->departmentIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->departmentIdsName] = $this->messFormat;
                    unset($this->departmentIds[$key]);
                } 
            }
        }
        $this->treatmentCode = $this->paramRequest['ApiData']['TreatmentCode'] ?? null;
        if ($this->treatmentCode !== null) {
            if (!is_string($this->treatmentCode)) {
                $this->errors[$this->treatmentCodeName] = $this->messFormat;
                $this->treatmentCode = null;
            }
        }
        $this->treatmentTypeCode = $this->paramRequest['ApiData']['TreatmentTypeCode'] ?? null;
        if ($this->treatmentTypeCode !== null) {
            if (!is_string($this->treatmentTypeCode)) {
                $this->errors[$this->treatmentTypeCodeName] = $this->messFormat;
                $this->treatmentTypeCode = null;
            }
        }
        $this->patientCode = $this->paramRequest['ApiData']['PatientCode'] ?? null;
        if ($this->patientCode !== null) {
            if (!is_string($this->patientCode)) {
                $this->errors[$this->patientCodeName] = $this->messFormat;
                $this->patientCode = null;
            }
        }else{
            if($this->treatmentCode != null){
                $this->patientCode = $this->getPatientCodedByTreatmentCode($this->treatmentCode);
            }
        }
        $this->executeRoomCode = $this->paramRequest['ApiData']['ExecuteRoomCode'] ?? null;
        if ($this->executeRoomCode !== null) {
            if (!is_string($this->executeRoomCode)) {
                $this->errors[$this->executeRoomCodeName] = $this->messFormat;
                $this->executeRoomCode = null;
            }
        }
        $this->groupBy = $this->paramRequest['ApiData']['GroupBy'] ?? null;

        if ($this->groupBy !== null && !is_array($this->groupBy)) {
            $this->errors[$this->groupByName] = $this->messFormat;
            $this->groupBy = null;
        }
        if ($this->groupBy != null) {
            $this->groupByString = arrayToCustomStringNotKey($this->groupBy);
        }
        $this->debateId = $this->paramRequest['ApiData']['DebateId'] ?? null;
        if ($this->debateId != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->debateId)) {
                $this->errors[$this->debateIdName] = $this->messFormat;
                $this->debateId = null;
            } 
        }
        $this->serviceReqSttIds = $this->paramRequest['ApiData']['ServiceReqSttIds'] ?? null;
        if ($this->serviceReqSttIds != null) {
            foreach ($this->serviceReqSttIds as $key => $item) {
                // Kiểm tra xem ID có tồn tại trong bảng  hay không
                if (!is_numeric($item)) {
                    $this->errors[$this->serviceReqSttIdsName] = $this->messFormat;
                    unset($this->serviceReqSttIds[$key]);
                } 
            }
        }
        $this->hasExecute = $this->paramRequest['ApiData']['HasExecute'] ?? true;
        if (!is_bool($this->hasExecute)) {
            $this->errors[$this->hasExecuteName] = $this->messFormat;
            $this->hasExecute = true;
        }
        $this->fromTime = $this->paramRequest['ApiData']['FromTime'] ?? null;
        if ($this->fromTime != null) {
            if (!preg_match('/^\d{14}$/',  $this->fromTime)) {
                $this->errors[$this->fromTimeName] = $this->messFormat;
                $this->fromTime = null;
            }
        }
        $this->toTime = $this->paramRequest['ApiData']['ToTime'] ?? null;
        if ($this->toTime != null) {
            if (!preg_match('/^\d{14}$/',  $this->toTime)) {
                $this->errors[$this->toTimeName] = $this->messFormat;
                $this->toTime = null;
            }
        }
        $this->logTimeTo = $this->paramRequest['ApiData']['LogTimeTo'] ?? null;
        if ($this->logTimeTo != null) {
            if (!preg_match('/^\d{14}$/',  $this->logTimeTo)) {
                $this->errors[$this->logTimeToName] = $this->messFormat;
                $this->logTimeTo = null;
            }
        }
        $this->executeDepartmentCode = $this->paramRequest['ApiData']['ExecuteDepartmentCode'] ?? null;
        if ($this->executeDepartmentCode != null) {
            if (!preg_match('/^.{0,20}$/',  $this->executeDepartmentCode)) {
                $this->errors[$this->executeDepartmentCodeName] = $this->messFormat;
                $this->executeDepartmentCode = null;
            }
        }
        $this->isNoExcute = $this->paramRequest['ApiData']['IsNoExcute'] ?? null;
        if ($this->isNoExcute !== null) {
            if (!is_bool($this->isNoExcute)) {
                $this->errors[$this->isNoExcuteName] = $this->messFormat;
                $this->isNoExcute = null;
            }
        }
        $this->isSpecimen = $this->paramRequest['ApiData']['IsSpecimen'] ?? null;
        if ($this->isSpecimen !== null) {
            if (!is_bool($this->isSpecimen)) {
                $this->errors[$this->isSpecimenName] = $this->messFormat;
                $this->isSpecimen = null;
            }
        }
        $this->intructionTimeTo = $this->paramRequest['ApiData']['IntructionTimeTo'] ?? null;
        if ($this->intructionTimeTo != null) {
            if (!preg_match('/^\d{14}$/',  $this->intructionTimeTo)) {
                $this->errors[$this->intructionTimeToName] = $this->messFormat;
                $this->intructionTimeTo = null;
            }
        }
        $this->intructionTimeFrom = $this->paramRequest['ApiData']['IntructionTimeFrom'] ?? null;
        if ($this->intructionTimeFrom != null) {
            if (!preg_match('/^\d{14}$/',  $this->intructionTimeFrom)) {
                $this->errors[$this->intructionTimeFromName] = $this->messFormat;
                $this->intructionTimeFrom = null;
            }
        }
        $this->debateTimeTo = $this->paramRequest['ApiData']['DebateTimeTo'] ?? null;
        if ($this->debateTimeTo != null) {
            if (!preg_match('/^\d{14}$/',  $this->debateTimeTo)) {
                $this->errors[$this->debateTimeToName] = $this->messFormat;
                $this->debateTimeTo = null;
            }
        }
        $this->debateTimeFrom = $this->paramRequest['ApiData']['DebateTimeFrom'] ?? null;
        if ($this->debateTimeFrom != null) {
            if (!preg_match('/^\d{14}$/',  $this->debateTimeFrom)) {
                $this->errors[$this->debateTimeFromName] = $this->messFormat;
                $this->debateTimeFrom = null;
            }
        }
        $this->inDateFrom = $this->paramRequest['ApiData']['InDateFrom'] ?? null;
        if ($this->inDateFrom != null) {
            if (!preg_match('/^\d{14}$/',  $this->inDateFrom)) {
                $this->errors[$this->inDateFromName] = $this->messFormat;
                $this->inDateFrom = null;
            }
        }
        $this->inDateTo = $this->paramRequest['ApiData']['InDateTo'] ?? null;
        if ($this->inDateTo != null) {
            if (!preg_match('/^\d{14}$/',  $this->inDateTo)) {
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
                } 
            }
        }
        $this->isNotKskRequriedAprovalOrIsKskApprove = $this->paramRequest['ApiData']['IsNotKskRequriedAproval_Or_IsKskApprove'] ?? true;
        if (!is_bool($this->isNotKskRequriedAprovalOrIsKskApprove)) {
            $this->errors[$this->isNotKskRequriedAprovalOrIsKskApproveName] = $this->messFormat;
            $this->isNotKskRequriedAprovalOrIsKskApprove = true;
        }
        $this->serviceReqId = $this->paramRequest['ApiData']['ServiceReqId'] ?? null;
        if ($this->serviceReqId != null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->serviceReqId)) {
                $this->errors[$this->serviceReqIdName] = $this->messFormat;
                $this->serviceReqId = null;
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
            } 
        }
        $this->branchId = $this->paramRequest['ApiData']['BranchId'] ?? null;
        if ($this->branchId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->branchId)) {
                $this->errors[$this->branchIdName] = $this->messFormat;
                $this->branchId = null;
            } 
        }
        $this->isApproveStore = $this->paramRequest['ApiData']['IsApproveStore'] ?? null;
        if ($this->isApproveStore !== null) {
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
            } 
        }
        $this->moduleId = $this->paramRequest['ApiData']['ModuleId'] ?? null;
        if ($this->moduleId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->moduleId)) {
                $this->errors[$this->moduleIdName] = $this->messFormat;
                $this->moduleId = null;
            } 
        }
        $this->roleId = $this->paramRequest['ApiData']['RoleId'] ?? null;
        if ($this->roleId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->roleId)) {
                $this->errors[$this->roleIdName] = $this->messFormat;
                $this->roleId = null;
            } 
        }
        $this->mediStockId = $this->paramRequest['ApiData']['MediStockId'] ?? null;
        if ($this->mediStockId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->mediStockId)) {
                $this->errors[$this->mediStockIdName] = $this->messFormat;
                $this->mediStockId = null;
            } 
        }
        $this->patientId = $this->paramRequest['ApiData']['PatientId'] ?? 0;

        $this->patientTypeId = $this->paramRequest['ApiData']['PatientTypeId'] ?? null;
        if ($this->patientTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patientTypeId)) {
                $this->errors[$this->patientTypeIdName] = $this->messFormat;
                $this->patientTypeId = null;
            } 
        }
        $this->medicineTypeId = $this->paramRequest['ApiData']['MedicineTypeId'] ?? null;
        if ($this->medicineTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->medicineTypeId)) {
                $this->errors[$this->medicineTypeIdName] = $this->messFormat;
                $this->medicineTypeId = null;
            }
        }
        $this->materialTypeId = $this->paramRequest['ApiData']['MaterialTypeId'] ?? null;
        if ($this->materialTypeId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->materialTypeId)) {
                $this->errors[$this->materialTypeIdName] = $this->messFormat;
                $this->materialTypeId = null;
            } 
        }
        $this->treatmentId = $this->paramRequest['ApiData']['TreatmentId'] ?? null;
        if ($this->treatmentId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->treatmentId)) {
                $this->errors[$this->treatmentIdName] = $this->messFormat;
                $this->treatmentId = null;
            } 
        }else{
            if($this->treatmentCode != null){
                $this->treatmentId = $this->getTreatmentIdByTreatmentCode($this->treatmentCode);
            }
        }

        $this->trackingId = $this->paramRequest['ApiData']['TrackingId'] ?? null;
        if ($this->trackingId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->trackingId)) {
                $this->errors[$this->trackingIdName] = $this->messFormat;
                $this->trackingId = null;
            } 
        }
        $this->roomId = $this->paramRequest['ApiData']['RoomId'] ?? null;
        if ($this->roomId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->roomId)) {
                $this->errors[$this->roomIdName] = $this->messFormat;
                $this->roomId = null;
            } 
        }
        $this->executeRoomId = $this->paramRequest['ApiData']['ExecuteRoomId'] ?? null;
        if ($this->executeRoomId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->executeRoomId)) {
                $this->errors[$this->executeRoomIdName] = $this->messFormat;
                $this->executeRoomId = null;
            } 
        }
        $this->patientTypeAllowId = $this->paramRequest['ApiData']['PatientTypeAllowId'] ?? null;
        if ($this->patientTypeAllowId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->patientTypeAllowId)) {
                $this->errors[$this->patientTypeAllowIdName] = $this->messFormat;
                $this->patientTypeAllowId = null;
            } 
        }
        $this->activeIngredientId = $this->paramRequest['ApiData']['ActiveIngredientId'] ?? null;
        if ($this->activeIngredientId !== null) {
            // Kiểm tra xem ID có tồn tại trong bảng  hay không
            if (!is_numeric($this->activeIngredientId)) {
                $this->errors[$this->activeIngredientIdName] = $this->messFormat;
                $this->activeIngredientId = null;
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
