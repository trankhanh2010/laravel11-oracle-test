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
use App\Events\Elastic\AgeType\CreateAgeTypeIndex;
use App\Events\Elastic\Area\CreateAreaIndex;
use App\Events\Elastic\Area\InsertAreaIndex;
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
use App\Events\Elastic\BhytBlacklist\CreateBhytBlacklistIndex;
use App\Events\Elastic\BhytBlacklist\InsertBhytBlacklistIndex;
use App\Events\Elastic\BhytParam\CreateBhytParamIndex;
use App\Events\Elastic\BhytParam\InsertBhytParamIndex;
use App\Events\Elastic\BhytWhitelist\CreateBhytWhitelistIndex;
use App\Events\Elastic\BhytWhitelist\InsertBhytWhitelistIndex;
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
use App\Events\Elastic\DeathWithin\CreateDeathWithinIndex;
use App\Events\Elastic\DeathWithin\InsertDeathWithinIndex;
use App\Events\Elastic\DebateReason\CreateDebateReasonIndex;
use App\Events\Elastic\DebateReason\InsertDebateReasonIndex;
use App\Events\Elastic\DebateType\CreateDebateTypeIndex;
use App\Events\Elastic\DeleteIndex;
use App\Events\Elastic\Department\CreateDepartmentIndex;
use App\Events\Elastic\Department\InsertDepartmentIndex;
use App\Events\Elastic\DiimType\CreateDiimTypeIndex;
use App\Events\Elastic\District\CreateDistrictIndex;
use App\Events\Elastic\District\InsertDistrictIndex;
use App\Events\Elastic\DosageForm\CreateDosageFormIndex;
use App\Events\Elastic\DosageForm\InsertDosageFormIndex;
use App\Events\Elastic\EmotionlessMethod\CreateEmotionlessMethodIndex;
use App\Events\Elastic\EmotionlessMethod\InsertEmotionlessMethodIndex;
use App\Events\Elastic\Employee\CreateEmployeeIndex;
use App\Events\Elastic\Employee\InsertEmployeeIndex;
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
use App\Events\Elastic\ExpMestReason\CreateExpMestReasonIndex;
use App\Events\Elastic\ExpMestReason\InsertExpMestReasonIndex;
use App\Events\Elastic\ExroRoom\CreateExroRoomIndex;
use App\Events\Elastic\ExroRoom\InsertExroRoomIndex;
use App\Events\Elastic\FileType\CreateFileTypeIndex;
use App\Events\Elastic\FileType\InsertFileTypeIndex;
use App\Events\Elastic\FilmSize\CreateFilmSizeIndex;
use App\Events\Elastic\FuexType\CreateFuexTypeIndex;
use App\Events\Elastic\Gender\CreateGenderIndex;
use App\Events\Elastic\Group\CreateGroupIndex;
use App\Events\Elastic\HeinServiceType\CreateHeinServiceTypeIndex;
use App\Events\Elastic\HospitalizeReason\CreateHospitalizeReasonIndex;
use App\Events\Elastic\HospitalizeReason\InsertHospitalizeReasonIndex;
use App\Events\Elastic\Icd\CreateIcdIndex;
use App\Events\Elastic\Icd\InsertIcdIndex;
use App\Events\Elastic\IcdCm\CreateIcdCmIndex;
use App\Events\Elastic\IcdCm\InsertIcdCmIndex;
use App\Events\Elastic\IcdGroup\CreateIcdGroupIndex;
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
use App\Events\Elastic\Medicine\CreateMedicineIndex;
use App\Events\Elastic\MedicineGroup\CreateMedicineGroupIndex;
use App\Events\Elastic\MedicineLine\CreateMedicineLineIndex;
use App\Events\Elastic\MedicinePaty\CreateMedicinePatyIndex;
use App\Events\Elastic\MedicinePaty\InsertMedicinePatyIndex;
use App\Events\Elastic\MedicineType\CreateMedicineTypeIndex;
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
use App\Events\Elastic\MestPatientType\CreateMestPatientTypeIndex;
use App\Events\Elastic\MestPatientType\InsertMestPatientTypeIndex;
use App\Events\Elastic\MestRoom\CreateMestRoomIndex;
use App\Events\Elastic\MestRoom\InsertMestRoomIndex;
use App\Events\Elastic\MilitaryRank\CreateMilitaryRankIndex;
use App\Events\Elastic\Module\CreateModuleIndex;
use App\Events\Elastic\Module\InsertModuleIndex;
use App\Events\Elastic\ModuleRole\CreateModuleRoleIndex;
use App\Events\Elastic\ModuleRole\InsertModuleRoleIndex;
use App\Events\Elastic\National\CreateNationalIndex;
use App\Events\Elastic\National\InsertNationalIndex;
use App\Events\Elastic\OtherPaySource\CreateOtherPaySourceIndex;
use App\Events\Elastic\OtherPaySource\InsertOtherPaySourceIndex;
use App\Events\Elastic\Package\CreatePackageIndex;
use App\Events\Elastic\PatientCase\CreatePatientCaseIndex;
use App\Events\Elastic\PatientClassify\CreatePatientClassifyIndex;
use App\Events\Elastic\PatientClassify\InsertPatientClassifyIndex;
use App\Events\Elastic\PatientType\CreatePatientTypeIndex;
use App\Events\Elastic\PatientType\InsertPatientTypeIndex;
use App\Events\Elastic\PatientTypeAllow\CreatePatientTypeAllowIndex;
use App\Events\Elastic\PatientTypeAllow\InsertPatientTypeAllowIndex;
use App\Events\Elastic\PatientTypeRoom\CreatePatientTypeRoomIndex;
use App\Events\Elastic\PatientTypeRoom\InsertPatientTypeRoomIndex;
use App\Events\Elastic\Position\CreatePositionIndex;
use App\Events\Elastic\Position\InsertPositionIndex;
use App\Events\Elastic\PreparationsBlood\CreatePreparationsBloodIndex;
use App\Events\Elastic\PreparationsBlood\InsertPreparationsBloodIndex;
use App\Events\Elastic\PriorityType\CreatePriorityTypeIndex;
use App\Events\Elastic\PriorityType\InsertPriorityTypeIndex;
use App\Events\Elastic\ProcessingMethod\CreateProcessingMethodIndex;
use App\Events\Elastic\Province\CreateProvinceIndex;
use App\Events\Elastic\Province\InsertProvinceIndex;
use App\Events\Elastic\PtttCatastrophe\CreatePtttCatastropheIndex;
use App\Events\Elastic\PtttCatastrophe\InsertPtttCatastropheIndex;
use App\Events\Elastic\PtttCondition\CreatePtttConditionIndex;
use App\Events\Elastic\PtttCondition\InsertPtttConditionIndex;
use App\Events\Elastic\PtttGroup\CreatePtttGroupIndex;
use App\Events\Elastic\PtttGroup\InsertPtttGroupIndex;
use App\Events\Elastic\RoomType\CreateRoomTypeIndex;
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
use App\Listeners\Elastic\AgeType\ElasticCreateAgeTypeIndex;
use App\Listeners\Elastic\Area\ElasticCreateAreaIndex;
use App\Listeners\Elastic\Area\ElasticInsertAreaIndex;
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
use App\Listeners\Elastic\BhytBlacklist\ElasticCreateBhytBlacklistIndex;
use App\Listeners\Elastic\BhytBlacklist\ElasticInsertBhytBlacklistIndex;
use App\Listeners\Elastic\BhytParam\ElasticCreateBhytParamIndex;
use App\Listeners\Elastic\BhytParam\ElasticInsertBhytParamIndex;
use App\Listeners\Elastic\BhytWhitelist\ElasticCreateBhytWhitelistIndex;
use App\Listeners\Elastic\BhytWhitelist\ElasticInsertBhytWhitelistIndex;
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
use App\Listeners\Elastic\DeathWithin\ElasticCreateDeathWithinIndex;
use App\Listeners\Elastic\DeathWithin\ElasticInsertDeathWithinIndex;
use App\Listeners\Elastic\DebateReason\ElasticCreateDebateReasonIndex;
use App\Listeners\Elastic\DebateReason\ElasticInsertDebateReasonIndex;
use App\Listeners\Elastic\DebateType\ElasticCreateDebateTypeIndex;
use App\Listeners\Elastic\Department\ElasticCreateDepartmentIndex;
use App\Listeners\Elastic\Department\ElasticInsertDepartmentIndex;
use App\Listeners\Elastic\DiimType\ElasticCreateDiimTypeIndex;
use App\Listeners\Elastic\District\ElasticCreateDistrictIndex;
use App\Listeners\Elastic\District\ElasticInsertDistrictIndex;
use App\Listeners\Elastic\DosageForm\ElasticCreateDosageFormIndex;
use App\Listeners\Elastic\DosageForm\ElasticInsertDosageFormIndex;
use App\Listeners\Elastic\ElasticDeleteIndex;
use App\Listeners\Elastic\EmotionlessMethod\ElasticCreateEmotionlessMethodIndex;
use App\Listeners\Elastic\EmotionlessMethod\ElasticInsertEmotionlessMethodIndex;
use App\Listeners\Elastic\Employee\ElasticCreateEmployeeIndex;
use App\Listeners\Elastic\Employee\ElasticInsertEmployeeIndex;
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
use App\Listeners\Elastic\ExpMestReason\ElasticCreateExpMestReasonIndex;
use App\Listeners\Elastic\ExpMestReason\ElasticInsertExpMestReasonIndex;
use App\Listeners\Elastic\ExroRoom\ElasticCreateExroRoomIndex;
use App\Listeners\Elastic\ExroRoom\ElasticInsertExroRoomIndex;
use App\Listeners\Elastic\FileType\ElasticCreateFileTypeIndex;
use App\Listeners\Elastic\FileType\ElasticInsertFileTypeIndex;
use App\Listeners\Elastic\FilmSize\ElasticCreateFilmSizeIndex;
use App\Listeners\Elastic\FuexType\ElasticCreateFuexTypeIndex;
use App\Listeners\Elastic\Gender\ElasticCreateGenderIndex;
use App\Listeners\Elastic\Group\ElasticCreateGroupIndex;
use App\Listeners\Elastic\HeinServiceType\ElasticCreateHeinServiceTypeIndex;
use App\Listeners\Elastic\HospitalizeReason\ElasticCreateHospitalizeReasonIndex;
use App\Listeners\Elastic\HospitalizeReason\ElasticInsertHospitalizeReasonIndex;
use App\Listeners\Elastic\Icd\ElasticCreateIcdIndex;
use App\Listeners\Elastic\Icd\ElasticInsertIcdIndex;
use App\Listeners\Elastic\IcdCm\ElasticCreateIcdCmIndex;
use App\Listeners\Elastic\IcdCm\ElasticInsertIcdCmIndex;
use App\Listeners\Elastic\IcdGroup\ElasticCreateIcdGroupIndex;
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
use App\Listeners\Elastic\Medicine\ElasticCreateMedicineIndex;
use App\Listeners\Elastic\MedicineGroup\ElasticCreateMedicineGroupIndex;
use App\Listeners\Elastic\MedicineLine\ElasticCreateMedicineLineIndex;
use App\Listeners\Elastic\MedicinePaty\ElasticCreateMedicinePatyIndex;
use App\Listeners\Elastic\MedicinePaty\ElasticInsertMedicinePatyIndex;
use App\Listeners\Elastic\MedicineType\ElasticCreateMedicineTypeIndex;
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
use App\Listeners\Elastic\MestPatientType\ElasticCreateMestPatientTypeIndex;
use App\Listeners\Elastic\MestPatientType\ElasticInsertMestPatientTypeIndex;
use App\Listeners\Elastic\MestRoom\ElasticCreateMestRoomIndex;
use App\Listeners\Elastic\MestRoom\ElasticInsertMestRoomIndex;
use App\Listeners\Elastic\MilitaryRank\ElasticCreateMilitaryRankIndex;
use App\Listeners\Elastic\Module\ElasticCreateModuleIndex;
use App\Listeners\Elastic\Module\ElasticInsertModuleIndex;
use App\Listeners\Elastic\ModuleRole\ElasticCreateModuleRoleIndex;
use App\Listeners\Elastic\ModuleRole\ElasticInsertModuleRoleIndex;
use App\Listeners\Elastic\National\ElasticCreateNationalIndex;
use App\Listeners\Elastic\National\ElasticInsertNationalIndex;
use App\Listeners\Elastic\OtherPaySource\ElasticCreateOtherPaySourceIndex;
use App\Listeners\Elastic\OtherPaySource\ElasticInsertOtherPaySourceIndex;
use App\Listeners\Elastic\Package\ElasticCreatePackageIndex;
use App\Listeners\Elastic\PatientCase\ElasticCreatePatientCaseIndex;
use App\Listeners\Elastic\PatientClassify\ElasticCreatePatientClassifyIndex;
use App\Listeners\Elastic\PatientClassify\ElasticInsertPatientClassifyIndex;
use App\Listeners\Elastic\PatientType\ElasticCreatePatientTypeIndex;
use App\Listeners\Elastic\PatientType\ElasticInsertPatientTypeIndex;
use App\Listeners\Elastic\PatientTypeAllow\ElasticCreatePatientTypeAllowIndex;
use App\Listeners\Elastic\PatientTypeAllow\ElasticInsertPatientTypeAllowIndex;
use App\Listeners\Elastic\PatientTypeRoom\ElasticCreatePatientTypeRoomIndex;
use App\Listeners\Elastic\PatientTypeRoom\ElasticInsertPatientTypeRoomIndex;
use App\Listeners\Elastic\Position\ElasticCreatePositionIndex;
use App\Listeners\Elastic\Position\ElasticInsertPositionIndex;
use App\Listeners\Elastic\PreparationsBlood\ElasticCreatePreparationsBloodIndex;
use App\Listeners\Elastic\PreparationsBlood\ElasticInsertPreparationsBloodIndex;
use App\Listeners\Elastic\PriorityType\ElasticCreatePriorityTypeIndex;
use App\Listeners\Elastic\PriorityType\ElasticInsertPriorityTypeIndex;
use App\Listeners\Elastic\ProcessingMethod\ElasticCreateProcessingMethodIndex;
use App\Listeners\Elastic\Province\ElasticCreateProvinceIndex;
use App\Listeners\Elastic\Province\ElasticInsertProvinceIndex;
use App\Listeners\Elastic\PtttCatastrophe\ElasticCreatePtttCatastropheIndex;
use App\Listeners\Elastic\PtttCatastrophe\ElasticInsertPtttCatastropheIndex;
use App\Listeners\Elastic\PtttCondition\ElasticCreatePtttConditionIndex;
use App\Listeners\Elastic\PtttCondition\ElasticInsertPtttConditionIndex;
use App\Listeners\Elastic\PtttGroup\ElasticCreatePtttGroupIndex;
use App\Listeners\Elastic\PtttGroup\ElasticInsertPtttGroupIndex;
use App\Listeners\Elastic\RoomType\ElasticCreateRoomTypeIndex;
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

        CreateDepartmentIndex::class => [
            ElasticCreateDepartmentIndex::class,
        ],
        InsertDepartmentIndex::class => [
            ElasticInsertDepartmentIndex::class,
        ],

        CreateDiimTypeIndex::class => [
            ElasticCreateDiimTypeIndex::class,
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

        CreateExpMestReasonIndex::class => [
            ElasticCreateExpMestReasonIndex::class,
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
    
        CreateFuexTypeIndex::class => [
            ElasticCreateFuexTypeIndex::class,
        ],

        CreateGenderIndex::class => [
            ElasticCreateGenderIndex::class,
        ],

        CreateGroupIndex::class => [
            ElasticCreateGroupIndex::class,
        ],

        CreateHeinServiceTypeIndex::class => [
            ElasticCreateHeinServiceTypeIndex::class,
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

        CreateMedicineIndex::class => [
            ElasticCreateMedicineIndex::class,
        ],

        CreateMedicineGroupIndex::class => [
            ElasticCreateMedicineGroupIndex::class,
        ],

        CreateMedicineLineIndex::class => [
            ElasticCreateMedicineLineIndex::class,
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

        CreatePatientCaseIndex::class => [
            ElasticCreatePatientCaseIndex::class,
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

        CreateRoomTypeIndex::class => [
            ElasticCreateRoomTypeIndex::class,
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
