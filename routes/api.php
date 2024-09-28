<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HISController;

// Cache Controllers
use App\Http\Controllers\Api\CacheControllers\DepartmentController;
use App\Http\Controllers\Api\CacheControllers\GroupController;
use App\Http\Controllers\Api\CacheControllers\RoomTypeController;
use App\Http\Controllers\Api\CacheControllers\RoomGroupController;
use App\Http\Controllers\Api\CacheControllers\ScreenSaverModuleLinkController;
use App\Http\Controllers\Api\CacheControllers\BedRoomController;
use App\Http\Controllers\Api\CacheControllers\TestTypeController;
use App\Http\Controllers\Api\CacheControllers\ExecuteRoomController;
use App\Http\Controllers\Api\CacheControllers\SpecialityController;
use App\Http\Controllers\Api\CacheControllers\TreatmentTypeController;
use App\Http\Controllers\Api\CacheControllers\MediOrgController;
use App\Http\Controllers\Api\CacheControllers\BranchController;
use App\Http\Controllers\Api\CacheControllers\DistrictController;
use App\Http\Controllers\Api\CacheControllers\OtherPaySourceController;
use App\Http\Controllers\Api\CacheControllers\MilitaryRankController;
use App\Http\Controllers\Api\CacheControllers\MediStockController;
use App\Http\Controllers\Api\CacheControllers\ReceptionRoomController;
use App\Http\Controllers\Api\CacheControllers\AreaController;
use App\Http\Controllers\Api\CacheControllers\PatientClassifyController;
use App\Http\Controllers\Api\CacheControllers\RefectoryController;
use App\Http\Controllers\Api\CacheControllers\ExecuteGroupController;
use App\Http\Controllers\Api\CacheControllers\CashierRoomController;
use App\Http\Controllers\Api\CacheControllers\NationalController;
use App\Http\Controllers\Api\CacheControllers\ProvinceController;
use App\Http\Controllers\Api\CacheControllers\DataStoreController;
use App\Http\Controllers\Api\CacheControllers\RoomController;
use App\Http\Controllers\Api\CacheControllers\ExecuteRoleController;
use App\Http\Controllers\Api\CacheControllers\CommuneController;
use App\Http\Controllers\Api\CacheControllers\ServiceController;
use App\Http\Controllers\Api\CacheControllers\ServiceTypeController;
use App\Http\Controllers\Api\CacheControllers\ServiceUnitController;
use App\Http\Controllers\Api\CacheControllers\PatientTypeController;
use App\Http\Controllers\Api\CacheControllers\PtttMethodController;
use App\Http\Controllers\Api\CacheControllers\PtttGroupController;
use App\Http\Controllers\Api\CacheControllers\IcdCmController;
use App\Http\Controllers\Api\CacheControllers\RationGroupController;
use App\Http\Controllers\Api\CacheControllers\DiimTypeController;
use App\Http\Controllers\Api\CacheControllers\FuexTypeController;
use App\Http\Controllers\Api\CacheControllers\TestSampleTypeController;
use App\Http\Controllers\Api\CacheControllers\FilmSizeController;
use App\Http\Controllers\Api\CacheControllers\GenderController;
use App\Http\Controllers\Api\CacheControllers\BodyPartController;
use App\Http\Controllers\Api\CacheControllers\ExeServiceModuleController;
use App\Http\Controllers\Api\CacheControllers\HeinServiceTypeController;
use App\Http\Controllers\Api\CacheControllers\SuimIndexController;
use App\Http\Controllers\Api\CacheControllers\PackageController;
use App\Http\Controllers\Api\CacheControllers\ServicePatyController;
use App\Http\Controllers\Api\CacheControllers\BhytWhitelistController;
use App\Http\Controllers\Api\CacheControllers\RationTimeController;
use App\Http\Controllers\Api\CacheControllers\MachineController;
use App\Http\Controllers\Api\CacheControllers\BedController;
use App\Http\Controllers\Api\CacheControllers\BedTypeController;
use App\Http\Controllers\Api\CacheControllers\EmployeeController;
use App\Http\Controllers\Api\CacheControllers\RoleController;
use App\Http\Controllers\Api\CacheControllers\EthnicController;
use App\Http\Controllers\Api\CacheControllers\PriorityTypeController;
use App\Http\Controllers\Api\CacheControllers\RelationController;
use App\Http\Controllers\Api\CacheControllers\CareerController;
use App\Http\Controllers\Api\CacheControllers\ReligionController;
use App\Http\Controllers\Api\CacheControllers\ServiceReqTypeController;
use App\Http\Controllers\Api\CacheControllers\SaleProfitCfgController;
use App\Http\Controllers\Api\CacheControllers\ServiceConditionController;
use App\Http\Controllers\Api\CacheControllers\ServiceMachineController;
use App\Http\Controllers\Api\CacheControllers\ServiceRoomController;
use App\Http\Controllers\Api\CacheControllers\ServiceFollowController;
use App\Http\Controllers\Api\CacheControllers\BedBstyController;
use App\Http\Controllers\Api\CacheControllers\ServSegrController;
use App\Http\Controllers\Api\CacheControllers\ServiceGroupController;
use App\Http\Controllers\Api\CacheControllers\InfoUserController;
use App\Http\Controllers\Api\CacheControllers\ExecuteRoleUserController;
use App\Http\Controllers\Api\CacheControllers\ModuleRoleController;
use App\Http\Controllers\Api\CacheControllers\MestPatientTypeController;
use App\Http\Controllers\Api\CacheControllers\MediStockMetyController;
use App\Http\Controllers\Api\CacheControllers\MediStockMatyController;
use App\Http\Controllers\Api\CacheControllers\MestRoomController;
use App\Http\Controllers\Api\CacheControllers\ExroRoomController;
use App\Http\Controllers\Api\CacheControllers\PatientTypeRoomController;
use App\Http\Controllers\Api\CacheControllers\PatientTypeAllowController;
use App\Http\Controllers\Api\CacheControllers\PositionController;
use App\Http\Controllers\Api\CacheControllers\WorkPlaceController;
use App\Http\Controllers\Api\CacheControllers\BornPositionController;
use App\Http\Controllers\Api\CacheControllers\PatientCaseController;
use App\Http\Controllers\Api\CacheControllers\BhytParamController;
use App\Http\Controllers\Api\CacheControllers\BhytBlacklistController;
use App\Http\Controllers\Api\CacheControllers\MedicinePatyController;
use App\Http\Controllers\Api\CacheControllers\AccidentBodyPartController;
use App\Http\Controllers\Api\CacheControllers\PreparationsBloodController;
use App\Http\Controllers\Api\CacheControllers\ContraindicationController;
use App\Http\Controllers\Api\CacheControllers\DosageFormController;
use App\Http\Controllers\Api\CacheControllers\AccidentLocationController;
use App\Http\Controllers\Api\CacheControllers\LicenseClassController;
use App\Http\Controllers\Api\CacheControllers\ManufacturerController;
use App\Http\Controllers\Api\CacheControllers\IcdController;
use App\Http\Controllers\Api\CacheControllers\MediRecordTypeController;
use App\Http\Controllers\Api\CacheControllers\FileTypeController;
use App\Http\Controllers\Api\CacheControllers\TreatmentEndTypeController;
use App\Http\Controllers\Api\CacheControllers\TranPatiTechController;
use App\Http\Controllers\Api\CacheControllers\DebateReasonController;
use App\Http\Controllers\Api\CacheControllers\CancelReasonController;
use App\Http\Controllers\Api\CacheControllers\InteractionReasonController;
use App\Http\Controllers\Api\CacheControllers\UnlimitReasonController;
use App\Http\Controllers\Api\CacheControllers\HospitalizeReasonController;
use App\Http\Controllers\Api\CacheControllers\ExpMestReasonController;
use App\Http\Controllers\Api\CacheControllers\CareerTitleController;
use App\Http\Controllers\Api\CacheControllers\AccidentHurtTypeController;
use App\Http\Controllers\Api\CacheControllers\SupplierController;
use App\Http\Controllers\Api\CacheControllers\DeathWithinController;
use App\Http\Controllers\Api\CacheControllers\LocationStoreController;
use App\Http\Controllers\Api\CacheControllers\AccidentCareController;
use App\Http\Controllers\Api\CacheControllers\PtttTableController;
use App\Http\Controllers\Api\CacheControllers\EmotionlessMethodController;
use App\Http\Controllers\Api\CacheControllers\PtttCatastropheController;
use App\Http\Controllers\Api\CacheControllers\PtttConditionController;
use App\Http\Controllers\Api\CacheControllers\AwarenessController;
use App\Http\Controllers\Api\CacheControllers\MedicineLineController;
use App\Http\Controllers\Api\CacheControllers\BloodVolumeController;
use App\Http\Controllers\Api\CacheControllers\MedicineUseFormController;
use App\Http\Controllers\Api\CacheControllers\BidTypeController;
use App\Http\Controllers\Api\CacheControllers\MedicineTypeAcinController;
use App\Http\Controllers\Api\CacheControllers\AtcGroupController;
use App\Http\Controllers\Api\CacheControllers\BloodGroupController;
use App\Http\Controllers\Api\CacheControllers\MedicineGroupController;
use App\Http\Controllers\Api\CacheControllers\TestIndexController;
use App\Http\Controllers\Api\CacheControllers\TestIndexUnitController;
use App\Http\Controllers\Api\CacheControllers\DebateTypeController;
use App\Http\Controllers\Api\CacheControllers\IcdGroupController;
use App\Http\Controllers\Api\CacheControllers\AgeTypeController;
use App\Http\Controllers\Api\CacheControllers\MedicineController;
use App\Http\Controllers\Api\CacheControllers\MedicineTypeController;
use App\Http\Controllers\Api\CacheControllers\MaterialTypeController;
use App\Http\Controllers\Api\CacheControllers\ModuleController;
// Base Api
use App\Http\Controllers\BaseControllers\CacheController;
use App\Http\Controllers\BaseControllers\ElasticSearchController;
use App\Http\Controllers\BaseControllers\LogController;
use App\Http\Controllers\BaseControllers\TelegramController;
use App\Http\Controllers\BaseControllers\BaseApiRequestController;
// Data Controllers
use App\Http\Controllers\Api\DataControllers\DebateController;
use App\Http\Controllers\Api\DataControllers\DebateUserController;
use App\Http\Controllers\Api\DataControllers\DebateEkipUserController;
use App\Http\Controllers\Api\DataControllers\DhstController;
use App\Http\Controllers\Api\DataControllers\PatientTypeAlterController;
use App\Http\Controllers\Api\DataControllers\SereServController;
use App\Http\Controllers\Api\DataControllers\ServiceReqController;
use App\Http\Controllers\Api\DataControllers\SereServExtController;
use App\Http\Controllers\Api\DataControllers\SereServTeinController;
use App\Http\Controllers\Api\DataControllers\TrackingController;
use App\Http\Controllers\Api\DataControllers\TreatmentController;
use App\Http\Controllers\Api\DataControllers\TreatmentBedRoomController;
use App\Http\Controllers\Api\DataControllers\UserRoomController;
use App\Http\Controllers\Api\DataControllers\SereServBillController;
use App\Http\Controllers\Api\DataControllers\SereServDepositController;
use App\Http\Controllers\Api\DataControllers\SeseDepoRepayController;
use App\Http\Controllers\Api\DataControllers\AccountBookController;

// Validate Controllers
use App\Http\Controllers\Api\ValidateControllers\CheckBedRoomController;
use App\Http\Controllers\Api\ValidateControllers\CheckAreaController;
use App\Http\Controllers\Api\ValidateControllers\CheckBranchController;
use App\Http\Controllers\Api\ValidateControllers\CheckBodyPartController;
use App\Http\Controllers\Api\ValidateControllers\CheckCashierRoomController;
use App\Http\Controllers\Api\ValidateControllers\CheckCommuneController;
use App\Http\Controllers\Api\ValidateControllers\CheckDepartmentController;
use App\Http\Controllers\Api\ValidateControllers\CheckDistrictController;
use App\Http\Controllers\Api\ValidateControllers\CheckDataStoreController;
use App\Http\Controllers\Api\ValidateControllers\CheckExecuteRoomController;
use App\Http\Controllers\Api\ValidateControllers\CheckExecuteGroupController;
use App\Http\Controllers\Api\ValidateControllers\CheckExecuteRoleController;
use App\Http\Controllers\Api\ValidateControllers\CheckIcdCmController;
use App\Http\Controllers\Api\ValidateControllers\CheckMediStockController;
use App\Http\Controllers\Api\ValidateControllers\CheckMediOrgController;
use App\Http\Controllers\Api\ValidateControllers\CheckNationalController;
use App\Http\Controllers\Api\ValidateControllers\CheckOtherPaySourceController;
use App\Http\Controllers\Api\ValidateControllers\CheckPatientClassifyController;
use App\Http\Controllers\Api\ValidateControllers\CheckProvinceController;
use App\Http\Controllers\Api\ValidateControllers\CheckReceptionRoomController;
use App\Http\Controllers\Api\ValidateControllers\CheckRefectoryController;
use App\Http\Controllers\Api\ValidateControllers\CheckSpecialityController;
use App\Http\Controllers\Api\ValidateControllers\CheckServiceController;
use App\Http\Controllers\Api\ValidateControllers\CheckTreatmentTypeController;
use App\Http\Controllers\Api\CacheControllers\ProcessingMethodController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/
Route::get("v1/info", function () { return phpinfo();})->name('.get_info');
Route::get("v1/test", function () { return microtime(true) - LARAVEL_START;})->name('.get_test');
Route::fallback(function () {
    return return_404_error_page_not_found();
});
Route::group([
    "middleware" => ["check_admin:api"]
], function () {
    /// Telegram
    Route::get('v1/updated-activity', [TelegramController::class, "updated_activity"])->name('.updated_activity');
    /// Log
    Route::get("v1/log", [LogController::class, "getLog"])->name('.get_log');
    /// Request
    Route::get("v1/get-all-request-name", [BaseApiRequestController::class, "getAllRequestname"])->name('.get_all_request_name');
    /// Cache
    Route::get("v1/clear-cache", [CacheController::class, "clearCache"])->name('.clear_cache');
    Route::get("v1/clear-cache-elastic-index-keyword", [CacheController::class, "clearCacheElaticIndexKeyword"])->name('.clear_cache_elatic_index_keyword');
    /// Elastic Search
    Route::get("v1/get-all-name", [ElasticSearchController::class, "get_all_name"])->name('.get_all_name');
    Route::get("v1/index-records-to-elasticsearch", [ElasticSearchController::class, "index_records_to_elasticsearch"])->name('.index_records_to_elasticsearch');
    Route::get("v1/get-mapping", [ElasticSearchController::class, "get_mapping"])->name('.get_mapping');
    Route::get("v1/get-setting", [ElasticSearchController::class, "get_index_settings"])->name('.get_index_settings');
    Route::get("v1/set-max-result-window", [ElasticSearchController::class, "setMaxResultWindow"])->name('.set_max_result_window');
    Route::get("v1/check-node", [ElasticSearchController::class, "checkNodes"])->name('.check_nodes');
    Route::delete("v1/delete-index", [ElasticSearchController::class, "delete_index"])->name('.delete_index');
});
Route::group([
    "middleware" => ["check_module:api"]
], function () {
    /// Bộ phận thương tích
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentBodyPart'], function () {
        Route::apiResource('v1/accident-body-part', AccidentBodyPartController::class);
    });
    /// Xử lý sau tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentCare'], function () {
        Route::apiResource('v1/accident-care', AccidentCareController::class);
    });
    /// Nguyên nhân tai nạn 
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentHurtType'], function () {
        Route::apiResource('v1/accident-hurt-type', AccidentHurtTypeController::class);
    });
    /// Địa điểm tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentLocation'], function () {
        Route::apiResource('v1/accident-location', AccidentLocationController::class);
    });
    /// Loại tuổi
    Route::apiResource('v1/age-type', AgeTypeController::class)->only(['index', 'show']);
    /// Khu vực
    Route::group(['as' => 'HIS.Desktop.Plugins.HisArea'], function () {
        Route::apiResource('v1/area', AreaController::class);
    });
    /// Nhóm ATC
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAtcGroup'], function () {
        Route::apiResource('v1/atc-group', AtcGroupController::class);
    });
    /// Ý thức
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAwareness'], function () {
        Route::apiResource('v1/awareness', AwarenessController::class);
    });
    /// Giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBed'], function () {
        Route::apiResource('v1/bed', BedController::class);
    });
    /// Giường - Dịch vụ giường
    Route::group(['as' => 'HIS.Desktop.Plugins.BedBsty'], function () {
        Route::apiResource('v1/bed-bsty', BedBstyController::class)->only(['index', 'show', 'store']);
    });
    /// Buồng bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedRoomList'], function () {
        Route::apiResource('v1/bed-room', BedRoomController::class);
    });
    /// Loại giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedTypeList'], function () {
        Route::apiResource('v1/bed-type', BedTypeController::class)->only(['index', 'show']);
    });
    /// Thẻ BHYT không hợp lệ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBhytBlacklist'], function () {
        Route::apiResource('v1/bhyt-blacklist', BhytBlacklistController::class);
    });
    /// Tham số BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBHYTParam'], function () {
        Route::apiResource('v1/bhyt-param', BhytParamController::class);
    });
    /// Đầu mã thẻ BHYT
    Route::group(['as' => 'BHYT HIS.Desktop.Plugins.HisBhytWhitelist'], function () {
        Route::apiResource('v1/bhyt-whitelist', BhytWhitelistController::class);
    });
    /// Loại thầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBidType'], function () {
        Route::apiResource('v1/bid-type', BidTypeController::class);
    });
    /// Nhóm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodGroup'], function () {
        Route::apiResource('v1/blood-group', BloodGroupController::class);
    });
    /// Dung tích túi máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodVolume'], function () {
        Route::apiResource('v1/blood-volume', BloodVolumeController::class);
    });
    /// Bộ phận cơ thể
    Route::apiResource('v1/body-part', BodyPartController::class);
    /// Ngôi thai
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBornPosition'], function () {
        Route::apiResource('v1/born-position', BornPositionController::class);
    });
    /// Cơ sở/Xã phường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBranch'], function () {
        Route::apiResource('v1/branch', BranchController::class);
    });
    /// Lý do hủy giao dịch
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCancelReason'], function () {
        Route::apiResource('v1/cancel-reason', CancelReasonController::class);
    });
    /// Nghề nghiệp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCareer'], function () {
        Route::apiResource('v1/career', CareerController::class);
    });
    /// Nghề nghiệp nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.CareerTitle'], function () {
        Route::apiResource('v1/career-title', CareerTitleController::class);
    });
    /// Phòng thu ngân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCashierRoom'], function () {
        Route::apiResource('v1/cashier-room', CashierRoomController::class);
    });
    /// Xã
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaCommune'], function () {
        Route::apiResource('v1/commune', CommuneController::class);
    });
    /// Chống chỉ định
    Route::group(['as' => 'HIS.Desktop.Plugins.HisContraindication'], function () {
        Route::apiResource('v1/contraindication', ContraindicationController::class);
    });
    /// Tủ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDataStore'], function () {
        Route::apiResource('v1/data-store', DataStoreController::class);
    });
    /// Thời gian tử vong
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDeathWithin'], function () {
        Route::apiResource('v1/death-within', DeathWithinController::class);
    });
    /// Lý do hội chẩn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDebateReason'], function () {
        Route::apiResource('v1/debate-reason', DebateReasonController::class);
    });
    // Loại hội chẩn
    Route::apiResource('v1/debate-type', DebateTypeController::class)->only(['index', 'show']);
    /// Khoa phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDepartment'], function () {
        Route::apiResource('v1/department', DepartmentController::class);
    });
    /// Loại chẩn đoán hình ảnh
    Route::apiResource('v1/diim-type', DiimTypeController::class)->only(['index', 'show']);
    /// Huyện
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaDistrict'], function () {
        Route::apiResource('v1/district', DistrictController::class);
    });
    /// Dạng bào chế
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDosageForm'], function () {
        Route::apiResource('v1/dosage-form', DosageFormController::class);
    });
    /// Phương pháp vô cảm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisEmotionlessMethod'], function () {
    });
    /// Tài khoản nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.EmpUser'], function () {
        Route::apiResource('v1/emp-user', EmployeeController::class);
    });
    /// Dân tộc
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaEthnic'], function () {
        Route::apiResource('v1/ethnic', EthnicController::class);
    });
    /// Nhóm thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteGroup'], function () {
        Route::apiResource('v1/execute-group', ExecuteGroupController::class);
    });
    /// Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRole'], function () {
        Route::apiResource('v1/execute-role', ExecuteRoleController::class);
    });
    /// Tài khoản - Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.ExecuteRoleUser'], function () {
        Route::apiResource('v1/execute-role-user', ExecuteRoleUserController::class)->only(['index', 'show', 'store']);
    });
    /// Phòng khám/cls/pttt
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRoom'], function () {
        Route::apiResource('v1/execute-room', ExecuteRoomController::class);
    });
    /// Module xử lý dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExeServiceModule'], function () {
        Route::apiResource('v1/exe-service-module', ExeServiceModuleController::class)->only(['index', 'show']);
    });
    /// Lý do xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExpMestReason'], function () {
        Route::apiResource('v1/exp-mest-reason', ExpMestReasonController::class)->only(['index', 'show']);
    });
    /// Phòng chỉ định - Phòng thực hiện 
    Route::group(['as' => 'HIS.Desktop.Plugins.ExroRoom'], function () {
        Route::apiResource('v1/exro-room', ExroRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Loại giấy tờ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisFileType'], function () {
        Route::apiResource('v1/file-type', FileTypeController::class);
    });
    /// Cỡ phim
    Route::group(['as' => 'HIS.Desktop.Plugins.HisFilmSize'], function () {
        Route::apiResource('v1/film-size', FilmSizeController::class)->only(['index', 'show']);
    });
    /// Loại thăm dò chức năng
    Route::apiResource('v1/fuex-type', FuexTypeController::class)->only(['index', 'show']);
    /// Giới tính
    Route::apiResource('v1/gender', GenderController::class)->only(['index', 'show']);
    /// Đơn vị
    Route::apiResource('v1/group', GroupController::class)->only(['index', 'show']);
    /// Nhóm dịch vụ BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisHeinServiceType'], function () {
        Route::apiResource('v1/hein-service-type', HeinServiceTypeController::class)->only(['index', 'show']);
    });
    /// Lý do nhập viện
    Route::group(['as' => 'HIS.Desktop.Plugins.HospitalizeReason'], function () {
        Route::apiResource('v1/hospitalize-reason', HospitalizeReasonController::class);
    });
    /// Icd - Cm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisIcdCm'], function () {
        Route::apiResource('v1/icd-cm', IcdCmController::class);
    });
    /// ICD - Accepted Icd - Chẩn đoán
    Route::group(['as' => 'HIS.Desktop.Plugins.HisIcd'], function () {
        Route::apiResource('v1/icd', IcdController::class);
    });
    /// Nhóm ICD
    Route::apiResource('v1/icd-group', IcdGroupController::class)->only(['index', 'show']);
    /// Thông tin tài khoản
    Route::group(['as' => 'HIS.Desktop.Plugins.InfoUser'], function () {
        Route::get("v1/info-user", [EmployeeController::class, "infoUser"])->name('.get_info_user');
        Route::put("v1/info-user", [EmployeeController::class, "updateInfoUser"])->name('.update_info_user');
    });
    /// Lý do kê đơn tương tác
    Route::group(['as' => 'HIS.Desktop.Plugins.InteractionReason'], function () {
        Route::apiResource('v1/interaction-reason', InteractionReasonController::class);
    });
    /// Hạng lái xe
    Route::group(['as' => 'HIS.Desktop.Plugins.LicenseClass'], function () {
        Route::apiResource('v1/license-class', LicenseClassController::class);
    });
    /// Vị trí hồ sơ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.LocationTreatment'], function () {
        Route::apiResource('v1/location-treatment', LocationStoreController::class);
    });
    /// Máy / Máy cận lâm sàn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMachine'], function () {
        Route::apiResource('v1/machine', MachineController::class);
    });
    /// Hãng sản xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisManufacturer'], function () {
        Route::apiResource('v1/manufacturer', ManufacturerController::class);
    });
    /// Loại vật tư
    Route::apiResource('v1/material-type', MaterialTypeController::class)->only(['index', 'show']);
    /// Thuốc
    Route::apiResource('v1/medicine', MedicineController::class)->only(['index', 'show']);
    /// Nhóm thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineGroup'], function () {
        Route::apiResource('v1/medicine-group', MedicineGroupController::class)->only(['index', 'show']);
    });
    /// Dòng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineLine'], function () {
        Route::apiResource('v1/medicine-line', MedicineLineController::class)->only(['index', 'show']);
    });
    /// Chính sách giá thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicinePaty'], function () {
        Route::apiResource('v1/medicine-paty', MedicinePatyController::class);
    });
    /// Loại thuốc - Hoạt chất
    Route::group(['as' => 'HIS.Desktop.Plugins.MedicineTypeActiveIngredient'], function () {
        Route::apiResource('v1/medicine-type-acin', MedicineTypeAcinController::class)->only(['index', 'show', 'store']);
    });
    /// Loại thuốc
    Route::apiResource('v1/medicine-type', MedicineTypeController::class)->only(['index', 'show']);
    /// Đường dùng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineUseForm'], function () {
        Route::apiResource('v1/medicine-use-form', MedicineUseFormController::class);
    });
    /// Cơ sở khám chữa bệnh ban đầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediOrg'], function () {
        Route::apiResource('v1/medi-org', MediOrgController::class);
    });
    /// Loại bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediRecordType'], function () {
        Route::apiResource('v1/medi-record-type', MediRecordTypeController::class);
    });
    /// Kho
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediStock'], function () {
        Route::apiResource('v1/medi-stock', MediStockController::class);
    });
    /// Kho - Loại vật tư
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMatyList'], function () {
        Route::apiResource('v1/medi-stock-maty-list', MediStockMatyController::class)->only(['index', 'show', 'store']);
    });
    /// Kho - Loại thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMetyList'], function () {
        Route::apiResource('v1/medi-stock-mety-list', MediStockMetyController::class)->only(['index', 'show', 'store']);
    });
    /// Kho - Đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestPatientType'], function () {
        Route::apiResource('v1/mest-patient-type', MestPatientTypeController::class)->only(['index', 'show', 'store']);
    });
    /// Kho - Phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestExportRoom'], function () {
        Route::apiResource('v1/mest-export-room', MestRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Quân hàm
    Route::apiResource('v1/military-rank', MilitaryRankController::class)->only(['index', 'show']);
    /// Chức năng
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModule'], function () {
        Route::apiResource('v1/module', ModuleController::class);
    });
    /// Vai trò - Chức năng 
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModuleRole'], function () {
        Route::apiResource('v1/module-role', ModuleRoleController::class)->only(['index', 'show', 'store']);
    });
    /// Quốc gia
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaNational'], function () {
        Route::apiResource('v1/national', NationalController::class);
    });
    /// Nguồn chi trả khác
    Route::group(['as' => 'HIS.Desktop.Plugins.HisOtherPaySource'], function () {
        Route::apiResource('v1/other-pay-source', OtherPaySourceController::class);
    });
    /// Gói
    Route::apiResource('v1/package', PackageController::class)->only(['index', 'show']);
    /// Trường hợp bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientCase'], function () {
        Route::apiResource('v1/patient-case', PatientCaseController::class)->only(['index', 'show']);
    });
    /// Phân loại bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientClassify'], function () {
        Route::apiResource('v1/patient-classify', PatientClassifyController::class);
    });
    /// Chuyển đổi đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeAllow'], function () {
        Route::apiResource('v1/patient-type-allow', PatientTypeAllowController::class);
    });
    /// Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientType'], function () {
        Route::apiResource('v1/patient-type', PatientTypeController::class);
    });
    /// Phòng thực hiện - Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeRoom'], function () {
        Route::apiResource('v1/patient-type-room', PatientTypeRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Chức vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPosition'], function () {
        Route::apiResource('v1/position', PositionController::class);
    });
    /// Chế phẩm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPreparationsBlood'], function () {
        Route::apiResource('v1/preparations-blood', PreparationsBloodController::class);
    });
    /// Đối tượng ưu tiên
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPriorityType'], function () {
        Route::apiResource('v1/priority-type', PriorityTypeController::class);
    });
    /// Phương pháp chế biến
    Route::group(['as' => 'HIS.Desktop.Plugins.HisProcessing'], function () {
        Route::apiResource('v1/processing-method', ProcessingMethodController::class)->only(['index', 'show']);
    });
    /// Tỉnh
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaProvince'], function () {
        Route::apiResource('v1/province', ProvinceController::class);
    });
    /// Tai biến PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttCatastrophe'], function () {
        Route::apiResource('v1/pttt-catastrophe', PtttCatastropheController::class);
    });
    /// Tình trạng PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttCondition'], function () {
        Route::apiResource('v1/pttt-condition', PtttConditionController::class);
    });
    /// Nhóm PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttGroup'], function () {
        Route::apiResource('v1/pttt-group', PtttGroupController::class);
    });
    /// Phương pháp PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttMethod'], function () {
        Route::apiResource('v1/pttt-method', PtttMethodController::class);
    });
    /// Bàn mổ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttTable'], function () {
        Route::apiResource('v1/pttt-table', PtttTableController::class);
    });
    /// Nhóm xuất ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationGroup'], function () {
        Route::apiResource('v1/ration-group', RationGroupController::class);
    });
    /// Bữa ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationTime'], function () {
        Route::apiResource('v1/ration-time', RationTimeController::class);
    });
    /// Khu đón tiếp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisReceptionRoom'], function () {
        Route::apiResource('v1/reception-room', ReceptionRoomController::class);
    });
    /// Nhà ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRefectory'], function () {
        Route::apiResource('v1/refectory', RefectoryController::class);
    });
    /// Mối quan hệ
    Route::group(['as' => 'HIS.Desktop.Plugins.EmrRelationList'], function () {
        Route::apiResource('v1/relation-list', RelationController::class);
    });
    /// Tôn giáo
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaReligion'], function () {
        Route::apiResource('v1/religion', ReligionController::class);
    });
    /// Vai trò
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsRole'], function () {
        Route::apiResource('v1/role', RoleController::class);
    });
    /// Phòng
    Route::apiResource('v1/room', RoomController::class)->only(['index', 'show']);
    /// Nhóm phòng
    Route::apiResource('v1/room-group', RoomGroupController::class)->only(['index', 'show', 'store']);
    /// Loại phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomTypeModule'], function () {
        Route::apiResource('v1/room-type', RoomTypeController::class)->only(['index', 'show']);
    });
    /// Thiết lập lợi nhuận xuất bán
    Route::group(['as' => 'HIS.Desktop.Plugins.EstablishSaleProfitCFG'], function () {
        Route::apiResource('v1/sale-profit-cfg', SaleProfitCfgController::class);
    });
    /// Điều kiện dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceCondition'], function () {
        Route::apiResource('v1/service-condition', ServiceConditionController::class);
    });
    /// Dịch vụ kỹ thuật
    Route::group(['as' => 'HIS.Desktop.Plugins.HisService'], function () {
        Route::apiResource('v1/service', ServiceController::class);
    });
    /// Dịch vụ đi kèm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceFollow'], function () {
        Route::apiResource('v1/service-follow', ServiceFollowController::class);
    });
    /// Nhóm dịch vụ
    Route::apiResource('v1/service-group', ServiceGroupController::class)->only(['index', 'show']);
    /// Dịch vụ máy
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceMachine'], function () {
        Route::apiResource('v1/service-machine', ServiceMachineController::class)->only(['index', 'show', 'store']);
    });
    /// Chính sách giá dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServicePatyList'], function () {
        Route::apiResource('v1/service-paty', ServicePatyController::class);
    });
    /// Loại y lệnh 
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqType'], function () {
        Route::apiResource('v1/service-req-type', ServiceReqTypeController::class)->only(['index', 'show']);
    });
    /// Dịch vụ phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomService'], function () {
        Route::apiResource('v1/service-room', ServiceRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Loại dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceType'], function () {
        Route::apiResource('v1/service-type', ServiceTypeController::class)->only(['index', 'show']);
    });
    /// Đơn vị tính
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceUnitEdit'], function () {
        Route::apiResource('v1/service-unit', ServiceUnitController::class);
    });
    /// Nhóm dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServSegr'], function () {
        Route::apiResource('v1/serv-segr', ServSegrController::class)->only(['index', 'show']);
    });
    /// Chuyên khoa
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSpeciality'], function () {
        Route::apiResource('v1/speciality', SpecialityController::class);
    });
    /// Chỉ số
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSuimIndex'], function () {
        Route::apiResource('v1/suim-index', SuimIndexController::class)->only(['index', 'show']);
    });
    /// Nhà cung cấp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSupplier'], function () {
        Route::apiResource('v1/supplier', SupplierController::class);
    });
    /// Chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndex'], function () {
        Route::apiResource('v1/test-index', TestIndexController::class)->only(['index', 'show']);
    });
    /// Đơn vị tính chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndexUnit'], function () {
        Route::apiResource('v1/test-index-unit', TestIndexUnitController::class)->only(['index', 'show']);
    });
    /// Loại mẫu bệnh phẩm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestSampleType'], function () {
        Route::apiResource('v1/test-sample-type', TestSampleTypeController::class)->only(['index', 'show']);
    });
    /// Loại xét nghiệm
    Route::apiResource('v1/test-type', TestTypeController::class)->only(['index', 'show']);
    /// Lý do chuyển tuyến chuyên môn
    Route::group(['as' => 'HIS.Desktop.Plugins.TranPatiTech'], function () {
        Route::apiResource('v1/tran-pati-tech', TranPatiTechController::class);
    });
    /// Loại ra viện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTreatmentEndType'], function () {
        Route::apiResource('v1/treatment-end-type', TreatmentEndTypeController::class);
    });
    /// Diện điều trị
    Route::group(['as' => 'HIS.Desktop.Plugins.TreatmentType'], function () {
        Route::apiResource('v1/treatment-type', TreatmentTypeController::class);
    });
    /// Lý do mở trần
    Route::group(['as' => 'HIS.Desktop.Plugins.HisUnlimitReason'], function () {
        Route::apiResource('v1/unlimit-reason', UnlimitReasonController::class);
    });
    /// Nơi làm việc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisWorkPlace'], function () {
        Route::apiResource('v1/work-place', WorkPlaceController::class);
    });

    /// Nhân viên - Phòng
    // Trả về nhân viên cùng phòng
    Route::get("v1/user-room/get-view", [UserRoomController::class, "user_with_room"])->name('.get-view');

    // Debate
    Route::group(['as' => 'HIS.Desktop.Plugins.Debate'], function () {
        Route::get("v1/debate/get", [DebateController::class, "debate_get"])->name('.get');
        Route::get("v1/debate/get-view", [DebateController::class, "debate_get_view"])->name('.get-view');

        Route::get("v2/debate/get", [DebateController::class, "debate_get_v2"])->name('.get_v2');
        Route::get("v2/debate/get-view", [DebateController::class, "debate_get_view_v2"])->name('.get_view_v2');
    });


    // Debate User
    Route::get("v1/debate-user/get", [DebateUserController::class, "debate_user"])->name('.get_debate_user');
    Route::get("v2/debate-user/get", [DebateUserController::class, "debate_user_v2"])->name('.get_debate_user_v2');


    // Debate Ekip User
    Route::get("v1/debate-ekip-user/get", [DebateEkipUserController::class, "debate_ekip_user"])->name('.get_debate_ekip_user');
    Route::get("v2/debate-ekip-user/get", [DebateEkipUserController::class, "debate_ekip_user_v2"])->name('.get_debate_ekip_user_v2');

    // Service Req
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqList'], function () {
        Route::get("v1/service-req/get-L-view", [ServiceReqController::class, "service_req_get_L_view"])->name('.get_L_view');
        Route::get("v2/service-req/get-L-view", [ServiceReqController::class, "service_req_get_L_view_v2"])->name('.get_L_view_v2');
        Route::get("v3/service-req/get-L-view", [ServiceReqController::class, "service_req_get_L_view_v3"])->name('.get_L_view_v3');
    });


    // Tracking
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTrackingList'], function () {
        Route::get("v1/tracking/get", [TrackingController::class, "tracking_get"])->name('.get');
        Route::get("v1/tracking/get-data", [TrackingController::class, "tracking_get_data"])->name('.get_data');

        Route::get("v2/tracking/get", [TrackingController::class, "tracking_get_v2"])->name('.get_v2');
        Route::get("v2/tracking/get-data", [TrackingController::class, "tracking_get_data_v2"])->name('.get_data_v2');
    });

    // Sere Serv
    Route::get("v1/sere-serv/get", [SereServController::class, "sere_serv_get"])->name('.get_sere_serv');

    Route::get("v2/sere-serv/get", [SereServController::class, "sere_serv_get_v2"])->name('.get_sere_serv_v2');
    Route::get("v2/sere-serv/get-count", [SereServController::class, "sere_serv_get_count_v2"])->name('.get_sere_serv_count_v2');

    Route::get("v3/sere-serv/get", [SereServController::class, "sere_serv_get_v3"])->name('.get_sere_serv_v3');
    Route::get("v3/sere-serv/get-count", [SereServController::class, "sere_serv_get_count_v3"])->name('.get_sere_serv_count_v3');

    Route::get("v1/sere-serv/get-view-5", [SereServController::class, "sere_serv_get_view_5"])->name('.get_sere_serv_view_5');


    // Patient Type Alter
    Route::group(['as' => 'HIS.Desktop.Plugins.CallPatientTypeAlter'], function () {
        Route::get("v1/patient-type-alter/get-view", [PatientTypeAlterController::class, "patient_type_alter_get_view"])->name('.get_view');
        Route::get("v2/patient-type-alter/get-view", [PatientTypeAlterController::class, "patient_type_alter_get_view_v2"])->name('.get_view_v2');
    });

    // Treatment
    Route::get("v1/treatment/get-L-view", [TreatmentController::class, "treatment_get_L_view"])->name('.get_treatment_L_view');
    Route::get("v1/treatment/get-treatment-with-patient-type-info-sdo", [TreatmentController::class, "treatment_get_treatment_with_patient_type_info_sdo"])->name('.get_treatment_treatment');
    Route::get("v1/treatment/get-fee-view", [TreatmentController::class, "treatment_get_fee_view"])->name('.get_treatment_fee_view');

    Route::get("v2/treatment/get-L-view", [TreatmentController::class, "treatment_get_L_view_v2"])->name('.get_treatment_L_view_v2');
    Route::get("v2/treatment/get-treatment-with-patient-type-info-sdo", [TreatmentController::class, "treatment_get_treatment_with_patient_type_info_sdo_v2"])->name('.get_treatment_treatment_v2');

    // Treatment Bed Room
    Route::group(['as' => 'HIS.Desktop.Plugins.TreatmentBedRoomList'], function () {
        Route::get("v1/treatment-bed-room/get-L-view", [TreatmentBedRoomController::class, "treatment_bed_room_get_L_view"])->name('.get_L_view');
        Route::get("v2/treatment-bed-room/get-L-view", [TreatmentBedRoomController::class, "treatment_bed_room_get_L_view_v2"])->name('.get_L_view_v2');
    });

    // DHST
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDhst'], function () {
        Route::get("v1/dhst/get", [DhstController::class, "dhst_get"])->name('.get');
        Route::get("v2/dhst/get", [DhstController::class, "dhst_get_v2"])->name('.get_v2');
        Route::get("v3/dhst/get", [DhstController::class, "dhst_get_v3"])->name('.get_v3');
    });


    // Sere Serv Ext
    Route::get("v1/sere-serv-ext/get", [SereServExtController::class, "sere_serv_ext"])->name('.get_sere_serv_ext');
    Route::get("v2/sere-serv-ext/get", [SereServExtController::class, "sere_serv_ext_v2"])->name('.get_sere_serv_ext_v2');

    // Sere Serv Tein
    Route::group(['as' => 'HIS.Desktop.Plugins.SereServTein'], function () {
        Route::get("v1/sere-serv-tein/get", [SereServTeinController::class, "sere_serv_tein_get"])->name('.get');
        Route::get("v1/sere-serv-tein/get-view", [SereServTeinController::class, "sere_serv_tein_get_view"])->name('.get_view');

        Route::get("v2/sere-serv-tein/get", [SereServTeinController::class, "sere_serv_tein_get_v2"])->name('.get_v2');
        Route::get("v2/sere-serv-tein/get-view", [SereServTeinController::class, "sere_serv_tein_get_view_v2"])->name('.get_view_v2');
    });


    // Sere Serv Bill
    Route::get("v1/sere-serv-bill/get", [SereServBillController::class, "sere_serv_bill_get"])->name('.get_sere_serv_bill');

    // Sere Serv Deposit
    Route::get("v1/sere-serv-deposit/get-view", [SereServDepositController::class, "sere_serv_deposit_get_view"])->name('.get_view_sere_serv_deposit');

    // Sese Depo Repay
    Route::get("v1/sese-depo-repay/get-view", [SeseDepoRepayController::class, "sese_depo_repay_get_view"])->name('.get_view_sese_depo_repay');

    // Account Book
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccountBookList'], function () {
        Route::get("v1/account-book/get-view", [AccountBookController::class, "account_book_get_view"])->name('.get_view');
    });
});
