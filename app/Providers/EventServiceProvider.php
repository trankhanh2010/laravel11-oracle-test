<?php

namespace App\Providers;

use App\Events\Cache\DeleteCache;
use App\Events\Elastic\AccidentBodyPart\CreateAccidentBodyPartIndex;
use App\Events\Elastic\AccidentBodyPart\InsertAccidentBodyPartIndex;
use App\Events\Elastic\AccidentCare\CreateAccidentCareIndex;
use App\Events\Elastic\AccidentCare\InsertAccidentCareIndex;
use App\Events\Elastic\AccidentHurtType\CreateAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentHurtType\InsertAccidentHurtTypeIndex;
use App\Events\Elastic\AccidentLocation\CreateAccidentLocationIndex;
use App\Events\Elastic\AccidentLocation\InsertAccidentLocationIndex;
use App\Events\Elastic\AccountBookVView\CreateAccountBookVViewIndex;
use App\Events\Elastic\AccountBookVView\InsertAccountBookVViewIndex;
use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use App\Events\Elastic\AgeType\InsertAgeTypeIndex;
use App\Events\Elastic\Area\CreateAreaIndex;
use App\Events\Elastic\Area\InsertAreaIndex;
use App\Events\Elastic\Atc\CreateAtcIndex;
use App\Events\Elastic\Atc\InsertAtcIndex;
use App\Events\Elastic\AtcGroup\CreateAtcGroupIndex;
use App\Events\Elastic\AtcGroup\InsertAtcGroupIndex;
use App\Events\Elastic\Awareness\CreateAwarenessIndex;
use App\Events\Elastic\Awareness\InsertAwarenessIndex;
use App\Events\Elastic\Bed\CreateBedIndex;
use App\Events\Elastic\Bed\InsertBedIndex;
use App\Events\Elastic\BedBsty\CreateBedBstyIndex;
use App\Events\Elastic\BedBsty\InsertBedBstyIndex;
use App\Events\Elastic\BedRoom\CreateBedRoomIndex;
use App\Events\Elastic\BedRoom\InsertBedRoomIndex;
use App\Events\Elastic\BedType\CreateBedTypeIndex;
use App\Events\Elastic\BedType\InsertBedTypeIndex;
use App\Events\Elastic\BhytBlacklist\CreateBhytBlacklistIndex;
use App\Events\Elastic\BhytBlacklist\InsertBhytBlacklistIndex;
use App\Events\Elastic\BhytParam\CreateBhytParamIndex;
use App\Events\Elastic\BhytParam\InsertBhytParamIndex;
use App\Events\Elastic\BhytWhitelist\CreateBhytWhitelistIndex;
use App\Events\Elastic\BhytWhitelist\InsertBhytWhitelistIndex;
use App\Events\Elastic\Bid\CreateBidIndex;
use App\Events\Elastic\Bid\InsertBidIndex;
use App\Events\Elastic\BidType\CreateBidTypeIndex;
use App\Events\Elastic\BidType\InsertBidTypeIndex;
use App\Events\Elastic\BloodGroup\CreateBloodGroupIndex;
use App\Events\Elastic\BloodGroup\InsertBloodGroupIndex;
use App\Events\Elastic\BloodVolume\CreateBloodVolumeIndex;
use App\Events\Elastic\BloodVolume\InsertBloodVolumeIndex;
use App\Events\Elastic\BodyPart\CreateBodyPartIndex;
use App\Events\Elastic\BodyPart\InsertBodyPartIndex;
use App\Events\Elastic\BornPosition\CreateBornPositionIndex;
use App\Events\Elastic\BornPosition\InsertBornPositionIndex;
use App\Events\Elastic\Branch\CreateBranchIndex;
use App\Events\Elastic\Branch\InsertBranchIndex;
use App\Events\Elastic\CancelReason\CreateCancelReasonIndex;
use App\Events\Elastic\CancelReason\InsertCancelReasonIndex;
use App\Events\Elastic\Career\CreateCareerIndex;
use App\Events\Elastic\Career\InsertCareerIndex;
use App\Events\Elastic\CareerTitle\CreateCareerTitleIndex;
use App\Events\Elastic\CareerTitle\InsertCareerTitleIndex;
use App\Events\Elastic\CashierRoom\CreateCashierRoomIndex;
use App\Events\Elastic\CashierRoom\InsertCashierRoomIndex;
use App\Events\Elastic\Commune\CreateCommuneIndex;
use App\Events\Elastic\Commune\InsertCommuneIndex;
use App\Events\Elastic\Contraindication\CreateContraindicationIndex;
use App\Events\Elastic\Contraindication\InsertContraindicationIndex;
use App\Events\Elastic\DataStore\CreateDataStoreIndex;
use App\Events\Elastic\DataStore\InsertDataStoreIndex;
use App\Events\Elastic\DeathCause\CreateDeathCauseIndex;
use App\Events\Elastic\DeathCause\InsertDeathCauseIndex;
use App\Events\Elastic\DeathWithin\CreateDeathWithinIndex;
use App\Events\Elastic\DeathWithin\InsertDeathWithinIndex;
use App\Events\Elastic\Debate\CreateDebateIndex;
use App\Events\Elastic\Debate\InsertDebateIndex;
use App\Events\Elastic\DebateEkipUser\CreateDebateEkipUserIndex;
use App\Events\Elastic\DebateEkipUser\InsertDebateEkipUserIndex;
use App\Events\Elastic\DebateReason\CreateDebateReasonIndex;
use App\Events\Elastic\DebateReason\InsertDebateReasonIndex;
use App\Events\Elastic\DebateType\CreateDebateTypeIndex;
use App\Events\Elastic\DebateType\InsertDebateTypeIndex;
use App\Events\Elastic\DebateUser\CreateDebateUserIndex;
use App\Events\Elastic\DebateUser\InsertDebateUserIndex;
use App\Events\Elastic\DebateVView\CreateDebateVViewIndex;
use App\Events\Elastic\DebateVView\InsertDebateVViewIndex;
use App\Events\Elastic\DeleteIndex;
use App\Events\Elastic\Department\CreateDepartmentIndex;
use App\Events\Elastic\Department\InsertDepartmentIndex;
use App\Events\Elastic\Dhst\CreateDhstIndex;
use App\Events\Elastic\Dhst\InsertDhstIndex;
use App\Events\Elastic\DiimType\CreateDiimTypeIndex;
use App\Events\Elastic\DiimType\InsertDiimTypeIndex;
use App\Events\Elastic\District\CreateDistrictIndex;
use App\Events\Elastic\District\InsertDistrictIndex;
use App\Events\Elastic\DocumentType\CreateDocumentTypeIndex;
use App\Events\Elastic\DocumentType\InsertDocumentTypeIndex;
use App\Events\Elastic\DosageForm\CreateDosageFormIndex;
use App\Events\Elastic\DosageForm\InsertDosageFormIndex;
use App\Events\Elastic\EmotionlessMethod\CreateEmotionlessMethodIndex;
use App\Events\Elastic\EmotionlessMethod\InsertEmotionlessMethodIndex;
use App\Events\Elastic\Employee\CreateEmployeeIndex;
use App\Events\Elastic\Employee\InsertEmployeeIndex;
use App\Events\Elastic\EmrCoverType\CreateEmrCoverTypeIndex;
use App\Events\Elastic\EmrCoverType\InsertEmrCoverTypeIndex;
use App\Events\Elastic\EmrForm\CreateEmrFormIndex;
use App\Events\Elastic\EmrForm\InsertEmrFormIndex;
use App\Events\Elastic\Ethnic\CreateEthnicIndex;
use App\Events\Elastic\Ethnic\InsertEthnicIndex;
use App\Events\Elastic\ExecuteGroup\CreateExecuteGroupIndex;
use App\Events\Elastic\ExecuteGroup\InsertExecuteGroupIndex;
use App\Events\Elastic\ExecuteRole\CreateExecuteRoleIndex;
use App\Events\Elastic\ExecuteRole\InsertExecuteRoleIndex;
use App\Events\Elastic\ExecuteRoleUser\CreateExecuteRoleUserIndex;
use App\Events\Elastic\ExecuteRoleUser\InsertExecuteRoleUserIndex;
use App\Events\Elastic\ExecuteRoom\CreateExecuteRoomIndex;
use App\Events\Elastic\ExecuteRoom\InsertExecuteRoomIndex;
use App\Events\Elastic\ExeServiceModule\CreateExeServiceModuleIndex;
use App\Events\Elastic\ExeServiceModule\InsertExeServiceModuleIndex;
use App\Events\Elastic\ExpMestReason\CreateExpMestReasonIndex;
use App\Events\Elastic\ExpMestReason\InsertExpMestReasonIndex;
use App\Events\Elastic\ExroRoom\CreateExroRoomIndex;
use App\Events\Elastic\ExroRoom\InsertExroRoomIndex;
use App\Events\Elastic\FileType\CreateFileTypeIndex;
use App\Events\Elastic\FileType\InsertFileTypeIndex;
use App\Events\Elastic\FilmSize\CreateFilmSizeIndex;
use App\Events\Elastic\FilmSize\InsertFilmSizeIndex;
use App\Events\Elastic\FuexType\CreateFuexTypeIndex;
use App\Events\Elastic\FuexType\InsertFuexTypeIndex;
use App\Events\Elastic\Gender\CreateGenderIndex;
use App\Events\Elastic\Gender\InsertGenderIndex;
use App\Events\Elastic\Group\CreateGroupIndex;
use App\Events\Elastic\Group\InsertGroupIndex;
use App\Events\Elastic\GroupType\CreateGroupTypeIndex;
use App\Events\Elastic\GroupType\InsertGroupTypeIndex;
use App\Events\Elastic\HeinServiceType\CreateHeinServiceTypeIndex;
use App\Events\Elastic\HeinServiceType\InsertHeinServiceTypeIndex;
use App\Events\Elastic\HospitalizeReason\CreateHospitalizeReasonIndex;
use App\Events\Elastic\HospitalizeReason\InsertHospitalizeReasonIndex;
use App\Events\Elastic\Htu\CreateHtuIndex;
use App\Events\Elastic\Htu\InsertHtuIndex;
use App\Events\Elastic\Icd\CreateIcdIndex;
use App\Events\Elastic\Icd\InsertIcdIndex;
use App\Events\Elastic\IcdCm\CreateIcdCmIndex;
use App\Events\Elastic\IcdCm\InsertIcdCmIndex;
use App\Events\Elastic\IcdGroup\CreateIcdGroupIndex;
use App\Events\Elastic\IcdGroup\InsertIcdGroupIndex;
use App\Events\Elastic\IcdListVView\CreateIcdListVViewIndex;
use App\Events\Elastic\IcdListVView\InsertIcdListVViewIndex;
use App\Events\Elastic\ImpSource\CreateImpSourceIndex;
use App\Events\Elastic\ImpSource\InsertImpSourceIndex;
use App\Events\Elastic\InteractionReason\CreateInteractionReasonIndex;
use App\Events\Elastic\InteractionReason\InsertInteractionReasonIndex;
use App\Events\Elastic\LicenseClass\CreateLicenseClassIndex;
use App\Events\Elastic\LicenseClass\InsertLicenseClassIndex;
use App\Events\Elastic\LocationStore\CreateLocationStoreIndex;
use App\Events\Elastic\LocationStore\InsertLocationStoreIndex;
use App\Events\Elastic\Machine\CreateMachineIndex;
use App\Events\Elastic\Machine\InsertMachineIndex;
use App\Events\Elastic\Manufacturer\CreateManufacturerIndex;
use App\Events\Elastic\Manufacturer\InsertManufacturerIndex;
use App\Events\Elastic\MaterialType\CreateMaterialTypeIndex;
use App\Events\Elastic\MaterialType\InsertMaterialTypeIndex;
use App\Events\Elastic\MaterialTypeMap\CreateMaterialTypeMapIndex;
use App\Events\Elastic\MaterialTypeMap\InsertMaterialTypeMapIndex;
use App\Events\Elastic\MedicalContract\CreateMedicalContractIndex;
use App\Events\Elastic\MedicalContract\InsertMedicalContractIndex;
use App\Events\Elastic\Medicine\CreateMedicineIndex;
use App\Events\Elastic\Medicine\InsertMedicineIndex;
use App\Events\Elastic\MedicineGroup\CreateMedicineGroupIndex;
use App\Events\Elastic\MedicineGroup\InsertMedicineGroupIndex;
use App\Events\Elastic\MedicineLine\CreateMedicineLineIndex;
use App\Events\Elastic\MedicineLine\InsertMedicineLineIndex;
use App\Events\Elastic\MedicinePaty\CreateMedicinePatyIndex;
use App\Events\Elastic\MedicinePaty\InsertMedicinePatyIndex;
use App\Events\Elastic\MedicineType\CreateMedicineTypeIndex;
use App\Events\Elastic\MedicineType\InsertMedicineTypeIndex;
use App\Events\Elastic\MedicineTypeAcin\CreateMedicineTypeAcinIndex;
use App\Events\Elastic\MedicineTypeAcin\InsertMedicineTypeAcinIndex;
use App\Events\Elastic\MedicineUseForm\CreateMedicineUseFormIndex;
use App\Events\Elastic\MedicineUseForm\InsertMedicineUseFormIndex;
use App\Events\Elastic\MediOrg\CreateMediOrgIndex;
use App\Events\Elastic\MediOrg\InsertMediOrgIndex;
use App\Events\Elastic\MediRecordType\CreateMediRecordTypeIndex;
use App\Events\Elastic\MediRecordType\InsertMediRecordTypeIndex;
use App\Events\Elastic\MediStock\CreateMediStockIndex;
use App\Events\Elastic\MediStock\InsertMediStockIndex;
use App\Events\Elastic\MediStockMaty\CreateMediStockMatyIndex;
use App\Events\Elastic\MediStockMaty\InsertMediStockMatyIndex;
use App\Events\Elastic\MediStockMety\CreateMediStockMetyIndex;
use App\Events\Elastic\MediStockMety\InsertMediStockMetyIndex;
use App\Events\Elastic\MemaGroup\CreateMemaGroupIndex;
use App\Events\Elastic\MemaGroup\InsertMemaGroupIndex;
use App\Events\Elastic\MestPatientType\CreateMestPatientTypeIndex;
use App\Events\Elastic\MestPatientType\InsertMestPatientTypeIndex;
use App\Events\Elastic\MestRoom\CreateMestRoomIndex;
use App\Events\Elastic\MestRoom\InsertMestRoomIndex;
use App\Events\Elastic\MilitaryRank\CreateMilitaryRankIndex;
use App\Events\Elastic\MilitaryRank\InsertMilitaryRankIndex;
use App\Events\Elastic\Module\CreateModuleIndex;
use App\Events\Elastic\Module\InsertModuleIndex;
use App\Events\Elastic\ModuleRole\CreateModuleRoleIndex;
use App\Events\Elastic\ModuleRole\InsertModuleRoleIndex;
use App\Events\Elastic\National\CreateNationalIndex;
use App\Events\Elastic\National\InsertNationalIndex;
use App\Events\Elastic\OtherPaySource\CreateOtherPaySourceIndex;
use App\Events\Elastic\OtherPaySource\InsertOtherPaySourceIndex;
use App\Events\Elastic\Package\CreatePackageIndex;
use App\Events\Elastic\Package\InsertPackageIndex;
use App\Events\Elastic\PackingType\CreatePackingTypeIndex;
use App\Events\Elastic\PackingType\InsertPackingTypeIndex;
use App\Events\Elastic\PatientCase\CreatePatientCaseIndex;
use App\Events\Elastic\PatientCase\InsertPatientCaseIndex;
use App\Events\Elastic\PatientClassify\CreatePatientClassifyIndex;
use App\Events\Elastic\PatientClassify\InsertPatientClassifyIndex;
use App\Events\Elastic\PatientType\CreatePatientTypeIndex;
use App\Events\Elastic\PatientType\InsertPatientTypeIndex;
use App\Events\Elastic\PatientTypeAllow\CreatePatientTypeAllowIndex;
use App\Events\Elastic\PatientTypeAllow\InsertPatientTypeAllowIndex;
use App\Events\Elastic\PatientTypeAlterVView\CreatePatientTypeAlterVViewIndex;
use App\Events\Elastic\PatientTypeAlterVView\InsertPatientTypeAlterVViewIndex;
use App\Events\Elastic\PatientTypeRoom\CreatePatientTypeRoomIndex;
use App\Events\Elastic\PatientTypeRoom\InsertPatientTypeRoomIndex;
use App\Events\Elastic\PayForm\CreatePayFormIndex;
use App\Events\Elastic\PayForm\InsertPayFormIndex;
use App\Events\Elastic\Position\CreatePositionIndex;
use App\Events\Elastic\Position\InsertPositionIndex;
use App\Events\Elastic\PreparationsBlood\CreatePreparationsBloodIndex;
use App\Events\Elastic\PreparationsBlood\InsertPreparationsBloodIndex;
use App\Events\Elastic\PriorityType\CreatePriorityTypeIndex;
use App\Events\Elastic\PriorityType\InsertPriorityTypeIndex;
use App\Events\Elastic\ProcessingMethod\CreateProcessingMethodIndex;
use App\Events\Elastic\ProcessingMethod\InsertProcessingMethodIndex;
use App\Events\Elastic\Province\CreateProvinceIndex;
use App\Events\Elastic\Province\InsertProvinceIndex;
use App\Events\Elastic\PtttCatastrophe\CreatePtttCatastropheIndex;
use App\Events\Elastic\PtttCatastrophe\InsertPtttCatastropheIndex;
use App\Events\Elastic\PtttCondition\CreatePtttConditionIndex;
use App\Events\Elastic\PtttCondition\InsertPtttConditionIndex;
use App\Events\Elastic\PtttGroup\CreatePtttGroupIndex;
use App\Events\Elastic\PtttGroup\InsertPtttGroupIndex;
use App\Events\Elastic\PtttMethod\CreatePtttMethodIndex;
use App\Events\Elastic\PtttMethod\InsertPtttMethodIndex;
use App\Events\Elastic\PtttTable\CreatePtttTableIndex;
use App\Events\Elastic\PtttTable\InsertPtttTableIndex;
use App\Events\Elastic\RationGroup\CreateRationGroupIndex;
use App\Events\Elastic\RationGroup\InsertRationGroupIndex;
use App\Events\Elastic\RationTime\CreateRationTimeIndex;
use App\Events\Elastic\RationTime\InsertRationTimeIndex;
use App\Events\Elastic\ReceptionRoom\CreateReceptionRoomIndex;
use App\Events\Elastic\ReceptionRoom\InsertReceptionRoomIndex;
use App\Events\Elastic\Refectory\CreateRefectoryIndex;
use App\Events\Elastic\Refectory\InsertRefectoryIndex;
use App\Events\Elastic\Relation\CreateRelationIndex;
use App\Events\Elastic\Relation\InsertRelationIndex;
use App\Events\Elastic\Religion\CreateReligionIndex;
use App\Events\Elastic\Religion\InsertReligionIndex;
use App\Events\Elastic\Role\CreateRoleIndex;
use App\Events\Elastic\Role\InsertRoleIndex;
use App\Events\Elastic\Room\CreateRoomIndex;
use App\Events\Elastic\Room\InsertRoomIndex;
use App\Events\Elastic\RoomGroup\CreateRoomGroupIndex;
use App\Events\Elastic\RoomGroup\InsertRoomGroupIndex;
use App\Events\Elastic\RoomType\CreateRoomTypeIndex;
use App\Events\Elastic\RoomType\InsertRoomTypeIndex;
use App\Events\Elastic\RoomVView\CreateRoomVViewIndex;
use App\Events\Elastic\RoomVView\InsertRoomVViewIndex;
use App\Events\Elastic\SaleProfitCfg\CreateSaleProfitCfgIndex;
use App\Events\Elastic\SaleProfitCfg\InsertSaleProfitCfgIndex;
use App\Events\Elastic\SereServ\CreateSereServIndex;
use App\Events\Elastic\SereServ\InsertSereServIndex;
use App\Events\Elastic\SereServBill\CreateSereServBillIndex;
use App\Events\Elastic\SereServBill\InsertSereServBillIndex;
use App\Events\Elastic\SereServDepositVView\CreateSereServDepositVViewIndex;
use App\Events\Elastic\SereServDepositVView\InsertSereServDepositVViewIndex;
use App\Events\Elastic\SereServExt\CreateSereServExtIndex;
use App\Events\Elastic\SereServExt\InsertSereServExtIndex;
use App\Events\Elastic\SereServTein\CreateSereServTeinIndex;
use App\Events\Elastic\SereServTein\InsertSereServTeinIndex;
use App\Events\Elastic\SereServTeinVView\CreateSereServTeinVViewIndex;
use App\Events\Elastic\SereServTeinVView\InsertSereServTeinVViewIndex;
use App\Events\Elastic\SereServVView4\CreateSereServVView4Index;
use App\Events\Elastic\SereServVView4\InsertSereServVView4Index;
use App\Events\Elastic\Service\CreateServiceIndex;
use App\Events\Elastic\Service\InsertServiceIndex;
use App\Events\Elastic\ServiceCondition\CreateServiceConditionIndex;
use App\Events\Elastic\ServiceCondition\InsertServiceConditionIndex;
use App\Events\Elastic\ServiceFollow\CreateServiceFollowIndex;
use App\Events\Elastic\ServiceFollow\InsertServiceFollowIndex;
use App\Events\Elastic\ServiceGroup\CreateServiceGroupIndex;
use App\Events\Elastic\ServiceGroup\InsertServiceGroupIndex;
use App\Events\Elastic\ServiceMachine\CreateServiceMachineIndex;
use App\Events\Elastic\ServiceMachine\InsertServiceMachineIndex;
use App\Events\Elastic\ServicePaty\CreateServicePatyIndex;
use App\Events\Elastic\ServicePaty\InsertServicePatyIndex;
use App\Events\Elastic\ServiceReq\CreateServiceReqIndex;
use App\Events\Elastic\ServiceReq\InsertServiceReqIndex;
use App\Events\Elastic\ServiceReqLView\CreateServiceReqLViewIndex;
use App\Events\Elastic\ServiceReqLView\InsertServiceReqLViewIndex;
use App\Events\Elastic\ServiceReqStt\CreateServiceReqSttIndex;
use App\Events\Elastic\ServiceReqStt\InsertServiceReqSttIndex;
use App\Events\Elastic\ServiceReqType\CreateServiceReqTypeIndex;
use App\Events\Elastic\ServiceReqType\InsertServiceReqTypeIndex;
use App\Events\Elastic\ServiceRoom\CreateServiceRoomIndex;
use App\Events\Elastic\ServiceRoom\InsertServiceRoomIndex;
use App\Events\Elastic\ServiceType\CreateServiceTypeIndex;
use App\Events\Elastic\ServiceType\InsertServiceTypeIndex;
use App\Events\Elastic\ServiceUnit\CreateServiceUnitIndex;
use App\Events\Elastic\ServiceUnit\InsertServiceUnitIndex;
use App\Events\Elastic\ServSegr\CreateServSegrIndex;
use App\Events\Elastic\ServSegr\InsertServSegrIndex;
use App\Events\Elastic\SeseDepoRepayVView\CreateSeseDepoRepayVViewIndex;
use App\Events\Elastic\SeseDepoRepayVView\InsertSeseDepoRepayVViewIndex;
use App\Events\Elastic\Speciality\CreateSpecialityIndex;
use App\Events\Elastic\Speciality\InsertSpecialityIndex;
use App\Events\Elastic\StorageCondition\CreateStorageConditionIndex;
use App\Events\Elastic\StorageCondition\InsertStorageConditionIndex;
use App\Events\Elastic\SuimIndex\CreateSuimIndexIndex;
use App\Events\Elastic\SuimIndex\InsertSuimIndexIndex;
use App\Events\Elastic\SuimIndexUnit\CreateSuimIndexUnitIndex;
use App\Events\Elastic\SuimIndexUnit\InsertSuimIndexUnitIndex;
use App\Events\Elastic\Supplier\CreateSupplierIndex;
use App\Events\Elastic\Supplier\InsertSupplierIndex;
use App\Events\Elastic\TestIndex\CreateTestIndexIndex;
use App\Events\Elastic\TestIndex\InsertTestIndexIndex;
use App\Events\Elastic\TestIndexGroup\CreateTestIndexGroupIndex;
use App\Events\Elastic\TestIndexGroup\InsertTestIndexGroupIndex;
use App\Events\Elastic\TestIndexUnit\CreateTestIndexUnitIndex;
use App\Events\Elastic\TestIndexUnit\InsertTestIndexUnitIndex;
use App\Events\Elastic\TestSampleType\CreateTestSampleTypeIndex;
use App\Events\Elastic\TestSampleType\InsertTestSampleTypeIndex;
use App\Events\Elastic\TestServiceReqListVView\CreateTestServiceReqListVViewIndex;
use App\Events\Elastic\TestServiceReqListVView\InsertTestServiceReqListVViewIndex;
use App\Events\Elastic\TestType\CreateTestTypeIndex;
use App\Events\Elastic\TestType\InsertTestTypeIndex;
use App\Events\Elastic\Tracking\CreateTrackingIndex;
use App\Events\Elastic\Tracking\InsertTrackingIndex;
use App\Events\Elastic\TranPatiForm\CreateTranPatiFormIndex;
use App\Events\Elastic\TranPatiForm\InsertTranPatiFormIndex;
use App\Events\Elastic\TranPatiTech\CreateTranPatiTechIndex;
use App\Events\Elastic\TranPatiTech\InsertTranPatiTechIndex;
use App\Events\Elastic\TransactionType\CreateTransactionTypeIndex;
use App\Events\Elastic\TransactionType\InsertTransactionTypeIndex;
use App\Events\Elastic\TreatmentBedRoomLView\CreateTreatmentBedRoomLViewIndex;
use App\Events\Elastic\TreatmentBedRoomLView\InsertTreatmentBedRoomLViewIndex;
use App\Events\Elastic\TreatmentEndType\CreateTreatmentEndTypeIndex;
use App\Events\Elastic\TreatmentEndType\InsertTreatmentEndTypeIndex;
use App\Events\Elastic\TreatmentFeeView\CreateTreatmentFeeViewIndex;
use App\Events\Elastic\TreatmentFeeView\InsertTreatmentFeeViewIndex;
use App\Events\Elastic\TreatmentLView\CreateTreatmentLViewIndex;
use App\Events\Elastic\TreatmentLView\InsertTreatmentLViewIndex;
use App\Events\Elastic\TreatmentResult\CreateTreatmentResultIndex;
use App\Events\Elastic\TreatmentResult\InsertTreatmentResultIndex;
use App\Events\Elastic\TreatmentType\CreateTreatmentTypeIndex;
use App\Events\Elastic\TreatmentType\InsertTreatmentTypeIndex;
use App\Events\Elastic\UnlimitReason\CreateUnlimitReasonIndex;
use App\Events\Elastic\UnlimitReason\InsertUnlimitReasonIndex;
use App\Events\Elastic\UserRoom\CreateUserRoomIndex;
use App\Events\Elastic\UserRoom\InsertUserRoomIndex;
use App\Events\Elastic\UserRoomVView\CreateUserRoomVViewIndex;
use App\Events\Elastic\UserRoomVView\InsertUserRoomVViewIndex;
use App\Events\Elastic\VaccineType\CreateVaccineTypeIndex;
use App\Events\Elastic\VaccineType\InsertVaccineTypeIndex;
use App\Events\Elastic\WorkPlace\CreateWorkPlaceIndex;
use App\Events\Elastic\WorkPlace\InsertWorkPlaceIndex;
use App\Events\Telegram\SendMessageToChannel;
use App\Listeners\Cache\DeleteCache as CacheDeleteCache;
use App\Listeners\Elastic\AccidentBodyPart\ElasticCreateAccidentBodyPartIndex;
use App\Listeners\Elastic\AccidentBodyPart\ElasticInsertAccidentBodyPartIndex;
use App\Listeners\Elastic\AccidentCare\ElasticCreateAccidentCareIndex;
use App\Listeners\Elastic\AccidentCare\ElasticInsertAccidentCareIndex;
use App\Listeners\Elastic\AccidentHurtType\ElasticCreateAccidentHurtTypeIndex;
use App\Listeners\Elastic\AccidentHurtType\ElasticInsertAccidentHurtTypeIndex;
use App\Listeners\Elastic\AccidentLocation\ElasticCreateAccidentLocationIndex;
use App\Listeners\Elastic\AccidentLocation\ElasticInsertAccidentLocationIndex;
use App\Listeners\Elastic\AccountBookVView\ElasticCreateAccountBookVViewIndex;
use App\Listeners\Elastic\AccountBookVView\ElasticInsertAccountBookVViewIndex;
use App\Listeners\Elastic\AgeType\ElasticCreateAgeTypeIndex;
use App\Listeners\Elastic\AgeType\ElasticInsertAgeTypeIndex;
use App\Listeners\Elastic\Area\ElasticCreateAreaIndex;
use App\Listeners\Elastic\Area\ElasticInsertAreaIndex;
use App\Listeners\Elastic\Atc\ElasticCreateAtcIndex;
use App\Listeners\Elastic\Atc\ElasticInsertAtcIndex;
use App\Listeners\Elastic\AtcGroup\ElasticCreateAtcGroupIndex;
use App\Listeners\Elastic\AtcGroup\ElasticInsertAtcGroupIndex;
use App\Listeners\Elastic\Awareness\ElasticCreateAwarenessIndex;
use App\Listeners\Elastic\Awareness\ElasticInsertAwarenessIndex;
use App\Listeners\Elastic\Bed\ElasticCreateBedIndex;
use App\Listeners\Elastic\Bed\ElasticInsertBedIndex;
use App\Listeners\Elastic\BedBsty\ElasticCreateBedBstyIndex;
use App\Listeners\Elastic\BedBsty\ElasticInsertBedBstyIndex;
use App\Listeners\Elastic\BedRoom\ElasticCreateBedRoomIndex;
use App\Listeners\Elastic\BedRoom\ElasticInsertBedRoomIndex;
use App\Listeners\Elastic\BedType\ElasticCreateBedTypeIndex;
use App\Listeners\Elastic\BedType\ElasticInsertBedTypeIndex;
use App\Listeners\Elastic\BhytBlacklist\ElasticCreateBhytBlacklistIndex;
use App\Listeners\Elastic\BhytBlacklist\ElasticInsertBhytBlacklistIndex;
use App\Listeners\Elastic\BhytParam\ElasticCreateBhytParamIndex;
use App\Listeners\Elastic\BhytParam\ElasticInsertBhytParamIndex;
use App\Listeners\Elastic\BhytWhitelist\ElasticCreateBhytWhitelistIndex;
use App\Listeners\Elastic\BhytWhitelist\ElasticInsertBhytWhitelistIndex;
use App\Listeners\Elastic\Bid\ElasticCreateBidIndex;
use App\Listeners\Elastic\Bid\ElasticInsertBidIndex;
use App\Listeners\Elastic\BidType\ElasticCreateBidTypeIndex;
use App\Listeners\Elastic\BidType\ElasticInsertBidTypeIndex;
use App\Listeners\Elastic\BloodGroup\ElasticCreateBloodGroupIndex;
use App\Listeners\Elastic\BloodGroup\ElasticInsertBloodGroupIndex;
use App\Listeners\Elastic\BloodVolume\ElasticCreateBloodVolumeIndex;
use App\Listeners\Elastic\BloodVolume\ElasticInsertBloodVolumeIndex;
use App\Listeners\Elastic\BodyPart\ElasticCreateBodyPartIndex;
use App\Listeners\Elastic\BodyPart\ElasticInsertBodyPartIndex;
use App\Listeners\Elastic\BornPosition\ElasticCreateBornPositionIndex;
use App\Listeners\Elastic\BornPosition\ElasticInsertBornPositionIndex;
use App\Listeners\Elastic\Branch\ElasticCreateBranchIndex;
use App\Listeners\Elastic\Branch\ElasticInsertBranchIndex;
use App\Listeners\Elastic\CancelReason\ElasticCreateCancelReasonIndex;
use App\Listeners\Elastic\CancelReason\ElasticInsertCancelReasonIndex;
use App\Listeners\Elastic\Career\ElasticCreateCareerIndex;
use App\Listeners\Elastic\Career\ElasticInsertCareerIndex;
use App\Listeners\Elastic\CareerTitle\ElasticCreateCareerTitleIndex;
use App\Listeners\Elastic\CareerTitle\ElasticInsertCareerTitleIndex;
use App\Listeners\Elastic\CashierRoom\ElasticCreateCashierRoomIndex;
use App\Listeners\Elastic\CashierRoom\ElasticInsertCashierRoomIndex;
use App\Listeners\Elastic\Commune\ElasticCreateCommuneIndex;
use App\Listeners\Elastic\Commune\ElasticInsertCommuneIndex;
use App\Listeners\Elastic\Contraindication\ElasticCreateContraindicationIndex;
use App\Listeners\Elastic\Contraindication\ElasticInsertContraindicationIndex;
use App\Listeners\Elastic\DataStore\ElasticCreateDataStoreIndex;
use App\Listeners\Elastic\DataStore\ElasticInsertDataStoreIndex;
use App\Listeners\Elastic\DeathCause\ElasticCreateDeathCauseIndex;
use App\Listeners\Elastic\DeathCause\ElasticInsertDeathCauseIndex;
use App\Listeners\Elastic\DeathWithin\ElasticCreateDeathWithinIndex;
use App\Listeners\Elastic\DeathWithin\ElasticInsertDeathWithinIndex;
use App\Listeners\Elastic\Debate\ElasticCreateDebateIndex;
use App\Listeners\Elastic\Debate\ElasticInsertDebateIndex;
use App\Listeners\Elastic\DebateEkipUser\ElasticCreateDebateEkipUserIndex;
use App\Listeners\Elastic\DebateEkipUser\ElasticInsertDebateEkipUserIndex;
use App\Listeners\Elastic\DebateReason\ElasticCreateDebateReasonIndex;
use App\Listeners\Elastic\DebateReason\ElasticInsertDebateReasonIndex;
use App\Listeners\Elastic\DebateType\ElasticCreateDebateTypeIndex;
use App\Listeners\Elastic\DebateType\ElasticInsertDebateTypeIndex;
use App\Listeners\Elastic\DebateUser\ElasticCreateDebateUserIndex;
use App\Listeners\Elastic\DebateUser\ElasticInsertDebateUserIndex;
use App\Listeners\Elastic\DebateVView\ElasticCreateDebateVViewIndex;
use App\Listeners\Elastic\DebateVView\ElasticInsertDebateVViewIndex;
use App\Listeners\Elastic\Department\ElasticCreateDepartmentIndex;
use App\Listeners\Elastic\Department\ElasticInsertDepartmentIndex;
use App\Listeners\Elastic\Dhst\ElasticCreateDhstIndex;
use App\Listeners\Elastic\Dhst\ElasticInsertDhstIndex;
use App\Listeners\Elastic\DiimType\ElasticCreateDiimTypeIndex;
use App\Listeners\Elastic\DiimType\ElasticInsertDiimTypeIndex;
use App\Listeners\Elastic\District\ElasticCreateDistrictIndex;
use App\Listeners\Elastic\District\ElasticInsertDistrictIndex;
use App\Listeners\Elastic\DocumentType\ElasticCreateDocumentTypeIndex;
use App\Listeners\Elastic\DocumentType\ElasticInsertDocumentTypeIndex;
use App\Listeners\Elastic\DosageForm\ElasticCreateDosageFormIndex;
use App\Listeners\Elastic\DosageForm\ElasticInsertDosageFormIndex;
use App\Listeners\Elastic\ElasticDeleteIndex;
use App\Listeners\Elastic\EmotionlessMethod\ElasticCreateEmotionlessMethodIndex;
use App\Listeners\Elastic\EmotionlessMethod\ElasticInsertEmotionlessMethodIndex;
use App\Listeners\Elastic\Employee\ElasticCreateEmployeeIndex;
use App\Listeners\Elastic\Employee\ElasticInsertEmployeeIndex;
use App\Listeners\Elastic\EmrCoverType\ElasticCreateEmrCoverTypeIndex;
use App\Listeners\Elastic\EmrCoverType\ElasticInsertEmrCoverTypeIndex;
use App\Listeners\Elastic\EmrForm\ElasticCreateEmrFormIndex;
use App\Listeners\Elastic\EmrForm\ElasticInsertEmrFormIndex;
use App\Listeners\Elastic\Ethnic\ElasticCreateEthnicIndex;
use App\Listeners\Elastic\Ethnic\ElasticInsertEthnicIndex;
use App\Listeners\Elastic\ExecuteGroup\ElasticCreateExecuteGroupIndex;
use App\Listeners\Elastic\ExecuteGroup\ElasticInsertExecuteGroupIndex;
use App\Listeners\Elastic\ExecuteRole\ElasticCreateExecuteRoleIndex;
use App\Listeners\Elastic\ExecuteRole\ElasticInsertExecuteRoleIndex;
use App\Listeners\Elastic\ExecuteRoleUser\ElasticCreateExecuteRoleUserIndex;
use App\Listeners\Elastic\ExecuteRoleUser\ElasticInsertExecuteRoleUserIndex;
use App\Listeners\Elastic\ExecuteRoom\ElasticCreateExecuteRoomIndex;
use App\Listeners\Elastic\ExecuteRoom\ElasticInsertExecuteRoomIndex;
use App\Listeners\Elastic\ExeServiceModule\ElasticCreateExeServiceModuleIndex;
use App\Listeners\Elastic\ExeServiceModule\ElasticInsertExeServiceModuleIndex;
use App\Listeners\Elastic\ExpMestReason\ElasticCreateExpMestReasonIndex;
use App\Listeners\Elastic\ExpMestReason\ElasticInsertExpMestReasonIndex;
use App\Listeners\Elastic\ExroRoom\ElasticCreateExroRoomIndex;
use App\Listeners\Elastic\ExroRoom\ElasticInsertExroRoomIndex;
use App\Listeners\Elastic\FileType\ElasticCreateFileTypeIndex;
use App\Listeners\Elastic\FileType\ElasticInsertFileTypeIndex;
use App\Listeners\Elastic\FilmSize\ElasticCreateFilmSizeIndex;
use App\Listeners\Elastic\FilmSize\ElasticInsertFilmSizeIndex;
use App\Listeners\Elastic\FuexType\ElasticCreateFuexTypeIndex;
use App\Listeners\Elastic\FuexType\ElasticInsertFuexTypeIndex;
use App\Listeners\Elastic\Gender\ElasticCreateGenderIndex;
use App\Listeners\Elastic\Gender\ElasticInsertGenderIndex;
use App\Listeners\Elastic\Group\ElasticCreateGroupIndex;
use App\Listeners\Elastic\Group\ElasticInsertGroupIndex;
use App\Listeners\Elastic\GroupType\ElasticCreateGroupTypeIndex;
use App\Listeners\Elastic\GroupType\ElasticInsertGroupTypeIndex;
use App\Listeners\Elastic\HeinServiceType\ElasticCreateHeinServiceTypeIndex;
use App\Listeners\Elastic\HeinServiceType\ElasticInsertHeinServiceTypeIndex;
use App\Listeners\Elastic\HospitalizeReason\ElasticCreateHospitalizeReasonIndex;
use App\Listeners\Elastic\HospitalizeReason\ElasticInsertHospitalizeReasonIndex;
use App\Listeners\Elastic\Htu\ElasticCreateHtuIndex;
use App\Listeners\Elastic\Htu\ElasticInsertHtuIndex;
use App\Listeners\Elastic\Icd\ElasticCreateIcdIndex;
use App\Listeners\Elastic\Icd\ElasticInsertIcdIndex;
use App\Listeners\Elastic\IcdCm\ElasticCreateIcdCmIndex;
use App\Listeners\Elastic\IcdCm\ElasticInsertIcdCmIndex;
use App\Listeners\Elastic\IcdGroup\ElasticCreateIcdGroupIndex;
use App\Listeners\Elastic\IcdGroup\ElasticInsertIcdGroupIndex;
use App\Listeners\Elastic\IcdListVView\ElasticCreateIcdListVViewIndex;
use App\Listeners\Elastic\IcdListVView\ElasticInsertIcdListVViewIndex;
use App\Listeners\Elastic\ImpSource\ElasticCreateImpSourceIndex;
use App\Listeners\Elastic\ImpSource\ElasticInsertImpSourceIndex;
use App\Listeners\Elastic\InteractionReason\ElasticCreateInteractionReasonIndex;
use App\Listeners\Elastic\InteractionReason\ElasticInsertInteractionReasonIndex;
use App\Listeners\Elastic\LicenseClass\ElasticCreateLicenseClassIndex;
use App\Listeners\Elastic\LicenseClass\ElasticInsertLicenseClassIndex;
use App\Listeners\Elastic\LocationStore\ElasticCreateLocationStoreIndex;
use App\Listeners\Elastic\LocationStore\ElasticInsertLocationStoreIndex;
use App\Listeners\Elastic\Machine\ElasticCreateMachineIndex;
use App\Listeners\Elastic\Machine\ElasticInsertMachineIndex;
use App\Listeners\Elastic\Manufacturer\ElasticCreateManufacturerIndex;
use App\Listeners\Elastic\Manufacturer\ElasticInsertManufacturerIndex;
use App\Listeners\Elastic\MaterialType\ElasticCreateMaterialTypeIndex;
use App\Listeners\Elastic\MaterialType\ElasticInsertMaterialTypeIndex;
use App\Listeners\Elastic\MaterialTypeMap\ElasticCreateMaterialTypeMapIndex;
use App\Listeners\Elastic\MaterialTypeMap\ElasticInsertMaterialTypeMapIndex;
use App\Listeners\Elastic\MedicalContract\ElasticCreateMedicalContractIndex;
use App\Listeners\Elastic\MedicalContract\ElasticInsertMedicalContractIndex;
use App\Listeners\Elastic\Medicine\ElasticCreateMedicineIndex;
use App\Listeners\Elastic\Medicine\ElasticInsertMedicineIndex;
use App\Listeners\Elastic\MedicineGroup\ElasticCreateMedicineGroupIndex;
use App\Listeners\Elastic\MedicineGroup\ElasticInsertMedicineGroupIndex;
use App\Listeners\Elastic\MedicineLine\ElasticCreateMedicineLineIndex;
use App\Listeners\Elastic\MedicineLine\ElasticInsertMedicineLineIndex;
use App\Listeners\Elastic\MedicinePaty\ElasticCreateMedicinePatyIndex;
use App\Listeners\Elastic\MedicinePaty\ElasticInsertMedicinePatyIndex;
use App\Listeners\Elastic\MedicineType\ElasticCreateMedicineTypeIndex;
use App\Listeners\Elastic\MedicineType\ElasticInsertMedicineTypeIndex;
use App\Listeners\Elastic\MedicineTypeAcin\ElasticCreateMedicineTypeAcinIndex;
use App\Listeners\Elastic\MedicineTypeAcin\ElasticInsertMedicineTypeAcinIndex;
use App\Listeners\Elastic\MedicineUseForm\ElasticCreateMedicineUseFormIndex;
use App\Listeners\Elastic\MedicineUseForm\ElasticInsertMedicineUseFormIndex;
use App\Listeners\Elastic\MediOrg\ElasticCreateMediOrgIndex;
use App\Listeners\Elastic\MediOrg\ElasticInsertMediOrgIndex;
use App\Listeners\Elastic\MediRecordType\ElasticCreateMediRecordTypeIndex;
use App\Listeners\Elastic\MediRecordType\ElasticInsertMediRecordTypeIndex;
use App\Listeners\Elastic\MediStock\ElasticCreateMediStockIndex;
use App\Listeners\Elastic\MediStock\ElasticInsertMediStockIndex;
use App\Listeners\Elastic\MediStockMaty\ElasticCreateMediStockMatyIndex;
use App\Listeners\Elastic\MediStockMaty\ElasticInsertMediStockMatyIndex;
use App\Listeners\Elastic\MediStockMety\ElasticCreateMediStockMetyIndex;
use App\Listeners\Elastic\MediStockMety\ElasticInsertMediStockMetyIndex;
use App\Listeners\Elastic\MemaGroup\ElasticCreateMemaGroupIndex;
use App\Listeners\Elastic\MemaGroup\ElasticInsertMemaGroupIndex;
use App\Listeners\Elastic\MestPatientType\ElasticCreateMestPatientTypeIndex;
use App\Listeners\Elastic\MestPatientType\ElasticInsertMestPatientTypeIndex;
use App\Listeners\Elastic\MestRoom\ElasticCreateMestRoomIndex;
use App\Listeners\Elastic\MestRoom\ElasticInsertMestRoomIndex;
use App\Listeners\Elastic\MilitaryRank\ElasticCreateMilitaryRankIndex;
use App\Listeners\Elastic\MilitaryRank\ElasticInsertMilitaryRankIndex;
use App\Listeners\Elastic\Module\ElasticCreateModuleIndex;
use App\Listeners\Elastic\Module\ElasticInsertModuleIndex;
use App\Listeners\Elastic\ModuleRole\ElasticCreateModuleRoleIndex;
use App\Listeners\Elastic\ModuleRole\ElasticInsertModuleRoleIndex;
use App\Listeners\Elastic\National\ElasticCreateNationalIndex;
use App\Listeners\Elastic\National\ElasticInsertNationalIndex;
use App\Listeners\Elastic\OtherPaySource\ElasticCreateOtherPaySourceIndex;
use App\Listeners\Elastic\OtherPaySource\ElasticInsertOtherPaySourceIndex;
use App\Listeners\Elastic\Package\ElasticCreatePackageIndex;
use App\Listeners\Elastic\Package\ElasticInsertPackageIndex;
use App\Listeners\Elastic\PackingType\ElasticCreatePackingTypeIndex;
use App\Listeners\Elastic\PackingType\ElasticInsertPackingTypeIndex;
use App\Listeners\Elastic\PatientCase\ElasticCreatePatientCaseIndex;
use App\Listeners\Elastic\PatientCase\ElasticInsertPatientCaseIndex;
use App\Listeners\Elastic\PatientClassify\ElasticCreatePatientClassifyIndex;
use App\Listeners\Elastic\PatientClassify\ElasticInsertPatientClassifyIndex;
use App\Listeners\Elastic\PatientType\ElasticCreatePatientTypeIndex;
use App\Listeners\Elastic\PatientType\ElasticInsertPatientTypeIndex;
use App\Listeners\Elastic\PatientTypeAllow\ElasticCreatePatientTypeAllowIndex;
use App\Listeners\Elastic\PatientTypeAllow\ElasticInsertPatientTypeAllowIndex;
use App\Listeners\Elastic\PatientTypeAlterVView\ElasticCreatePatientTypeAlterVViewIndex;
use App\Listeners\Elastic\PatientTypeAlterVView\ElasticInsertPatientTypeAlterVViewIndex;
use App\Listeners\Elastic\PatientTypeRoom\ElasticCreatePatientTypeRoomIndex;
use App\Listeners\Elastic\PatientTypeRoom\ElasticInsertPatientTypeRoomIndex;
use App\Listeners\Elastic\PayForm\ElasticCreatePayFormIndex;
use App\Listeners\Elastic\PayForm\ElasticInsertPayFormIndex;
use App\Listeners\Elastic\Position\ElasticCreatePositionIndex;
use App\Listeners\Elastic\Position\ElasticInsertPositionIndex;
use App\Listeners\Elastic\PreparationsBlood\ElasticCreatePreparationsBloodIndex;
use App\Listeners\Elastic\PreparationsBlood\ElasticInsertPreparationsBloodIndex;
use App\Listeners\Elastic\PriorityType\ElasticCreatePriorityTypeIndex;
use App\Listeners\Elastic\PriorityType\ElasticInsertPriorityTypeIndex;
use App\Listeners\Elastic\ProcessingMethod\ElasticCreateProcessingMethodIndex;
use App\Listeners\Elastic\ProcessingMethod\ElasticInsertProcessingMethodIndex;
use App\Listeners\Elastic\Province\ElasticCreateProvinceIndex;
use App\Listeners\Elastic\Province\ElasticInsertProvinceIndex;
use App\Listeners\Elastic\PtttCatastrophe\ElasticCreatePtttCatastropheIndex;
use App\Listeners\Elastic\PtttCatastrophe\ElasticInsertPtttCatastropheIndex;
use App\Listeners\Elastic\PtttCondition\ElasticCreatePtttConditionIndex;
use App\Listeners\Elastic\PtttCondition\ElasticInsertPtttConditionIndex;
use App\Listeners\Elastic\PtttGroup\ElasticCreatePtttGroupIndex;
use App\Listeners\Elastic\PtttGroup\ElasticInsertPtttGroupIndex;
use App\Listeners\Elastic\PtttMethod\ElasticCreatePtttMethodIndex;
use App\Listeners\Elastic\PtttMethod\ElasticInsertPtttMethodIndex;
use App\Listeners\Elastic\PtttTable\ElasticCreatePtttTableIndex;
use App\Listeners\Elastic\PtttTable\ElasticInsertPtttTableIndex;
use App\Listeners\Elastic\RationGroup\ElasticCreateRationGroupIndex;
use App\Listeners\Elastic\RationGroup\ElasticInsertRationGroupIndex;
use App\Listeners\Elastic\RationTime\ElasticCreateRationTimeIndex;
use App\Listeners\Elastic\RationTime\ElasticInsertRationTimeIndex;
use App\Listeners\Elastic\ReceptionRoom\ElasticCreateReceptionRoomIndex;
use App\Listeners\Elastic\ReceptionRoom\ElasticInsertReceptionRoomIndex;
use App\Listeners\Elastic\Refectory\ElasticCreateRefectoryIndex;
use App\Listeners\Elastic\Refectory\ElasticInsertRefectoryIndex;
use App\Listeners\Elastic\Relation\ElasticCreateRelationIndex;
use App\Listeners\Elastic\Relation\ElasticInsertRelationIndex;
use App\Listeners\Elastic\Religion\ElasticCreateReligionIndex;
use App\Listeners\Elastic\Religion\ElasticInsertReligionIndex;
use App\Listeners\Elastic\Role\ElasticCreateRoleIndex;
use App\Listeners\Elastic\Role\ElasticInsertRoleIndex;
use App\Listeners\Elastic\Room\ElasticCreateRoomIndex;
use App\Listeners\Elastic\Room\ElasticInsertRoomIndex;
use App\Listeners\Elastic\RoomGroup\ElasticCreateRoomGroupIndex;
use App\Listeners\Elastic\RoomGroup\ElasticInsertRoomGroupIndex;
use App\Listeners\Elastic\RoomType\ElasticCreateRoomTypeIndex;
use App\Listeners\Elastic\RoomType\ElasticInsertRoomTypeIndex;
use App\Listeners\Elastic\RoomVView\ElasticCreateRoomVViewIndex;
use App\Listeners\Elastic\RoomVView\ElasticInsertRoomVViewIndex;
use App\Listeners\Elastic\SaleProfitCfg\ElasticCreateSaleProfitCfgIndex;
use App\Listeners\Elastic\SaleProfitCfg\ElasticInsertSaleProfitCfgIndex;
use App\Listeners\Elastic\SereServ\ElasticCreateSereServIndex;
use App\Listeners\Elastic\SereServ\ElasticInsertSereServIndex;
use App\Listeners\Elastic\SereServBill\ElasticCreateSereServBillIndex;
use App\Listeners\Elastic\SereServBill\ElasticInsertSereServBillIndex;
use App\Listeners\Elastic\SereServDepositVView\ElasticCreateSereServDepositVViewIndex;
use App\Listeners\Elastic\SereServDepositVView\ElasticInsertSereServDepositVViewIndex;
use App\Listeners\Elastic\SereServExt\ElasticCreateSereServExtIndex;
use App\Listeners\Elastic\SereServExt\ElasticInsertSereServExtIndex;
use App\Listeners\Elastic\SereServTein\ElasticCreateSereServTeinIndex;
use App\Listeners\Elastic\SereServTein\ElasticInsertSereServTeinIndex;
use App\Listeners\Elastic\SereServTeinVView\ElasticCreateSereServTeinVViewIndex;
use App\Listeners\Elastic\SereServTeinVView\ElasticInsertSereServTeinVViewIndex;
use App\Listeners\Elastic\SereServVView4\ElasticCreateSereServVView4Index;
use App\Listeners\Elastic\SereServVView4\ElasticInsertSereServVView4Index;
use App\Listeners\Elastic\Service\ElasticCreateServiceIndex;
use App\Listeners\Elastic\Service\ElasticInsertServiceIndex;
use App\Listeners\Elastic\ServiceCondition\ElasticCreateServiceConditionIndex;
use App\Listeners\Elastic\ServiceCondition\ElasticInsertServiceConditionIndex;
use App\Listeners\Elastic\ServiceFollow\ElasticCreateServiceFollowIndex;
use App\Listeners\Elastic\ServiceFollow\ElasticInsertServiceFollowIndex;
use App\Listeners\Elastic\ServiceGroup\ElasticCreateServiceGroupIndex;
use App\Listeners\Elastic\ServiceGroup\ElasticInsertServiceGroupIndex;
use App\Listeners\Elastic\ServiceMachine\ElasticCreateServiceMachineIndex;
use App\Listeners\Elastic\ServiceMachine\ElasticInsertServiceMachineIndex;
use App\Listeners\Elastic\ServicePaty\ElasticCreateServicePatyIndex;
use App\Listeners\Elastic\ServicePaty\ElasticInsertServicePatyIndex;
use App\Listeners\Elastic\ServiceReq\ElasticCreateServiceReqIndex;
use App\Listeners\Elastic\ServiceReq\ElasticInsertServiceReqIndex;
use App\Listeners\Elastic\ServiceReqLView\ElasticCreateServiceReqLViewIndex;
use App\Listeners\Elastic\ServiceReqLView\ElasticInsertServiceReqLViewIndex;
use App\Listeners\Elastic\ServiceReqStt\ElasticCreateServiceReqSttIndex;
use App\Listeners\Elastic\ServiceReqStt\ElasticInsertServiceReqSttIndex;
use App\Listeners\Elastic\ServiceReqType\ElasticCreateServiceReqTypeIndex;
use App\Listeners\Elastic\ServiceReqType\ElasticInsertServiceReqTypeIndex;
use App\Listeners\Elastic\ServiceRoom\ElasticCreateServiceRoomIndex;
use App\Listeners\Elastic\ServiceRoom\ElasticInsertServiceRoomIndex;
use App\Listeners\Elastic\ServiceType\ElasticCreateServiceTypeIndex;
use App\Listeners\Elastic\ServiceType\ElasticInsertServiceTypeIndex;
use App\Listeners\Elastic\ServiceUnit\ElasticCreateServiceUnitIndex;
use App\Listeners\Elastic\ServiceUnit\ElasticInsertServiceUnitIndex;
use App\Listeners\Elastic\ServSegr\ElasticCreateServSegrIndex;
use App\Listeners\Elastic\ServSegr\ElasticInsertServSegrIndex;
use App\Listeners\Elastic\SeseDepoRepayVView\ElasticCreateSeseDepoRepayVViewIndex;
use App\Listeners\Elastic\SeseDepoRepayVView\ElasticInsertSeseDepoRepayVViewIndex;
use App\Listeners\Elastic\Speciality\ElasticCreateSpecialityIndex;
use App\Listeners\Elastic\Speciality\ElasticInsertSpecialityIndex;
use App\Listeners\Elastic\StorageCondition\ElasticCreateStorageConditionIndex;
use App\Listeners\Elastic\StorageCondition\ElasticInsertStorageConditionIndex;
use App\Listeners\Elastic\SuimIndex\ElasticCreateSuimIndexIndex;
use App\Listeners\Elastic\SuimIndex\ElasticInsertSuimIndexIndex;
use App\Listeners\Elastic\SuimIndexUnit\ElasticCreateSuimIndexUnitIndex;
use App\Listeners\Elastic\SuimIndexUnit\ElasticInsertSuimIndexUnitIndex;
use App\Listeners\Elastic\Supplier\ElasticCreateSupplierIndex;
use App\Listeners\Elastic\Supplier\ElasticInsertSupplierIndex;
use App\Listeners\Elastic\TestIndex\ElasticCreateTestIndexIndex;
use App\Listeners\Elastic\TestIndex\ElasticInsertTestIndexIndex;
use App\Listeners\Elastic\TestIndexGroup\ElasticCreateTestIndexGroupIndex;
use App\Listeners\Elastic\TestIndexGroup\ElasticInsertTestIndexGroupIndex;
use App\Listeners\Elastic\TestIndexUnit\ElasticCreateTestIndexUnitIndex;
use App\Listeners\Elastic\TestIndexUnit\ElasticInsertTestIndexUnitIndex;
use App\Listeners\Elastic\TestSampleType\ElasticCreateTestSampleTypeIndex;
use App\Listeners\Elastic\TestSampleType\ElasticInsertTestSampleTypeIndex;
use App\Listeners\Elastic\TestServiceReqListVView\ElasticCreateTestServiceReqListVViewIndex;
use App\Listeners\Elastic\TestServiceReqListVView\ElasticInsertTestServiceReqListVViewIndex;
use App\Listeners\Elastic\TestType\ElasticCreateTestTypeIndex;
use App\Listeners\Elastic\TestType\ElasticInsertTestTypeIndex;
use App\Listeners\Elastic\Tracking\ElasticCreateTrackingIndex;
use App\Listeners\Elastic\Tracking\ElasticInsertTrackingIndex;
use App\Listeners\Elastic\TranPatiForm\ElasticCreateTranPatiFormIndex;
use App\Listeners\Elastic\TranPatiForm\ElasticInsertTranPatiFormIndex;
use App\Listeners\Elastic\TranPatiTech\ElasticCreateTranPatiTechIndex;
use App\Listeners\Elastic\TranPatiTech\ElasticInsertTranPatiTechIndex;
use App\Listeners\Elastic\TransactionType\ElasticCreateTransactionTypeIndex;
use App\Listeners\Elastic\TransactionType\ElasticInsertTransactionTypeIndex;
use App\Listeners\Elastic\TreatmentBedRoomLView\ElasticCreateTreatmentBedRoomLViewIndex;
use App\Listeners\Elastic\TreatmentBedRoomLView\ElasticInsertTreatmentBedRoomLViewIndex;
use App\Listeners\Elastic\TreatmentEndType\ElasticCreateTreatmentEndTypeIndex;
use App\Listeners\Elastic\TreatmentEndType\ElasticInsertTreatmentEndTypeIndex;
use App\Listeners\Elastic\TreatmentFeeView\ElasticCreateTreatmentFeeViewIndex;
use App\Listeners\Elastic\TreatmentFeeView\ElasticInsertTreatmentFeeViewIndex;
use App\Listeners\Elastic\TreatmentLView\ElasticCreateTreatmentLViewIndex;
use App\Listeners\Elastic\TreatmentLView\ElasticInsertTreatmentLViewIndex;
use App\Listeners\Elastic\TreatmentResult\ElasticCreateTreatmentResultIndex;
use App\Listeners\Elastic\TreatmentResult\ElasticInsertTreatmentResultIndex;
use App\Listeners\Elastic\TreatmentType\ElasticCreateTreatmentTypeIndex;
use App\Listeners\Elastic\TreatmentType\ElasticInsertTreatmentTypeIndex;
use App\Listeners\Elastic\UnlimitReason\ElasticCreateUnlimitReasonIndex;
use App\Listeners\Elastic\UnlimitReason\ElasticInsertUnlimitReasonIndex;
use App\Listeners\Elastic\UserRoom\ElasticCreateUserRoomIndex;
use App\Listeners\Elastic\UserRoom\ElasticInsertUserRoomIndex;
use App\Listeners\Elastic\UserRoomVView\ElasticCreateUserRoomVViewIndex;
use App\Listeners\Elastic\UserRoomVView\ElasticInsertUserRoomVViewIndex;
use App\Listeners\Elastic\VaccineType\ElasticCreateVaccineTypeIndex;
use App\Listeners\Elastic\VaccineType\ElasticInsertVaccineTypeIndex;
use App\Listeners\Elastic\WorkPlace\ElasticCreateWorkPlaceIndex;
use App\Listeners\Elastic\WorkPlace\ElasticInsertWorkPlaceIndex;
use App\Listeners\Telegram\TelegramSendMessageToChannel;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],

        // Cache
        DeleteCache::class => [
            CacheDeleteCache::class,
        ],

        // Telegram
        SendMessageToChannel::class => [
            TelegramSendMessageToChannel::class,
        ],

        // Elastic Search
        DeleteIndex::class => [
            ElasticDeleteIndex::class,
        ],

        CreateAccidentBodyPartIndex::class => [
            ElasticCreateAccidentBodyPartIndex::class,
        ],
        InsertAccidentBodyPartIndex::class => [
            ElasticInsertAccidentBodyPartIndex::class,
        ],

        CreateAccidentCareIndex::class => [
            ElasticCreateAccidentCareIndex::class,
        ],
        InsertAccidentCareIndex::class => [
            ElasticInsertAccidentCareIndex::class,
        ],

        CreateAccidentHurtTypeIndex::class => [
            ElasticCreateAccidentHurtTypeIndex::class,
        ],
        InsertAccidentHurtTypeIndex::class => [
            ElasticInsertAccidentHurtTypeIndex::class,
        ],

        CreateAccidentLocationIndex::class => [
            ElasticCreateAccidentLocationIndex::class,
        ],
        InsertAccidentLocationIndex::class => [
            ElasticInsertAccidentLocationIndex::class,
        ],

        CreateAgeTypeIndex::class => [
            ElasticCreateAgeTypeIndex::class,
        ],
        InsertAgeTypeIndex::class => [
            ElasticInsertAgeTypeIndex::class,
        ],
        
        CreateAreaIndex::class => [
            ElasticCreateAreaIndex::class,
        ],
        InsertAreaIndex::class => [
            ElasticInsertAreaIndex::class,
        ],

        CreateAtcGroupIndex::class => [
            ElasticCreateAtcGroupIndex::class,
        ],
        InsertAtcGroupIndex::class => [
            ElasticInsertAtcGroupIndex::class,
        ],

        CreateAwarenessIndex::class => [
            ElasticCreateAwarenessIndex::class,
        ],
        InsertAwarenessIndex::class => [
            ElasticInsertAwarenessIndex::class,
        ],

        CreateBedBstyIndex::class => [
            ElasticCreateBedBstyIndex::class,
        ],
        InsertBedBstyIndex::class => [
            ElasticInsertBedBstyIndex::class,
        ],

        CreateBedIndex::class => [
            ElasticCreateBedIndex::class,
        ],
        InsertBedIndex::class => [
            ElasticInsertBedIndex::class,
        ],

        CreateBedRoomIndex::class => [
            ElasticCreateBedRoomIndex::class,
        ],
        InsertBedRoomIndex::class => [
            ElasticInsertBedRoomIndex::class,
        ],

        CreateBedTypeIndex::class => [
            ElasticCreateBedTypeIndex::class,
        ],
        InsertBedTypeIndex::class => [
            ElasticInsertBedTypeIndex::class,
        ],

        CreateBhytBlacklistIndex::class => [
            ElasticCreateBhytBlacklistIndex::class,
        ],
        InsertBhytBlacklistIndex::class => [
            ElasticInsertBhytBlacklistIndex::class,
        ],

        CreateBhytParamIndex::class => [
            ElasticCreateBhytParamIndex::class,
        ],
        InsertBhytParamIndex::class => [
            ElasticInsertBhytParamIndex::class,
        ],

        CreateBhytWhitelistIndex::class => [
            ElasticCreateBhytWhitelistIndex::class,
        ],
        InsertBhytWhitelistIndex::class => [
            ElasticInsertBhytWhitelistIndex::class,
        ],

        CreateBidTypeIndex::class => [
            ElasticCreateBidTypeIndex::class,
        ],
        InsertBidTypeIndex::class => [
            ElasticInsertBidTypeIndex::class,
        ],

        CreateBloodGroupIndex::class => [
            ElasticCreateBloodGroupIndex::class,
        ],
        InsertBloodGroupIndex::class => [
            ElasticInsertBloodGroupIndex::class,
        ],

        CreateBloodVolumeIndex::class => [
            ElasticCreateBloodVolumeIndex::class,
        ],
        InsertBloodVolumeIndex::class => [
            ElasticInsertBloodVolumeIndex::class,
        ],

        CreateBodyPartIndex::class => [
            ElasticCreateBodyPartIndex::class,
        ],
        InsertBodyPartIndex::class => [
            ElasticInsertBodyPartIndex::class,
        ],

        CreateBornPositionIndex::class => [
            ElasticCreateBornPositionIndex::class,
        ],
        InsertBornPositionIndex::class => [
            ElasticInsertBornPositionIndex::class,
        ],

        CreateBranchIndex::class => [
            ElasticCreateBranchIndex::class,
        ],
        InsertBranchIndex::class => [
            ElasticInsertBranchIndex::class,
        ],

        CreateCancelReasonIndex::class => [
            ElasticCreateCancelReasonIndex::class,
        ],
        InsertCancelReasonIndex::class => [
            ElasticInsertCancelReasonIndex::class,
        ],

        CreateCareerIndex::class => [
            ElasticCreateCareerIndex::class,
        ],
        InsertCareerIndex::class => [
            ElasticInsertCareerIndex::class,
        ],

        CreateCareerTitleIndex::class => [
            ElasticCreateCareerTitleIndex::class,
        ],
        InsertCareerTitleIndex::class => [
            ElasticInsertCareerTitleIndex::class,
        ],

        CreateCashierRoomIndex::class => [
            ElasticCreateCashierRoomIndex::class,
        ],
        InsertCashierRoomIndex::class => [
            ElasticInsertCashierRoomIndex::class,
        ],

        CreateCommuneIndex::class => [
            ElasticCreateCommuneIndex::class,
        ],
        InsertCommuneIndex::class => [
            ElasticInsertCommuneIndex::class,
        ],

        CreateContraindicationIndex::class => [
            ElasticCreateContraindicationIndex::class,
        ],
        InsertContraindicationIndex::class => [
            ElasticInsertContraindicationIndex::class,
        ],

        CreateDataStoreIndex::class => [
            ElasticCreateDataStoreIndex::class,
        ],
        InsertDataStoreIndex::class => [
            ElasticInsertDataStoreIndex::class,
        ],

        CreateDeathWithinIndex::class => [
            ElasticCreateDeathWithinIndex::class,
        ],
        InsertDeathWithinIndex::class => [
            ElasticInsertDeathWithinIndex::class,
        ],

        CreateDebateReasonIndex::class => [
            ElasticCreateDebateReasonIndex::class,
        ],
        InsertDebateReasonIndex::class => [
            ElasticInsertDebateReasonIndex::class,
        ],

        CreateDebateTypeIndex::class => [
            ElasticCreateDebateTypeIndex::class,
        ],
        InsertDebateTypeIndex::class => [
            ElasticInsertDebateTypeIndex::class,
        ],

        CreateDepartmentIndex::class => [
            ElasticCreateDepartmentIndex::class,
        ],
        InsertDepartmentIndex::class => [
            ElasticInsertDepartmentIndex::class,
        ],

        CreateDiimTypeIndex::class => [
            ElasticCreateDiimTypeIndex::class,
        ],
        InsertDiimTypeIndex::class => [
            ElasticInsertDiimTypeIndex::class,
        ],

        CreateDistrictIndex::class => [
            ElasticCreateDistrictIndex::class,
        ],
        InsertDistrictIndex::class => [
            ElasticInsertDistrictIndex::class,
        ],

        CreateDosageFormIndex::class => [
            ElasticCreateDosageFormIndex::class,
        ],
        InsertDosageFormIndex::class => [
            ElasticInsertDosageFormIndex::class,
        ],

        CreateEmotionlessMethodIndex::class => [
            ElasticCreateEmotionlessMethodIndex::class,
        ],
        InsertEmotionlessMethodIndex::class => [
            ElasticInsertEmotionlessMethodIndex::class,
        ],

        CreateEmployeeIndex::class => [
            ElasticCreateEmployeeIndex::class,
        ],
        InsertEmployeeIndex::class => [
            ElasticInsertEmployeeIndex::class,
        ],

        CreateEthnicIndex::class => [
            ElasticCreateEthnicIndex::class,
        ],
        InsertEthnicIndex::class => [
            ElasticInsertEthnicIndex::class,
        ],

        CreateExecuteGroupIndex::class => [
            ElasticCreateExecuteGroupIndex::class,
        ],
        InsertExecuteGroupIndex::class => [
            ElasticInsertExecuteGroupIndex::class,
        ],

        CreateExecuteRoleIndex::class => [
            ElasticCreateExecuteRoleIndex::class,
        ],
        InsertExecuteRoleIndex::class => [
            ElasticInsertExecuteRoleIndex::class,
        ],

        CreateExecuteRoleUserIndex::class => [
            ElasticCreateExecuteRoleUserIndex::class,
        ],
        InsertExecuteRoleUserIndex::class => [
            ElasticInsertExecuteRoleUserIndex::class,
        ],

        CreateExecuteRoomIndex::class => [
            ElasticCreateExecuteRoomIndex::class,
        ],
        InsertExecuteRoomIndex::class => [
            ElasticInsertExecuteRoomIndex::class,
        ],

        CreateExeServiceModuleIndex::class => [
            ElasticCreateExeServiceModuleIndex::class,
        ],
        InsertExeServiceModuleIndex::class => [
            ElasticInsertExeServiceModuleIndex::class,
        ],

        CreateExpMestReasonIndex::class => [
            ElasticCreateExpMestReasonIndex::class,
        ],
        InsertExpMestReasonIndex::class => [
            ElasticInsertExpMestReasonIndex::class,
        ],

        CreateExroRoomIndex::class => [
            ElasticCreateExroRoomIndex::class,
        ],
        InsertExroRoomIndex::class => [
            ElasticInsertExroRoomIndex::class,
        ],

        CreateFileTypeIndex::class => [
            ElasticCreateFileTypeIndex::class,
        ],
        InsertFileTypeIndex::class => [
            ElasticInsertFileTypeIndex::class,
        ],

        CreateFilmSizeIndex::class => [
            ElasticCreateFilmSizeIndex::class,
        ],
        InsertFilmSizeIndex::class => [
            ElasticInsertFilmSizeIndex::class,
        ],
    
        CreateFuexTypeIndex::class => [
            ElasticCreateFuexTypeIndex::class,
        ],
        InsertFuexTypeIndex::class => [
            ElasticInsertFuexTypeIndex::class,
        ],

        CreateGenderIndex::class => [
            ElasticCreateGenderIndex::class,
        ],
        InsertGenderIndex::class => [
            ElasticInsertGenderIndex::class,
        ],

        CreateGroupIndex::class => [
            ElasticCreateGroupIndex::class,
        ],
        InsertGroupIndex::class => [
            ElasticInsertGroupIndex::class,
        ],

        CreateHeinServiceTypeIndex::class => [
            ElasticCreateHeinServiceTypeIndex::class,
        ],
        InsertHeinServiceTypeIndex::class => [
            ElasticInsertHeinServiceTypeIndex::class,
        ],

        CreateHospitalizeReasonIndex::class => [
            ElasticCreateHospitalizeReasonIndex::class,
        ],
        InsertHospitalizeReasonIndex::class => [
            ElasticInsertHospitalizeReasonIndex::class,
        ],

        CreateIcdCmIndex::class => [
            ElasticCreateIcdCmIndex::class,
        ],
        InsertIcdCmIndex::class => [
            ElasticInsertIcdCmIndex::class,
        ],

        CreateIcdIndex::class => [
            ElasticCreateIcdIndex::class,
        ],
        InsertIcdIndex::class => [
            ElasticInsertIcdIndex::class,
        ],

        CreateIcdGroupIndex::class => [
            ElasticCreateIcdGroupIndex::class,
        ],
        InsertIcdGroupIndex::class => [
            ElasticInsertIcdGroupIndex::class,
        ],

        CreateInteractionReasonIndex::class => [
            ElasticCreateInteractionReasonIndex::class,
        ],
        InsertInteractionReasonIndex::class => [
            ElasticInsertInteractionReasonIndex::class,
        ],

        CreateLicenseClassIndex::class => [
            ElasticCreateLicenseClassIndex::class,
        ],
        InsertLicenseClassIndex::class => [
            ElasticInsertLicenseClassIndex::class,
        ],

        CreateLocationStoreIndex::class => [
            ElasticCreateLocationStoreIndex::class,
        ],
        InsertLocationStoreIndex::class => [
            ElasticInsertLocationStoreIndex::class,
        ],

        CreateMachineIndex::class => [
            ElasticCreateMachineIndex::class,
        ],
        InsertMachineIndex::class => [
            ElasticInsertMachineIndex::class,
        ],

        CreateManufacturerIndex::class => [
            ElasticCreateManufacturerIndex::class,
        ],
        InsertManufacturerIndex::class => [
            ElasticInsertManufacturerIndex::class,
        ],

        CreateMaterialTypeIndex::class => [
            ElasticCreateMaterialTypeIndex::class,
        ],
        InsertMaterialTypeIndex::class => [
            ElasticInsertMaterialTypeIndex::class,
        ],

        CreateMedicineIndex::class => [
            ElasticCreateMedicineIndex::class,
        ],
        InsertMedicineIndex::class => [
            ElasticInsertMedicineIndex::class,
        ],

        CreateMedicineGroupIndex::class => [
            ElasticCreateMedicineGroupIndex::class,
        ],
        InsertMedicineGroupIndex::class => [
            ElasticInsertMedicineGroupIndex::class,
        ],

        CreateMedicineLineIndex::class => [
            ElasticCreateMedicineLineIndex::class,
        ],
        InsertMedicineLineIndex::class => [
            ElasticInsertMedicineLineIndex::class,
        ],

        CreateMedicinePatyIndex::class => [
            ElasticCreateMedicinePatyIndex::class,
        ],
        InsertMedicinePatyIndex::class => [
            ElasticInsertMedicinePatyIndex::class,
        ],

        CreateMedicineTypeAcinIndex::class => [
            ElasticCreateMedicineTypeAcinIndex::class,
        ],
        InsertMedicineTypeAcinIndex::class => [
            ElasticInsertMedicineTypeAcinIndex::class,
        ],

        CreateMedicineTypeIndex::class => [
            ElasticCreateMedicineTypeIndex::class,
        ],
        InsertMedicineTypeIndex::class => [
            ElasticInsertMedicineTypeIndex::class,
        ],

        CreateMedicineUseFormIndex::class => [
            ElasticCreateMedicineUseFormIndex::class,
        ],
        InsertMedicineUseFormIndex::class => [
            ElasticInsertMedicineUseFormIndex::class,
        ],

        CreateMediOrgIndex::class => [
            ElasticCreateMediOrgIndex::class,
        ],
        InsertMediOrgIndex::class => [
            ElasticInsertMediOrgIndex::class,
        ],

        CreateMediRecordTypeIndex::class => [
            ElasticCreateMediRecordTypeIndex::class,
        ],
        InsertMediRecordTypeIndex::class => [
            ElasticInsertMediRecordTypeIndex::class,
        ],

        CreateMediStockIndex::class => [
            ElasticCreateMediStockIndex::class,
        ],
        InsertMediStockIndex::class => [
            ElasticInsertMediStockIndex::class,
        ],

        CreateMediStockMatyIndex::class => [
            ElasticCreateMediStockMatyIndex::class,
        ],
        InsertMediStockMatyIndex::class => [
            ElasticInsertMediStockMatyIndex::class,
        ],

        CreateMediStockMetyIndex::class => [
            ElasticCreateMediStockMetyIndex::class,
        ],
        InsertMediStockMetyIndex::class => [
            ElasticInsertMediStockMetyIndex::class,
        ],

        CreateMestPatientTypeIndex::class => [
            ElasticCreateMestPatientTypeIndex::class,
        ],
        InsertMestPatientTypeIndex::class => [
            ElasticInsertMestPatientTypeIndex::class,
        ],

        CreateMestRoomIndex::class => [
            ElasticCreateMestRoomIndex::class,
        ],
        InsertMestRoomIndex::class => [
            ElasticInsertMestRoomIndex::class,
        ],

        CreateMilitaryRankIndex::class => [
            ElasticCreateMilitaryRankIndex::class,
        ],
        InsertMilitaryRankIndex::class => [
            ElasticInsertMilitaryRankIndex::class,
        ],

        CreateModuleRoleIndex::class => [
            ElasticCreateModuleRoleIndex::class,
        ],
        InsertModuleRoleIndex::class => [
            ElasticInsertModuleRoleIndex::class,
        ],

        CreateModuleIndex::class => [
            ElasticCreateModuleIndex::class,
        ],
        InsertModuleIndex::class => [
            ElasticInsertModuleIndex::class,
        ],

        CreateNationalIndex::class => [
            ElasticCreateNationalIndex::class,
        ],
        InsertNationalIndex::class => [
            ElasticInsertNationalIndex::class,
        ],

        CreateOtherPaySourceIndex::class => [
            ElasticCreateOtherPaySourceIndex::class,
        ],
        InsertOtherPaySourceIndex::class => [
            ElasticInsertOtherPaySourceIndex::class,
        ],

        CreatePackageIndex::class => [
            ElasticCreatePackageIndex::class,
        ],
        InsertPackageIndex::class => [
            ElasticInsertPackageIndex::class,
        ],

        CreatePatientCaseIndex::class => [
            ElasticCreatePatientCaseIndex::class,
        ],
        InsertPatientCaseIndex::class => [
            ElasticInsertPatientCaseIndex::class,
        ],

        CreatePatientClassifyIndex::class => [
            ElasticCreatePatientClassifyIndex::class,
        ],
        InsertPatientClassifyIndex::class => [
            ElasticInsertPatientClassifyIndex::class,
        ],

        CreatePatientTypeAllowIndex::class => [
            ElasticCreatePatientTypeAllowIndex::class,
        ],
        InsertPatientTypeAllowIndex::class => [
            ElasticInsertPatientTypeAllowIndex::class,
        ],

        CreatePatientTypeIndex::class => [
            ElasticCreatePatientTypeIndex::class,
        ],
        InsertPatientTypeIndex::class => [
            ElasticInsertPatientTypeIndex::class,
        ],

        CreatePatientTypeRoomIndex::class => [
            ElasticCreatePatientTypeRoomIndex::class,
        ],
        InsertPatientTypeRoomIndex::class => [
            ElasticInsertPatientTypeRoomIndex::class,
        ],

        CreatePositionIndex::class => [
            ElasticCreatePositionIndex::class,
        ],
        InsertPositionIndex::class => [
            ElasticInsertPositionIndex::class,
        ],

        CreatePreparationsBloodIndex::class => [
            ElasticCreatePreparationsBloodIndex::class,
        ],
        InsertPreparationsBloodIndex::class => [
            ElasticInsertPreparationsBloodIndex::class,
        ],

        CreatePriorityTypeIndex::class => [
            ElasticCreatePriorityTypeIndex::class,
        ],
        InsertPriorityTypeIndex::class => [
            ElasticInsertPriorityTypeIndex::class,
        ],

        CreateProcessingMethodIndex::class => [
            ElasticCreateProcessingMethodIndex::class,
        ],
        InsertProcessingMethodIndex::class => [
            ElasticInsertProcessingMethodIndex::class,
        ],

        CreateProvinceIndex::class => [
            ElasticCreateProvinceIndex::class,
        ],
        InsertProvinceIndex::class => [
            ElasticInsertProvinceIndex::class,
        ],

        CreatePtttCatastropheIndex::class => [
            ElasticCreatePtttCatastropheIndex::class,
        ],
        InsertPtttCatastropheIndex::class => [
            ElasticInsertPtttCatastropheIndex::class,
        ],

        CreatePtttConditionIndex::class => [
            ElasticCreatePtttConditionIndex::class,
        ],
        InsertPtttConditionIndex::class => [
            ElasticInsertPtttConditionIndex::class,
        ],

        CreatePtttGroupIndex::class => [
            ElasticCreatePtttGroupIndex::class,
        ],
        InsertPtttGroupIndex::class => [
            ElasticInsertPtttGroupIndex::class,
        ],

        CreatePtttMethodIndex::class => [
            ElasticCreatePtttMethodIndex::class,
        ],
        InsertPtttMethodIndex::class => [
            ElasticInsertPtttMethodIndex::class,
        ],

        CreatePtttTableIndex::class => [
            ElasticCreatePtttTableIndex::class,
        ],
        InsertPtttTableIndex::class => [
            ElasticInsertPtttTableIndex::class,
        ],

        CreateRationGroupIndex::class => [
            ElasticCreateRationGroupIndex::class,
        ],
        InsertRationGroupIndex::class => [
            ElasticInsertRationGroupIndex::class,
        ],

        CreateRationTimeIndex::class => [
            ElasticCreateRationTimeIndex::class,
        ],
        InsertRationTimeIndex::class => [
            ElasticInsertRationTimeIndex::class,
        ],

        CreateReceptionRoomIndex::class => [
            ElasticCreateReceptionRoomIndex::class,
        ],
        InsertReceptionRoomIndex::class => [
            ElasticInsertReceptionRoomIndex::class,
        ],

        CreateRefectoryIndex::class => [
            ElasticCreateRefectoryIndex::class,
        ],
        InsertRefectoryIndex::class => [
            ElasticInsertRefectoryIndex::class,
        ],

        CreateRelationIndex::class => [
            ElasticCreateRelationIndex::class,
        ],
        InsertRelationIndex::class => [
            ElasticInsertRelationIndex::class,
        ],

        CreateReligionIndex::class => [
            ElasticCreateReligionIndex::class,
        ],
        InsertReligionIndex::class => [
            ElasticInsertReligionIndex::class,
        ],

        CreateRoleIndex::class => [
            ElasticCreateRoleIndex::class,
        ],
        InsertRoleIndex::class => [
            ElasticInsertRoleIndex::class,
        ],

        CreateRoomIndex::class => [
            ElasticCreateRoomIndex::class,
        ],
        InsertRoomIndex::class => [
            ElasticInsertRoomIndex::class,
        ],

        CreateRoomGroupIndex::class => [
            ElasticCreateRoomGroupIndex::class,
        ],
        InsertRoomGroupIndex::class => [
            ElasticInsertRoomGroupIndex::class,
        ],

        CreateRoomTypeIndex::class => [
            ElasticCreateRoomTypeIndex::class,
        ],
        InsertRoomTypeIndex::class => [
            ElasticInsertRoomTypeIndex::class,
        ],

        CreateSaleProfitCfgIndex::class => [
            ElasticCreateSaleProfitCfgIndex::class,
        ],
        InsertSaleProfitCfgIndex::class => [
            ElasticInsertSaleProfitCfgIndex::class,
        ],

        CreateServiceConditionIndex::class => [
            ElasticCreateServiceConditionIndex::class,
        ],
        InsertServiceConditionIndex::class => [
            ElasticInsertServiceConditionIndex::class,
        ],

        CreateServiceIndex::class => [
            ElasticCreateServiceIndex::class,
        ],
        InsertServiceIndex::class => [
            ElasticInsertServiceIndex::class,
        ],
        
        CreateServiceFollowIndex::class => [
            ElasticCreateServiceFollowIndex::class,
        ],
        InsertServiceFollowIndex::class => [
            ElasticInsertServiceFollowIndex::class,
        ],

        CreateServiceGroupIndex::class => [
            ElasticCreateServiceGroupIndex::class,
        ],
        InsertServiceGroupIndex::class => [
            ElasticInsertServiceGroupIndex::class,
        ],

        CreateServiceMachineIndex::class => [
            ElasticCreateServiceMachineIndex::class,
        ],
        InsertServiceMachineIndex::class => [
            ElasticInsertServiceMachineIndex::class,
        ],

        CreateServicePatyIndex::class => [
            ElasticCreateServicePatyIndex::class,
        ],
        InsertServicePatyIndex::class => [
            ElasticInsertServicePatyIndex::class,
        ],

        CreateServiceReqTypeIndex::class => [
            ElasticCreateServiceReqTypeIndex::class,
        ],
        InsertServiceReqTypeIndex::class => [
            ElasticInsertServiceReqTypeIndex::class,
        ],

        CreateServiceRoomIndex::class => [
            ElasticCreateServiceRoomIndex::class,
        ],
        InsertServiceRoomIndex::class => [
            ElasticInsertServiceRoomIndex::class,
        ],

        CreateServiceTypeIndex::class => [
            ElasticCreateServiceTypeIndex::class,
        ],
        InsertServiceTypeIndex::class => [
            ElasticInsertServiceTypeIndex::class,
        ],

        CreateServiceUnitIndex::class => [
            ElasticCreateServiceUnitIndex::class,
        ],
        InsertServiceUnitIndex::class => [
            ElasticInsertServiceUnitIndex::class,
        ],

        CreateServSegrIndex::class => [
            ElasticCreateServSegrIndex::class,
        ],
        InsertServSegrIndex::class => [
            ElasticInsertServSegrIndex::class,
        ],

        CreateSpecialityIndex::class => [
            ElasticCreateSpecialityIndex::class,
        ],
        InsertSpecialityIndex::class => [
            ElasticInsertSpecialityIndex::class,
        ],

        CreateSuimIndexIndex::class => [
            ElasticCreateSuimIndexIndex::class,
        ],
        InsertSuimIndexIndex::class => [
            ElasticInsertSuimIndexIndex::class,
        ],

        CreateSupplierIndex::class => [
            ElasticCreateSupplierIndex::class,
        ],
        InsertSupplierIndex::class => [
            ElasticInsertSupplierIndex::class,
        ],
        
        CreateTestIndexIndex::class => [
            ElasticCreateTestIndexIndex::class,
        ],
        InsertTestIndexIndex::class => [
            ElasticInsertTestIndexIndex::class,
        ],

        CreateTestIndexUnitIndex::class => [
            ElasticCreateTestIndexUnitIndex::class,
        ],
        InsertTestIndexUnitIndex::class => [
            ElasticInsertTestIndexUnitIndex::class,
        ],

        CreateTestSampleTypeIndex::class => [
            ElasticCreateTestSampleTypeIndex::class,
        ],
        InsertTestSampleTypeIndex::class => [
            ElasticInsertTestSampleTypeIndex::class,
        ],

        CreateTestTypeIndex::class => [
            ElasticCreateTestTypeIndex::class,
        ],
        InsertTestTypeIndex::class => [
            ElasticInsertTestTypeIndex::class,
        ],

        CreateTranPatiTechIndex::class => [
            ElasticCreateTranPatiTechIndex::class,
        ],
        InsertTranPatiTechIndex::class => [
            ElasticInsertTranPatiTechIndex::class,
        ],

        CreatePayFormIndex::class => [
            ElasticCreatePayFormIndex::class,
        ],
        InsertPayFormIndex::class => [
            ElasticInsertPayFormIndex::class,
        ],

        CreateTransactionTypeIndex::class => [
            ElasticCreateTransactionTypeIndex::class,
        ],
        InsertTransactionTypeIndex::class => [
            ElasticInsertTransactionTypeIndex::class,
        ],

        CreateTreatmentEndTypeIndex::class => [
            ElasticCreateTreatmentEndTypeIndex::class,
        ],
        InsertTreatmentEndTypeIndex::class => [
            ElasticInsertTreatmentEndTypeIndex::class,
        ],

        CreateTreatmentTypeIndex::class => [
            ElasticCreateTreatmentTypeIndex::class,
        ],
        InsertTreatmentTypeIndex::class => [
            ElasticInsertTreatmentTypeIndex::class,
        ],

        CreateUnlimitReasonIndex::class => [
            ElasticCreateUnlimitReasonIndex::class,
        ],
        InsertUnlimitReasonIndex::class => [
            ElasticInsertUnlimitReasonIndex::class,
        ],

        CreateWorkPlaceIndex::class => [
            ElasticCreateWorkPlaceIndex::class,
        ],
        InsertWorkPlaceIndex::class => [
            ElasticInsertWorkPlaceIndex::class,
        ],

        CreateGroupTypeIndex::class => [
            ElasticCreateGroupTypeIndex::class,
        ],
        InsertGroupTypeIndex::class => [
            ElasticInsertGroupTypeIndex::class,
        ],

        CreatePackingTypeIndex::class => [
            ElasticCreatePackingTypeIndex::class,
        ],
        InsertPackingTypeIndex::class => [
            ElasticInsertPackingTypeIndex::class,
        ],

        CreateMaterialTypeMapIndex::class => [
            ElasticCreateMaterialTypeMapIndex::class,
        ],
        InsertMaterialTypeMapIndex::class => [
            ElasticInsertMaterialTypeMapIndex::class,
        ],

        CreateMemaGroupIndex::class => [
            ElasticCreateMemaGroupIndex::class,
        ],
        InsertMemaGroupIndex::class => [
            ElasticInsertMemaGroupIndex::class,
        ],

        CreateImpSourceIndex::class => [
            ElasticCreateImpSourceIndex::class,
        ],
        InsertImpSourceIndex::class => [
            ElasticInsertImpSourceIndex::class,
        ],

        CreateMedicalContractIndex::class => [
            ElasticCreateMedicalContractIndex::class,
        ],
        InsertMedicalContractIndex::class => [
            ElasticInsertMedicalContractIndex::class,
        ],

        CreateBidIndex::class => [
            ElasticCreateBidIndex::class,
        ],
        InsertBidIndex::class => [
            ElasticInsertBidIndex::class,
        ],

        CreateAtcIndex::class => [
            ElasticCreateAtcIndex::class,
        ],
        InsertAtcIndex::class => [
            ElasticInsertAtcIndex::class,
        ],

        CreateStorageConditionIndex::class => [
            ElasticCreateStorageConditionIndex::class,
        ],
        InsertStorageConditionIndex::class => [
            ElasticInsertStorageConditionIndex::class,
        ],

        CreateVaccineTypeIndex::class => [
            ElasticCreateVaccineTypeIndex::class,
        ],
        InsertVaccineTypeIndex::class => [
            ElasticInsertVaccineTypeIndex::class,
        ],

        CreateHtuIndex::class => [
            ElasticCreateHtuIndex::class,
        ],
        InsertHtuIndex::class => [
            ElasticInsertHtuIndex::class,
        ],

        CreateSuimIndexUnitIndex::class => [
            ElasticCreateSuimIndexUnitIndex::class,
        ],
        InsertSuimIndexUnitIndex::class => [
            ElasticInsertSuimIndexUnitIndex::class,
        ],

        CreateTestIndexGroupIndex::class => [
            ElasticCreateTestIndexGroupIndex::class,
        ],
        InsertTestIndexGroupIndex::class => [
            ElasticInsertTestIndexGroupIndex::class,
        ],

        CreateServiceReqLViewIndex::class => [
            ElasticCreateServiceReqLViewIndex::class,
        ],
        InsertServiceReqLViewIndex::class => [
            ElasticInsertServiceReqLViewIndex::class,
        ],

        CreateDebateIndex::class => [
            ElasticCreateDebateIndex::class,
        ],
        InsertDebateIndex::class => [
            ElasticInsertDebateIndex::class,
        ],

        CreateDebateVViewIndex::class => [
            ElasticCreateDebateVViewIndex::class,
        ],
        InsertDebateVViewIndex::class => [
            ElasticInsertDebateVViewIndex::class,
        ],

        CreateUserRoomVViewIndex::class => [
            ElasticCreateUserRoomVViewIndex::class,
        ],
        InsertUserRoomVViewIndex::class => [
            ElasticInsertUserRoomVViewIndex::class,
        ],

        CreateDebateUserIndex::class => [
            ElasticCreateDebateUserIndex::class,
        ],
        InsertDebateUserIndex::class => [
            ElasticInsertDebateUserIndex::class,
        ],

        CreateDebateEkipUserIndex::class => [
            ElasticCreateDebateEkipUserIndex::class,
        ],
        InsertDebateEkipUserIndex::class => [
            ElasticInsertDebateEkipUserIndex::class,
        ],

        CreateTrackingIndex::class => [
            ElasticCreateTrackingIndex::class,
        ],
        InsertTrackingIndex::class => [
            ElasticInsertTrackingIndex::class,
        ],

        CreateTestServiceReqListVViewIndex::class => [
            ElasticCreateTestServiceReqListVViewIndex::class,
        ],
        InsertTestServiceReqListVViewIndex::class => [
            ElasticInsertTestServiceReqListVViewIndex::class,
        ],

        CreateSereServIndex::class => [
            ElasticCreateSereServIndex::class,
        ],
        InsertSereServIndex::class => [
            ElasticInsertSereServIndex::class,
        ],

        CreateSereServVView4Index::class => [
            ElasticCreateSereServVView4Index::class,
        ],
        InsertSereServVView4Index::class => [
            ElasticInsertSereServVView4Index::class,
        ],
       
        CreatePatientTypeAlterVViewIndex::class => [
            ElasticCreatePatientTypeAlterVViewIndex::class,
        ],
        InsertPatientTypeAlterVViewIndex::class => [
            ElasticInsertPatientTypeAlterVViewIndex::class,
        ],

        CreateTreatmentLViewIndex::class => [
            ElasticCreateTreatmentLViewIndex::class,
        ],
        InsertTreatmentLViewIndex::class => [
            ElasticInsertTreatmentLViewIndex::class,
        ],

        CreateTreatmentFeeViewIndex::class => [
            ElasticCreateTreatmentFeeViewIndex::class,
        ],
        InsertTreatmentFeeViewIndex::class => [
            ElasticInsertTreatmentFeeViewIndex::class,
        ],

        CreateTreatmentBedRoomLViewIndex::class => [
            ElasticCreateTreatmentBedRoomLViewIndex::class,
        ],
        InsertTreatmentBedRoomLViewIndex::class => [
            ElasticInsertTreatmentBedRoomLViewIndex::class,
        ],

        CreateDhstIndex::class => [
            ElasticCreateDhstIndex::class,
        ],
        InsertDhstIndex::class => [
            ElasticInsertDhstIndex::class,
        ],

        CreateSereServExtIndex::class => [
            ElasticCreateSereServExtIndex::class,
        ],
        InsertSereServExtIndex::class => [
            ElasticInsertSereServExtIndex::class,
        ],

        CreateSereServTeinIndex::class => [
            ElasticCreateSereServTeinIndex::class,
        ],
        InsertSereServTeinIndex::class => [
            ElasticInsertSereServTeinIndex::class,
        ],

        CreateSereServTeinVViewIndex::class => [
            ElasticCreateSereServTeinVViewIndex::class,
        ],
        InsertSereServTeinVViewIndex::class => [
            ElasticInsertSereServTeinVViewIndex::class,
        ],

        CreateSereServBillIndex::class => [
            ElasticCreateSereServBillIndex::class,
        ],
        InsertSereServBillIndex::class => [
            ElasticInsertSereServBillIndex::class,
        ],

        CreateSereServDepositVViewIndex::class => [
            ElasticCreateSereServDepositVViewIndex::class,
        ],
        InsertSereServDepositVViewIndex::class => [
            ElasticInsertSereServDepositVViewIndex::class,
        ],

        CreateSeseDepoRepayVViewIndex::class => [
            ElasticCreateSeseDepoRepayVViewIndex::class,
        ],
        InsertSeseDepoRepayVViewIndex::class => [
            ElasticInsertSeseDepoRepayVViewIndex::class,
        ],

        CreateAccountBookVViewIndex::class => [
            ElasticCreateAccountBookVViewIndex::class,
        ],
        InsertAccountBookVViewIndex::class => [
            ElasticInsertAccountBookVViewIndex::class,
        ],

        CreateEmrCoverTypeIndex::class => [
            ElasticCreateEmrCoverTypeIndex::class,
        ],
        InsertEmrCoverTypeIndex::class => [
            ElasticInsertEmrCoverTypeIndex::class,
        ],

        CreateEmrFormIndex::class => [
            ElasticCreateEmrFormIndex::class,
        ],
        InsertEmrFormIndex::class => [
            ElasticInsertEmrFormIndex::class,
        ],

        CreateRoomVViewIndex::class => [
            ElasticCreateRoomVViewIndex::class,
        ],
        InsertRoomVViewIndex::class => [
            ElasticInsertRoomVViewIndex::class,
        ],

        CreateIcdListVViewIndex::class => [
            ElasticCreateIcdListVViewIndex::class,
        ],
        InsertIcdListVViewIndex::class => [
            ElasticInsertIcdListVViewIndex::class,
        ],

        CreateDeathCauseIndex::class => [
            ElasticCreateDeathCauseIndex::class,
        ],
        InsertDeathCauseIndex::class => [
            ElasticInsertDeathCauseIndex::class,
        ],

        CreateTreatmentResultIndex::class => [
            ElasticCreateTreatmentResultIndex::class,
        ],
        InsertTreatmentResultIndex::class => [
            ElasticInsertTreatmentResultIndex::class,
        ],

        CreateTranPatiFormIndex::class => [
            ElasticCreateTranPatiFormIndex::class,
        ],
        InsertTranPatiFormIndex::class => [
            ElasticInsertTranPatiFormIndex::class,
        ],

        CreateDocumentTypeIndex::class => [
            ElasticCreateDocumentTypeIndex::class,
        ],
        InsertDocumentTypeIndex::class => [
            ElasticInsertDocumentTypeIndex::class,
        ],

        CreateServiceReqSttIndex::class => [
            ElasticCreateServiceReqSttIndex::class,
        ],
        InsertServiceReqSttIndex::class => [
            ElasticInsertServiceReqSttIndex::class,
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
