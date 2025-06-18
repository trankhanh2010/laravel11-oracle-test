<?php

use App\Events\Job\JobIndexElasticInfo;
use App\Events\Transaction\MoMoNotificationTamUngReceived;
use App\Http\Controllers\Api\AuthControllers\CheckTokenController;
use App\Http\Controllers\Api\NoCacheControllers\SereServExtController;
use App\Http\Controllers\Api\NoCacheControllers\ThuocVatTuBeanVViewController;
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
use App\Http\Controllers\Api\CacheControllers\AppointmentPeriodController;
use App\Http\Controllers\Api\CacheControllers\AtcController;
use App\Http\Controllers\Api\CacheControllers\BidController;
use App\Http\Controllers\Api\CacheControllers\ConfigController;
use App\Http\Controllers\Api\CacheControllers\DeathCauseController;
use App\Http\Controllers\Api\CacheControllers\DeathCertBookController;
use App\Http\Controllers\Api\CacheControllers\DocumentBookController;
use App\Http\Controllers\Api\CacheControllers\DocumentTypeController;
use App\Http\Controllers\Api\CacheControllers\EmrCoverTypeController;
use App\Http\Controllers\Api\CacheControllers\EmrFormController;
use App\Http\Controllers\Api\CacheControllers\EquipmentSetController;
use App\Http\Controllers\Api\CacheControllers\ExpMestTemplateController;
use App\Http\Controllers\Api\CacheControllers\FundController;
use App\Http\Controllers\Api\CacheControllers\GroupTypeController;
use App\Http\Controllers\Api\CacheControllers\HealthExamRankController;
use App\Http\Controllers\Api\CacheControllers\HtuController;
use App\Http\Controllers\Api\CacheControllers\IcdListVViewController;
use App\Http\Controllers\Api\CacheControllers\ImpSourceController;
use App\Http\Controllers\Api\CacheControllers\KskContractController;
use App\Http\Controllers\Api\CacheControllers\MedicineController;
use App\Http\Controllers\Api\CacheControllers\MedicineTypeController;
use App\Http\Controllers\Api\CacheControllers\MaterialTypeController;
use App\Http\Controllers\Api\CacheControllers\MaterialTypeMapController;
use App\Http\Controllers\Api\CacheControllers\MedicalContractController;
use App\Http\Controllers\Api\CacheControllers\MemaGroupController;
use App\Http\Controllers\Api\CacheControllers\ModuleController;
use App\Http\Controllers\Api\CacheControllers\NextTreaIntrController;
use App\Http\Controllers\Api\CacheControllers\PackingTypeController;
use App\Http\Controllers\Api\CacheControllers\PayFormController;
use App\Http\Controllers\Api\CacheControllers\Phieutdvacsbnc2PhieumauController;
use App\Http\Controllers\Api\CacheControllers\ProcessingMethodController;
use App\Http\Controllers\Api\CacheControllers\RepayReasonController;
use App\Http\Controllers\Api\CacheControllers\ServiceReqSttController;
use App\Http\Controllers\Api\CacheControllers\SpeedUnitController;
use App\Http\Controllers\Api\CacheControllers\StorageConditionController;
use App\Http\Controllers\Api\CacheControllers\SuimIndexUnitController;
use App\Http\Controllers\Api\CacheControllers\TestIndexGroupController;
use App\Http\Controllers\Api\CacheControllers\TranPatiFormController;
use App\Http\Controllers\Api\CacheControllers\TranPatiReasonController;
use App\Http\Controllers\Api\CacheControllers\TranPatiTempController;
use App\Http\Controllers\Api\TransactionControllers\TransactionTamUngController;
use App\Http\Controllers\Api\CacheControllers\TransactionTypeController;
use App\Http\Controllers\Api\CacheControllers\TreatmentEndTypeExtController;
use App\Http\Controllers\Api\CacheControllers\TreatmentResultController;
use App\Http\Controllers\Api\CacheControllers\VaccineTypeController;
use App\Http\Controllers\Api\NoCacheControllers\AccountBookVViewController;
use App\Http\Controllers\Api\NoCacheControllers\BangKeVViewController;
use App\Http\Controllers\Api\NoCacheControllers\CareController;
// No cache Controller
use App\Http\Controllers\Api\NoCacheControllers\DebateController;
use App\Http\Controllers\Api\NoCacheControllers\DebateDetailVViewController;
use App\Http\Controllers\Api\NoCacheControllers\DebateEkipUserController;
use App\Http\Controllers\Api\NoCacheControllers\DebateListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\DebateUserController;
use App\Http\Controllers\Api\NoCacheControllers\DebateVViewController;
use App\Http\Controllers\Api\NoCacheControllers\DepositReqListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\DhstController;
use App\Http\Controllers\Api\NoCacheControllers\DocumentListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\DonVViewController;
use App\Http\Controllers\Api\NoCacheControllers\MedicalCaseCoverListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\MedicineListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\PatientTypeAlterVViewController;
use App\Http\Controllers\Api\NoCacheControllers\ResultClsVViewController;
use App\Http\Controllers\Api\NoCacheControllers\RoomVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServBillController;
use App\Http\Controllers\Api\NoCacheControllers\SereServClsListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServController;
use App\Http\Controllers\Api\NoCacheControllers\SereServDepositVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServDetailVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServTeinChartsVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServTeinController;
use App\Http\Controllers\Api\NoCacheControllers\SereServTeinListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServTeinVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SereServVView4Controller;
use App\Http\Controllers\Api\NoCacheControllers\ServiceReqListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\ServiceReqLViewController;
use App\Http\Controllers\Api\NoCacheControllers\SeseDepoRepayVViewController;
use App\Http\Controllers\Api\NoCacheControllers\SignerController;
use App\Http\Controllers\Api\NoCacheControllers\TestServiceReqListVView2Controller;
use App\Http\Controllers\Api\NoCacheControllers\TestServiceReqListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TrackingController;
use App\Http\Controllers\Api\NoCacheControllers\TrackingDataController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentBedRoomLViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentFeeViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentLViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentWithPatientTypeInfoSdoController;
use App\Http\Controllers\Api\NoCacheControllers\UserRoomVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TestServiceTypeListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TrackingListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TransactionTTDetailVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TransactionListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TransactionTUDetailVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentBedRoomListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentExecuteRoomListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentFeeDetailVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentFeeListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentListVViewController;
use App\Http\Controllers\Api\NoCacheControllers\TreatmentRoomGroupVViewController;
use App\Http\Controllers\Api\NoCacheControllers\UserAccountBookVViewController;
use App\Http\Controllers\Api\NoCacheControllers\YeuCauKhamClsPtttDataController;
use App\Http\Controllers\Api\NoCacheControllers\YeuCauKhamClsPtttVViewController;
// Base Api
use App\Http\Controllers\BaseControllers\CacheController;
use App\Http\Controllers\BaseControllers\ElasticSearchController;
use App\Http\Controllers\BaseControllers\LogController;
use App\Http\Controllers\BaseControllers\TelegramController;
use App\Http\Controllers\BaseControllers\BaseApiRequestController;

// Validate Controllers
use App\Http\Controllers\Api\ValidateControllers\CheckBedController;
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
use App\Http\Controllers\BaseControllers\RedisController;
use Illuminate\Support\Facades\DB;

// Transaction
use App\Http\Controllers\Api\TransactionControllers\TreatmentFeePayMentController;
use App\Http\Controllers\Api\TransactionControllers\MoMoController;
use App\Http\Controllers\Api\TransactionControllers\TransactionCancelController;
use App\Http\Controllers\Api\TransactionControllers\TransactionController;
use App\Http\Controllers\Api\TransactionControllers\TransactionHoanUngController;
use App\Http\Controllers\Api\TransactionControllers\TransactionHoanUngDichVuController;
use App\Http\Controllers\Api\TransactionControllers\TransactionTamThuDichVuController;
use App\Http\Controllers\Api\TransactionControllers\TransactionThanhToanController;
use App\Http\Controllers\Api\TransactionControllers\VietinbankController;
use App\Http\Controllers\Api\TransactionControllers\TransactionRestoreController;
use App\Http\Controllers\Api\ValidateControllers\DeviceGetOtpController;
use App\Http\Controllers\Api\ValidateControllers\OtpController;
use App\Http\Controllers\BaseControllers\ConvertController;
use App\Http\Controllers\BaseControllers\DigitalCertificateController;
use App\Http\Controllers\BaseControllers\XmlController;

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

Route::get("v1/info", function () {
    return phpinfo();
})->name('.get_info');
Route::get("v1/test", function () {
    return microtime(true) - LARAVEL_START;
})->name('.get_test');
Route::get("v1/test-db", function () {
    $start = microtime(true);
    DB::connection('oracle_his')->getPdo();
    $end = microtime(true);
    $executionTime = ($end - $start) * 1000;
    return $executionTime;
})->name('.get_test_db');
// Websocket
Route::get("v1/test-wss", function () {
    broadcast(new MoMoNotificationTamUngReceived('test'));
})->name('.test_wss');
Route::get("v1/test-wss-index-elastic", function () {
    broadcast(new JobIndexElasticInfo('test'));
})->name('.test_wss_index_elastic');

Route::get('v1/check-token', [CheckTokenController::class, 'index']);
Route::get('v1/log-out', [CheckTokenController::class, 'logOut']);

Route::fallback(function () {
    return return_404_error_page_not_found();
});

/// Verify mã OTP
Route::get('v1/check-otp-treatment-fee', [OtpController::class, 'verifyOtpTreatmentFee'])
    ->withoutMiddleware('check_token');
/// Gửi OTP    
Route::get('v1/send-otp-treatment-fee', [OtpController::class, 'sendOtpTreatmentFee'])
    ->withoutMiddleware('check_token');

// refresh AccessToken OA zalo
Route::get('v1/refresh-access-token-otp-zalo', [OtpController::class, 'refreshAccessTokenOtpZalo'])
    ->withoutMiddleware('check_token');
// cập nhật token zalo trong db
Route::get('v1/set-token-otp-zalo', [OtpController::class, 'setTokenOtpZalo'])
    ->withoutMiddleware('check_token');
// // Lấy AccessToken và RefreshToken từ đầu
// Route::get('v1/get-access-and-refresh-token', [OtpController::class, 'getAccessAndRefreshToken'])
//     ->withoutMiddleware('check_token');

/// MOMO nofity ipn
// Thông báo trạng thái thanh toán /// k cần token
Route::post('v1/momo-notify-thanh-toan', [MoMoController::class, 'handleNotificationThanhToan'])
    ->withoutMiddleware('check_token');
// Thông báo trạng thái tạm ứng /// k cần token
Route::post('v1/momo-notify-tam-ung', [MoMoController::class, 'handleNotificationTamUng'])
    ->withoutMiddleware('check_token');
// check trạng thái payment momo
Route::get('v1/check-transaction', [TreatmentFeePayMentController::class, 'checkTransactionStatus'])
    ->withoutMiddleware('check_token');

/// VietinBank
// Xác nhận giao dịch
Route::post('v1/vietinbank-confirm-transaction', [VietinbankController::class, 'handleConfirmTransaction'])
    ->withoutMiddleware('check_token');
// Vấn tin giao dịch
Route::post('v1/vietinbank-inq-detail-trans', [VietinbankController::class, 'handleInqDetailTrans'])
    ->withoutMiddleware('check_token');
/// Request
Route::get("v1/get-column-name", [BaseApiRequestController::class, "getColumnname"])->name('.get_column_name')
    ->withoutMiddleware('check_token');
Route::group([
    "middleware" => ["check_admin:api"]
], function () {
    // Kiểm tra => tạo mới hoặc gia hạn Root-CA
    Route::get('v1/certificates', [DigitalCertificateController::class, 'certificates']);
    Route::get('v1/renew-certificate', [DigitalCertificateController::class, 'renewCertificate']);
    Route::get('v1/revoke-certificate', [DigitalCertificateController::class, 'revokeCertificate']);
    Route::get('v1/get-certificate-info', [DigitalCertificateController::class, 'getCertificateInfo']);
    Route::get('v1/sign', [DigitalCertificateController::class, 'sign']);
    Route::get('v1/sign-xml', [DigitalCertificateController::class, 'signXML']);
    Route::get('v1/multi-sign-xml', [DigitalCertificateController::class, 'multiSignXML']);

    /// Redis
    Route::get('v1/redis-ping', [RedisController::class, "ping"])->name('.redis_ping');
    /// PDF
    /// Chuyển TXT sang word
    Route::get('v1/convert-sar-print-to-word/{id}', [ConvertController::class, "convertSarPrintToWord"])->name('.convert_sar_print_to_word');
    /// Tách header và content file pdf
    Route::get('v1/split-header-content-file-pdf', [ConvertController::class, "splitHeaderContentFilePDF"])->name('.split_header_content_file_pdf');
    Route::get('v1/merge-content-document', [ConvertController::class, "mergeContentDocument"])->name('.merge_content_document');

    /// Telegram
    Route::get('v1/updated-activity', [TelegramController::class, "updated_activity"])->name('.updated_activity');
    Route::get('v1/test-send-mess-to-chanel-telegram', [TelegramController::class, "testSendMessToChanelTelegram"])->name('.test_send_mess_to_chanel_telegram');
    /// Log
    Route::get("v1/log", [LogController::class, "getLog"])->name('.get_log');
    /// Request
    Route::get("v1/get-all-request-name", [BaseApiRequestController::class, "getAllRequestname"])->name('.get_all_request_name');
});
Route::group([
    "middleware" => ["check_module:api"]
], function () {
    /// Cache
    Route::group(['as' => 'CauHinhCacheRedisHeThong->'], function () { // link không có trong module => luôn trả về false => nếu là spAdmin thì qua được
        Route::get("v1/clear-cache", [CacheController::class, "clearCache"])->name('.clear_cache');
        Route::get("v1/clear-cache-elastic-index-keyword", [CacheController::class, "clearCacheElaticIndexKeyword"])->name('.clear_cache_elatic_index_keyword');
    });

    /// Elastic Search
    Route::group(['as' => 'CauHinhElasticHeThong->'], function () { // link không có trong module => luôn trả về false => nếu là spAdmin thì qua được
        Route::get("v1/elastic-ping", [ElasticSearchController::class, "ping"])->name('.elastic_ping');
        Route::get("v1/get-all-name", [ElasticSearchController::class, "getAllName"])->name('.get_all_name');
        Route::get("v1/index-records-to-elasticsearch", [ElasticSearchController::class, "indexRecordsToElasticsearch"])->name('.index_records_to_elasticsearch');
        Route::get("v1/get-mapping", [ElasticSearchController::class, "getMapping"])->name('.get_mapping');
        Route::get("v1/get-setting", [ElasticSearchController::class, "getIndexSettings"])->name('.get_index_settings');
        Route::get("v1/set-max-result-window", [ElasticSearchController::class, "setMaxResultWindow"])->name('.set_max_result_window');
        Route::get("v1/check-node", [ElasticSearchController::class, "checkNodes"])->name('.check_nodes');
        Route::delete("v1/delete-index", [ElasticSearchController::class, "deleteIndex"])->name('.delete_index');
        Route::get("v1/get-docs-count", [ElasticSearchController::class, "getDocsCount"])->name('.get_docs_count');
    });

    /// Quản lí thiết bị nhận OTP
    Route::get('v1/device-get-otp-treatment-fee-list', [DeviceGetOtpController::class, "getDeviceGetOtpTreatmentFeeList"]);
    /// Mở khóa thiết bị đang bị chặn nhận mã OTP xem viện phí
    Route::get('v1/unlock-device-get-otp-treatment-fee-list', [DeviceGetOtpController::class, "unlockDeviceLimitTotalRequestSendOtp"]);

    /// Xml 130
    Route::get("v1/insert-data-from-xml-130-to-db", [XmlController::class, "insertDataFromXml130ToDb"])->name('.insert_data_from_xml130_to_db');

    /// Bộ phận thương tích
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentBodyPart->'], function () {
        Route::apiResource('v1/accident-body-part', AccidentBodyPartController::class);
    });
    /// Xử lý sau tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentCare->'], function () {
        Route::apiResource('v1/accident-care', AccidentCareController::class);
    });
    /// Nguyên nhân tai nạn 
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentHurtType->'], function () {
        Route::apiResource('v1/accident-hurt-type', AccidentHurtTypeController::class);
    });
    /// Địa điểm tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentLocation->'], function () {
        Route::apiResource('v1/accident-location', AccidentLocationController::class);
    });
    /// Loại tuổi
    Route::apiResource('v1/age-type', AgeTypeController::class);
    /// Khu vực
    Route::group(['as' => 'HIS.Desktop.Plugins.HisArea->'], function () {
        Route::apiResource('v1/area', AreaController::class);
        Route::get('v1/area-check', [CheckAreaController::class, "checkCode"])->name('.area_check_code');
    });
    /// ATC
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAtc->'], function () {
        Route::apiResource('v1/atc', AtcController::class);
    });
    /// Nhóm ATC
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAtcGroup->'], function () {
        Route::apiResource('v1/atc-group', AtcGroupController::class);
    });
    /// Bộ vật tư
    Route::apiResource('v1/equipment-set', EquipmentSetController::class);
    /// Ý thức
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAwareness->'], function () {
        Route::apiResource('v1/awareness', AwarenessController::class);
    });
    /// Giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBed->'], function () {
        Route::apiResource('v1/bed', BedController::class);
        Route::get('v1/bed-check', [CheckBedController::class, "checkCode"])->name('.bed_check_code');
    });
    /// Giường - Dịch vụ giường
    Route::group(['as' => 'HIS.Desktop.Plugins.BedBsty->'], function () {
        Route::apiResource('v1/bed-bsty', BedBstyController::class)->only(['index', 'show', 'store']);
    });
    /// Buồng bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedRoomList->'], function () {
        Route::apiResource('v1/bed-room', BedRoomController::class);
        Route::get('v1/bed-room-check', [CheckBedRoomController::class, "checkCode"])->name('.bed_room_check_code');
    });
    /// Loại giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedTypeList->'], function () {
        Route::apiResource('v1/bed-type', BedTypeController::class);
    });
    /// Thẻ BHYT không hợp lệ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBhytBlacklist->'], function () {
        Route::apiResource('v1/bhyt-blacklist', BhytBlacklistController::class);
    });
    /// Tham số BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBHYTParam->'], function () {
        Route::apiResource('v1/bhyt-param', BhytParamController::class);
    });
    /// Đầu mã thẻ BHYT
    Route::group(['as' => 'BHYT HIS.Desktop.Plugins.HisBhytWhitelist->'], function () {
        Route::apiResource('v1/bhyt-whitelist', BhytWhitelistController::class);
    });
    /// Thầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBidList->'], function () {
        Route::apiResource('v1/bid', BidController::class);
    });
    /// Loại thầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBidType->'], function () {
        Route::apiResource('v1/bid-type', BidTypeController::class);
    });
    /// Nhóm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodGroup->'], function () {
        Route::apiResource('v1/blood-group', BloodGroupController::class);
    });
    /// Dung tích túi máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodVolume->'], function () {
        Route::apiResource('v1/blood-volume', BloodVolumeController::class);
    });
    /// Bộ phận cơ thể
    Route::apiResource('v1/body-part', BodyPartController::class);
    Route::get('v1/body-part-check', [CheckBodyPartController::class, "checkCode"])->name('.body_part_check_code');
    /// Ngôi thai
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBornPosition->'], function () {
        Route::apiResource('v1/born-position', BornPositionController::class);
    });
    /// Cơ sở/Xã phường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBranch->'], function () {
        Route::apiResource('v1/branch', BranchController::class);
        Route::get('v1/branch-check', [CheckBranchController::class, "checkCode"])->name('.branch_check_code');
    });
    /// Lý do hủy giao dịch
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCancelReason->'], function () {
        Route::apiResource('v1/cancel-reason', CancelReasonController::class);
    });
    /// Nghề nghiệp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCareer->'], function () {
        Route::apiResource('v1/career', CareerController::class);
    });
    /// Nghề nghiệp nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.CareerTitle->'], function () {
        Route::apiResource('v1/career-title', CareerTitleController::class);
    });
    /// Phòng thu ngân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCashierRoom->'], function () {
        Route::apiResource('v1/cashier-room', CashierRoomController::class);
        Route::get('v1/cashier-room-check', [CheckCashierRoomController::class, "checkCode"])->name('.cashier_room_check_code');
    });
    /// Xã
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaCommune->'], function () {
        Route::apiResource('v1/commune', CommuneController::class);
        Route::get('v1/commune-check', [CheckCommuneController::class, "checkCode"])->name('.commune_check_code');
    });
    /// Chống chỉ định
    Route::group(['as' => 'HIS.Desktop.Plugins.HisContraindication->'], function () {
        Route::apiResource('v1/contraindication', ContraindicationController::class);
    });
    /// Tủ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDataStore->'], function () {
        Route::apiResource('v1/data-store', DataStoreController::class);
        Route::get('v1/data-store-check', [CheckDataStoreController::class, "checkCode"])->name('.data_store_check_code');
    });
    /// Thời gian tử vong
    Route::apiResource('v1/death-within', DeathWithinController::class);

    /// Nguyên nhân tử vong
    Route::apiResource('v1/death-cause', DeathCauseController::class)->only(['index', 'show']);
    /// Kết quả điều trị
    Route::apiResource('v1/treatment-result', TreatmentResultController::class)->only(['index', 'show']);
    /// Lý do hội chẩn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDebateReason->'], function () {
        Route::apiResource('v1/debate-reason', DebateReasonController::class);
    });
    // Loại hội chẩn
    Route::apiResource('v1/debate-type', DebateTypeController::class);
    /// Khoa phòng
    // Route::group(['as' => 'HIS.Desktop.Plugins.HisDepartment->'], function () {
    Route::apiResource('v1/department', DepartmentController::class);
    Route::get('v1/department-check', [CheckDepartmentController::class, "checkCode"])->name('.department_check_code');
    // });
    /// Loại chẩn đoán hình ảnh
    Route::apiResource('v1/diim-type', DiimTypeController::class);
    /// Huyện
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaDistrict->'], function () {
        Route::apiResource('v1/district', DistrictController::class);
        Route::get('v1/district-check', [CheckDistrictController::class, "checkCode"])->name('.district_check_code');
    });
    /// Dạng bào chế
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDosageForm->'], function () {
        Route::apiResource('v1/dosage-form', DosageFormController::class);
    });
    /// Phương pháp vô cảm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisEmotionlessMethod->'], function () {});
    /// Tài khoản nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.EmpUser->'], function () {
        Route::apiResource('v1/emp-user', EmployeeController::class);
    });
    /// Dân tộc
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaEthnic->'], function () {
        Route::apiResource('v1/ethnic', EthnicController::class);
    });
    /// Nhóm thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteGroup->'], function () {
        Route::apiResource('v1/execute-group', ExecuteGroupController::class);
        Route::get('v1/execute-group-check', [CheckExecuteGroupController::class, "checkCode"])->name('.execute_group_check_code');
    });
    /// Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRole->'], function () {
        Route::apiResource('v1/execute-role', ExecuteRoleController::class);
        Route::get('v1/execute-role-check', [CheckExecuteRoleController::class, "checkCode"])->name('.execute_role_check_code');
    });
    /// Tài khoản - Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.ExecuteRoleUser->'], function () {
        Route::apiResource('v1/execute-role-user', ExecuteRoleUserController::class)->only(['index', 'show', 'store']);
    });
    /// Phòng khám/cls/pttt
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRoom->'], function () {
        Route::apiResource('v1/execute-room', ExecuteRoomController::class);
        Route::get('v1/execute-room-check', [CheckExecuteRoomController::class, "checkCode"])->name('.execute_room_check_code');
    });
    /// Module xử lý dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExeServiceModule->'], function () {
        Route::apiResource('v1/exe-service-module', ExeServiceModuleController::class);
    });
    /// Lý do xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExpMestReason->'], function () {
        Route::apiResource('v1/exp-mest-reason', ExpMestReasonController::class);
    });
    /// Phòng chỉ định - Phòng thực hiện 
    Route::group(['as' => 'HIS.Desktop.Plugins.ExroRoom->'], function () {
        Route::apiResource('v1/exro-room', ExroRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Loại giấy tờ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisFileType->'], function () {
        Route::apiResource('v1/file-type', FileTypeController::class);
    });
    /// Cỡ phim
    Route::group(['as' => 'HIS.Desktop.Plugins.HisFilmSize->'], function () {
        Route::apiResource('v1/film-size', FilmSizeController::class);
    });
    /// Loại thăm dò chức năng
    Route::apiResource('v1/fuex-type', FuexTypeController::class);
    /// Giới tính
    Route::apiResource('v1/gender', GenderController::class);
    /// Đơn vị
    Route::apiResource('v1/group', GroupController::class);
    /// Loại đơn vị
    Route::apiResource('v1/group-type', GroupTypeController::class);
    /// Nhóm dịch vụ BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisHeinServiceType->'], function () {
        Route::apiResource('v1/hein-service-type', HeinServiceTypeController::class)->only(['index', 'show']);
    });
    /// Lý do nhập viện
    Route::group(['as' => 'HIS.Desktop.Plugins.HospitalizeReason->'], function () {
        Route::apiResource('v1/hospitalize-reason', HospitalizeReasonController::class);
    });
    /// Cách dùng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisHtu->'], function () {
        Route::apiResource('v1/htu', HtuController::class);
    });
    /// Icd - Cm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisIcdCm->'], function () {
        Route::apiResource('v1/icd-cm', IcdCmController::class);
        Route::get('v1/icd-cm-check', [CheckIcdCmController::class, "checkCode"])->name('.icd_cm_check_code');
    });
    /// ICD - Accepted Icd - Chẩn đoán
    // Route::group(['as' => 'HIS.Desktop.Plugins.HisIcd->'], function () {
    Route::apiResource('v1/icd', IcdController::class);
    // });
    /// Nhóm ICD
    Route::apiResource('v1/icd-group', IcdGroupController::class);
    /// Nguồn nhập
    Route::group(['as' => 'HIS.Desktop.Plugins.HisImpSource->'], function () {
        Route::apiResource('v1/imp-source', ImpSourceController::class);
    });
    /// Thông tin tài khoản
    Route::group(['as' => 'HIS.Desktop.Plugins.InfoUser->'], function () {
        Route::get("v1/info-user", [EmployeeController::class, "infoUser"])->name('.get_info_user');
        Route::put("v1/info-user", [EmployeeController::class, "updateInfoUser"])->name('.update_info_user');
    });
    /// Lý do kê đơn tương tác
    Route::group(['as' => 'HIS.Desktop.Plugins.InteractionReason->'], function () {
        Route::apiResource('v1/interaction-reason', InteractionReasonController::class);
    });
    /// Hạng lái xe
    Route::group(['as' => 'HIS.Desktop.Plugins.LicenseClass->'], function () {
        Route::apiResource('v1/license-class', LicenseClassController::class);
    });
    /// Vị trí hồ sơ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.LocationTreatment->'], function () {
        Route::apiResource('v1/location-treatment', LocationStoreController::class);
    });
    /// Máy / Máy cận lâm sàn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMachine->'], function () {
        Route::apiResource('v1/machine', MachineController::class);
    });
    /// Hãng sản xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisManufacturer->'], function () {
        Route::apiResource('v1/manufacturer', ManufacturerController::class);
    });
    /// Loại vật tư
    Route::apiResource('v1/material-type', MaterialTypeController::class);
    /// Vật tư tương đương
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMaterialTypeMap->'], function () {
        Route::apiResource('v1/material-type-map', MaterialTypeMapController::class);
    });
    /// Hợp đồng dược
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicalContractList->'], function () {
        Route::apiResource('v1/medical-contract', MedicalContractController::class);
    });
    /// Thuốc
    Route::apiResource('v1/medicine', MedicineController::class);
    /// Nhóm thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineGroup->'], function () {
        Route::apiResource('v1/medicine-group', MedicineGroupController::class);
    });
    /// Dòng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineLine->'], function () {
        Route::apiResource('v1/medicine-line', MedicineLineController::class);
    });
    /// Chính sách giá thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicinePaty->'], function () {
        Route::apiResource('v1/medicine-paty', MedicinePatyController::class);
    });
    /// Loại thuốc - Hoạt chất
    Route::group(['as' => 'HIS.Desktop.Plugins.MedicineTypeActiveIngredient->'], function () {
        Route::apiResource('v1/medicine-type-acin', MedicineTypeAcinController::class)->only(['index', 'show', 'store']);
    });
    /// Loại thuốc
    Route::apiResource('v1/medicine-type', MedicineTypeController::class);
    /// Đường dùng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineUseForm->'], function () {
        Route::apiResource('v1/medicine-use-form', MedicineUseFormController::class);
    });
    /// Cơ sở khám chữa bệnh ban đầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediOrg->'], function () {
        Route::apiResource('v1/medi-org', MediOrgController::class);
        Route::get('v1/medi-org-check', [CheckMediOrgController::class, "checkCode"])->name('.medi_org_check_code');
    });
    /// Loại bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediRecordType->'], function () {
        Route::apiResource('v1/medi-record-type', MediRecordTypeController::class);
    });
    /// Kho
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediStock->'], function () {
        Route::apiResource('v1/medi-stock', MediStockController::class);
        Route::get('v1/medi-stock-check', [CheckMediStockController::class, "checkCode"])->name('.medi_stock_check_code');
    });
    /// Kho - Loại vật tư
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMatyList->'], function () {
        Route::apiResource('v1/medi-stock-maty-list', MediStockMatyController::class)->only(['index', 'show', 'store']);
    });
    /// Kho - Loại thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMetyList->'], function () {
        Route::apiResource('v1/medi-stock-mety-list', MediStockMetyController::class)->only(['index', 'show', 'store']);
    });
    /// Mema Group
    Route::apiResource('v1/mema-group', MemaGroupController::class);
    /// Kho - Đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestPatientType->'], function () {
        Route::apiResource('v1/mest-patient-type', MestPatientTypeController::class)->only(['index', 'show', 'store']);
    });
    /// Kho - Phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestExportRoom->'], function () {
        Route::apiResource('v1/mest-export-room', MestRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Quân hàm
    Route::apiResource('v1/military-rank', MilitaryRankController::class);
    /// Chức năng
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModule->'], function () {
        Route::apiResource('v1/module', ModuleController::class);
    });
    /// Vai trò - Chức năng 
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModuleRole->'], function () {
        Route::apiResource('v1/module-role', ModuleRoleController::class)->only(['index', 'show', 'store']);
    });
    /// Quốc gia
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaNational->'], function () {
        Route::apiResource('v1/national', NationalController::class);
        Route::get('v1/national-check', [CheckNationalController::class, "checkCode"])->name('.national_check_code');
    });
    /// Nguồn chi trả khác
    Route::group(['as' => 'HIS.Desktop.Plugins.HisOtherPaySource->'], function () {
        Route::apiResource('v1/other-pay-source', OtherPaySourceController::class);
        Route::get('v1/other-pay-source-check', [CheckOtherPaySourceController::class, "checkCode"])->name('.other_pay_source_check_code');
    });
    /// Gói
    Route::apiResource('v1/package', PackageController::class);
    /// Quy cách đóng gói 
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPackingType->'], function () {
        Route::apiResource('v1/packing-type', PackingTypeController::class);
    });
    /// Trường hợp bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientCase->'], function () {
        Route::apiResource('v1/patient-case', PatientCaseController::class);
    });
    /// Phân loại bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientClassify->'], function () {
        Route::apiResource('v1/patient-classify', PatientClassifyController::class);
        Route::get('v1/patient-classify-check', [CheckPatientClassifyController::class, "checkCode"])->name('.patient_classify_check_code');
    });
    /// Chuyển đổi đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeAllow->'], function () {
        Route::apiResource('v1/patient-type-allow', PatientTypeAllowController::class);
    });
    /// Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientType->'], function () {
        Route::apiResource('v1/patient-type', PatientTypeController::class);
    });
    /// Phòng thực hiện - Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeRoom->'], function () {
        Route::apiResource('v1/patient-type-room', PatientTypeRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Hình thức thanh toán
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPayForm->'], function () {
        Route::apiResource('v1/pay-form', PayFormController::class);
    });
    /// Chức vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPosition->'], function () {
        Route::apiResource('v1/position', PositionController::class);
    });
    /// Chế phẩm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPreparationsBlood->'], function () {
        Route::apiResource('v1/preparations-blood', PreparationsBloodController::class);
    });
    /// Đối tượng ưu tiên
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPriorityType->'], function () {
        Route::apiResource('v1/priority-type', PriorityTypeController::class);
    });
    /// Phương pháp chế biến
    Route::group(['as' => 'HIS.Desktop.Plugins.HisProcessing->'], function () {
        Route::apiResource('v1/processing-method', ProcessingMethodController::class);
    });
    /// Tỉnh
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaProvince->'], function () {
        Route::apiResource('v1/province', ProvinceController::class);
        Route::get('v1/province-check', [CheckProvinceController::class, "checkCode"])->name('.province_check_code');
    });
    /// Tai biến PTTT
    Route::apiResource('v1/pttt-catastrophe', PtttCatastropheController::class);

    /// Tình trạng PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttCondition->'], function () {
        Route::apiResource('v1/pttt-condition', PtttConditionController::class);
    });
    /// Nhóm PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttGroup->'], function () {
        Route::apiResource('v1/pttt-group', PtttGroupController::class);
    });
    /// Phương pháp PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttMethod->'], function () {
        Route::apiResource('v1/pttt-method', PtttMethodController::class);
    });
    /// Bàn mổ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttTable->'], function () {
        Route::apiResource('v1/pttt-table', PtttTableController::class);
    });
    /// Nhóm xuất ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationGroup->'], function () {
        Route::apiResource('v1/ration-group', RationGroupController::class);
    });
    /// Bữa ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationTime->'], function () {
        Route::apiResource('v1/ration-time', RationTimeController::class);
    });
    /// Khu đón tiếp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisReceptionRoom->'], function () {
        Route::apiResource('v1/reception-room', ReceptionRoomController::class);
        Route::get('v1/reception-room-check', [CheckReceptionRoomController::class, "checkCode"])->name('.reception_room_check_code');
    });
    /// Nhà ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRefectory->'], function () {
        Route::apiResource('v1/refectory', RefectoryController::class);
        Route::get('v1/refectory-check', [CheckRefectoryController::class, "checkCode"])->name('.refectory_check_code');
    });
    /// Mối quan hệ
    Route::group(['as' => 'HIS.Desktop.Plugins.EmrRelationList->'], function () {
        Route::apiResource('v1/relation-list', RelationController::class);
    });
    /// Tôn giáo
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaReligion->'], function () {
        Route::apiResource('v1/religion', ReligionController::class);
    });
    /// Vai trò
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsRole->'], function () {
        Route::apiResource('v1/role', RoleController::class);
    });
    /// Phòng
    Route::apiResource('v1/room', RoomController::class)->only(['index', 'show']);
    /// Nhóm phòng
    Route::apiResource('v1/room-group', RoomGroupController::class)->only(['index', 'show', 'store']);
    /// Loại phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomTypeModule->'], function () {
        Route::apiResource('v1/room-type', RoomTypeController::class);
    });
    /// Thiết lập lợi nhuận xuất bán
    Route::group(['as' => 'HIS.Desktop.Plugins.EstablishSaleProfitCFG->'], function () {
        Route::apiResource('v1/sale-profit-cfg', SaleProfitCfgController::class);
    });
    /// Điều kiện dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceCondition->'], function () {
        Route::apiResource('v1/service-condition', ServiceConditionController::class);
    });
    /// Dịch vụ kỹ thuật
    Route::group(['as' => 'HIS.Desktop.Plugins.HisService->'], function () {
        Route::apiResource('v1/service', ServiceController::class);
        Route::get('v1/service-check', [CheckServiceController::class, "checkCode"])->name('.service_check_code');
    });
    /// Dịch vụ đi kèm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceFollow->'], function () {
        Route::apiResource('v1/service-follow', ServiceFollowController::class);
    });
    /// Nhóm dịch vụ
    Route::apiResource('v1/service-group', ServiceGroupController::class);
    /// Dịch vụ máy
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceMachine->'], function () {
        Route::apiResource('v1/service-machine', ServiceMachineController::class)->only(['index', 'show', 'store']);
    });
    /// Chính sách giá dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServicePatyList->'], function () {
        Route::apiResource('v1/service-paty', ServicePatyController::class);
    });
    /// Loại y lệnh 
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqType->'], function () {
        Route::apiResource('v1/service-req-type', ServiceReqTypeController::class);
    });
    /// Dịch vụ phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomService->'], function () {
        Route::apiResource('v1/service-room', ServiceRoomController::class)->only(['index', 'show', 'store']);
    });
    /// Loại dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceType->'], function () {
        Route::apiResource('v1/service-type', ServiceTypeController::class);
    });
    /// Đơn vị tính
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceUnitEdit->'], function () {
        Route::apiResource('v1/service-unit', ServiceUnitController::class);
    });
    /// Nhóm dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServSegr->'], function () {
        Route::apiResource('v1/serv-segr', ServSegrController::class)->only(['index', 'show']);
    });
    /// Chuyên khoa
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSpeciality->'], function () {
        Route::apiResource('v1/speciality', SpecialityController::class);
        Route::get('v1/speciality-check', [CheckSpecialityController::class, "checkCode"])->name('.speciality_check_code');
    });
    /// Điều kiện bảo quản
    Route::group(['as' => 'HIS.Desktop.Plugins.HisStorageCondition->'], function () {
        Route::apiResource('v1/storage-condition', StorageConditionController::class);
    });
    /// Chỉ số
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSuimIndex->'], function () {
        Route::apiResource('v1/suim-index', SuimIndexController::class);
    });
    /// Đơn vị tính chỉ số siêu âm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSuimIndexUnit->'], function () {
        Route::apiResource('v1/suim-index-unit', SuimIndexUnitController::class);
    });
    /// Nhà cung cấp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSupplier->'], function () {
        Route::apiResource('v1/supplier', SupplierController::class);
    });
    /// Chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndex->'], function () {
        Route::apiResource('v1/test-index', TestIndexController::class);
    });
    /// Nhóm chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndexGroup->'], function () {
        Route::apiResource('v1/test-index-group', TestIndexGroupController::class);
    });
    /// Đơn vị tính chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndexUnit->'], function () {
        Route::apiResource('v1/test-index-unit', TestIndexUnitController::class);
    });
    /// Loại mẫu bệnh phẩm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestSampleType->'], function () {
        Route::apiResource('v1/test-sample-type', TestSampleTypeController::class);
    });
    /// Loại xét nghiệm
    Route::apiResource('v1/test-type', TestTypeController::class);
    /// Lý do chuyển tuyến chuyên môn
    Route::group(['as' => 'HIS.Desktop.Plugins.TranPatiTech->'], function () {
        Route::apiResource('v1/tran-pati-tech', TranPatiTechController::class);
    });
    /// Loại giao dịch
    Route::apiResource('v1/transaction-type', TransactionTypeController::class)->only(['index', 'show'])->withoutMiddleware([
        'check_token',
        'check_admin:api',
        'check_module:api',
    ]);
    /// Loại ra viện
    Route::apiResource('v1/treatment-end-type', TreatmentEndTypeController::class);

    /// Diện điều trị
    Route::group(['as' => 'HIS.Desktop.Plugins.TreatmentType->'], function () {
        Route::apiResource('v1/treatment-type', TreatmentTypeController::class);
        Route::get('v1/treatment-type-check', [CheckTreatmentTypeController::class, "checkCode"])->name('.treatment_type_check_code');
    });
    /// Lý do mở trần
    Route::group(['as' => 'HIS.Desktop.Plugins.HisUnlimitReason->'], function () {
        Route::apiResource('v1/unlimit-reason', UnlimitReasonController::class);
    });
    /// Loại Vaccine
    Route::group(['as' => 'HIS.Desktop.Plugins.HisVaccineType->'], function () {
        Route::apiResource('v1/vaccine-type', VaccineTypeController::class);
    });
    /// Nơi làm việc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisWorkPlace->'], function () {
        Route::apiResource('v1/work-place', WorkPlaceController::class);
    });

    /// No cache

    /// Biên bản hội chẩn
    Route::group(['as' => 'HIS.Desktop.Plugins.Debate->'], function () {
        Route::apiResource('v1/debate', DebateController::class)->only(['index', 'show']);
        Route::apiResource('v1/debate-v-view', DebateVViewController::class)->only(['index', 'show']);
        /// Danh sách biên bản hội chẩn
        Route::apiResource('v1/debate-list-v-view', DebateListVViewController::class)->only(['index']);
        /// Biên bản hội chẩn
        Route::apiResource('v1/debate-detail-v-view', DebateDetailVViewController::class)->only(['show']);
    });
    /// Y lệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqList->'], function () {
        Route::apiResource('v1/service-req-l-view', ServiceReqLViewController::class)->only(['index', 'show']);
    });
    /// Nhân viên - Phòng
    Route::apiResource('v1/user-room-v-view', UserRoomVViewController::class)->only(['index', 'show']);
    /// Debate User
    Route::apiResource('v1/debate-user', DebateUserController::class)->only(['index', 'show']);
    /// Debate Ekip User
    Route::apiResource('v1/debate-ekip-user', DebateEkipUserController::class)->only(['index', 'show']);
    /// Hình thức chuyển viện
    Route::apiResource('v1/tran-pati-form', TranPatiFormController::class)->only(['index', 'show']);
    /// Tờ điều trị
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTrackingList->'], function () {
        Route::apiResource('v1/tracking', TrackingController::class)->only(['index', 'show']);
        Route::apiResource('v1/tracking-data', TrackingDataController::class)->only(['index']);
    });
    /// Danh sách y lệnh chỉ định
    Route::apiResource('v1/test-service-req-list-v-view', TestServiceReqListVViewController::class)->only(['index']);
    // Lấy theo id k cần token
    Route::get('v1/test-service-req-list-v-view/{id}', [TestServiceReqListVViewController::class, 'show'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);

    // Data không cần token
    Route::get('v1/test-service-req-list-v-view-no-login', [TestServiceReqListVViewController::class, 'viewNoLogin'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);

    Route::apiResource('v1/test-service-req-list-v-view-2', TestServiceReqListVView2Controller::class)->only(['index', 'show']);
    /// Chi tiết các dịch vụ của y lệnh
    Route::apiResource('v1/sere-serv', SereServController::class)->only(['index', 'show']);
    Route::apiResource('v1/sere-serv-v-view-4', SereServVView4Controller::class)->only(['index', 'show']);
    /// Đối tượng điều trị

    Route::group(['as' => 'HIS.Desktop.Plugins.CallPatientTypeAlter->'], function () {
        Route::apiResource('v1/patient-type-alter-v-view', PatientTypeAlterVViewController::class)->only(['index', 'show']);
    });
    /// Hồ sơ điều trị
    Route::group(['as' => 'HIS.Desktop.Plugins.TreatmentList->'], function () {
        Route::apiResource('v1/treatment-l-view', TreatmentLViewController::class)->only(['index', 'show']);
        Route::apiResource('v1/treatment-fee-view', TreatmentFeeViewController::class)->only(['index', 'show']);
        Route::apiResource('v1/treatment-with-patient-type-info-sdo', TreatmentWithPatientTypeInfoSdoController::class)->only(['index']);
    });
    /// Treatment Bed Room
    Route::group(['as' => 'HIS.Desktop.Plugins.TreatmentBedRoomList->'], function () {
        Route::apiResource('v1/treatment-bed-room-l-view', TreatmentBedRoomLViewController::class)->only(['index', 'show']);
    });
    /// Dấu hiệu sinh tồn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDhst->'], function () {
        Route::apiResource('v1/dhst', DhstController::class)->only(['index', 'show']);
    });
    /// Sere Serv Ext
    Route::apiResource('v1/sere-serv-ext', SereServExtController::class)->only(['index', 'show']);
    /// Kết quả xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.SereServTein->'], function () {
        Route::apiResource('v1/sere-serv-tein', SereServTeinController::class)->only(['index', 'show']);
        Route::apiResource('v1/sere-serv-tein-v-view', SereServTeinVViewController::class)->only(['index', 'show']);
        /// SereServ Tein Charts
        Route::apiResource('v1/sere-serv-tein-charts-v-view', SereServTeinChartsVViewController::class)->only(['index', 'show']);
        /// SereServTein List
        Route::apiResource('v1/sere-serv-tein-list-v-view', SereServTeinListVViewController::class)->only(['index', 'show']);
    });
    /// Sere Serv Bill
    Route::apiResource('v1/sere-serv-bill', SereServBillController::class)->only(['index', 'show']);
    /// Sere Serv Deposit
    Route::apiResource('v1/sere-serv-deposit-v-view', SereServDepositVViewController::class)->only(['index', 'show']);
    /// Sese Depo Repay
    Route::apiResource('v1/sese-depo-repay-v-view', SeseDepoRepayVViewController::class)->only(['index', 'show']);
    /// Account Book
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccountBookList->'], function () {
        Route::apiResource('v1/account-book-v-view', AccountBookVViewController::class)->only(['index', 'show']);
    });
    /// Chăm sóc
    Route::apiResource('v1/care', CareController::class)->only(['index', 'show']);
    /// Transaction List
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionList->'], function () {
        Route::apiResource('v1/transaction-list-v-view', TransactionListVViewController::class)->only(['index', 'show']);
    });
    /// Transaction Cancel
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionCancel->'], function () {
        Route::put('v1/transaction-cancel/{id}', [TransactionListVViewController::class, 'cancelTransaction']);
    });
    /// Transaction Update
    Route::apiResource('v1/transaction', TransactionController::class)->only(['update']);
    /// Transaction Restore
    Route::apiResource('v1/transaction-restore', TransactionRestoreController::class)->only(['update']);

    Route::apiResource('v1/transaction-list-v-view-no-login', TransactionListVViewController::class)->only(['index', 'show'])->withoutMiddleware([
        'check_token',
        'check_admin:api',
        'check_module:api',
    ]);
    /// Test Service Type List
    // k cần token
    Route::apiResource('v1/test-service-type-list-v-view', TestServiceTypeListVViewController::class)->only(['index'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);
    /// Treatment Fee Detail
    // k cần token
    Route::apiResource('v1/treatment-fee-detail-v-view', TreatmentFeeDetailVViewController::class)->only(['index'])
        // ->withoutMiddleware([
        //     'check_token',
        //     'check_admin:api',
        //     'check_module:api',
        // ])
    ;
    /// Danh sách thông tin bệnh nhân viện phí
    Route::apiResource('v1/treatment-fee-list-v-view', TreatmentFeeListVViewController::class)->only(['index']);
    // Lấy theo id k cần token
    Route::get('v1/treatment-fee-list-v-view/{id}', [TreatmentFeeListVViewController::class, 'show'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);
    // Data không cần token
    Route::get('v1/treatment-fee-list-v-view-no-login', [TreatmentFeeListVViewController::class, 'viewNoLogin'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);

    // Transaction
    // k cần token
    Route::apiResource('v1/treatment-fee-payment', TreatmentFeePayMentController::class)->only(['index'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);
    // Transaction cho yêu cầu tạm ứng 
    // k cần token
    Route::get('v1/treatment-fee-payment-deposit-req', [TreatmentFeePayMentController::class, 'createPaymentDepositReq'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);
    /// Tạo giao dịch tạm ứng Transaction Tạm ứng
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionDeposit->'], function () {
        Route::apiResource('v1/transaction-tam-ung', TransactionTamUngController::class)->only(['store']);
    });
    /// Tạo giao dịch hoàn ứng Transaction Hoàn ứng
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionRepay->'], function () {
        Route::apiResource('v1/transaction-hoan-ung', TransactionHoanUngController::class)->only(['store']);
    });
    /// Tạo giao dịch thanh toán Transaction Thanh Toán
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionBill->'], function () {
        Route::apiResource('v1/transaction-thanh-toan', TransactionThanhToanController::class)->only(['store']);
    });
    /// Tạo giao dịch tạm thu dịch vụ Transaction Tạm Thu Dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionDeposit->'], function () {
        Route::apiResource('v1/transaction-tam-thu-dich-vu', TransactionTamThuDichVuController::class)->only(['store']);
    });
    /// Tạo giao dịch hoàn ứng dịch vụ Transaction Hoàn Ứng Dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionRepay->'], function () {
        Route::apiResource('v1/transaction-hoan-ung-dich-vu', TransactionHoanUngDichVuController::class)->only(['store']);
    });

    /// Chi tiết giao dịch thanh toán
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionBillDetail->'], function () {
        Route::apiResource('v1/transaction-tt-detail-v-view', TransactionTTDetailVViewController::class)->only(['index']);
    });
    /// Chi tiết giao dịch tạm thu
    Route::group(['as' => 'HIS.Desktop.Plugins.TransactionDepositDetail->'], function () {
        Route::apiResource('v1/transaction-tu-detail-v-view', TransactionTUDetailVViewController::class)->only(['index']);
    });
    // Danh sách yêu cầu tạm ứng
    Route::apiResource('v1/deposit-req-list-v-view-no-login', DepositReqListVViewController::class)->only(['index', 'show'])
        ->withoutMiddleware([
            'check_token',
            'check_admin:api',
            'check_module:api',
        ]);
    Route::group(['as' => 'HIS.Desktop.Plugins.DepositRequest->'], function () {
        Route::apiResource('v1/deposit-req-list-v-view', DepositReqListVViewController::class);
    });

    /// Vỏ bệnh án
    Route::apiResource('v1/medical-case-cover-list-v-view', MedicalCaseCoverListVViewController::class)->only(['show']);
    /// TreatmentBedRoomList
    Route::apiResource('v1/treatment-bed-room-list-v-view', TreatmentBedRoomListVViewController::class)->only(['index']);
    /// TreatmentExecuteRoomList
    Route::apiResource('v1/treatment-exe-room-list-v-view', TreatmentExecuteRoomListVViewController::class)->only(['index']);
    /// Loại vỏ bệnh án
    Route::apiResource('v1/emr-cover-type', EmrCoverTypeController::class);
    /// Loại phiếu
    Route::apiResource('v1/emr-form', EmrFormController::class);
    /// Phòng
    Route::apiResource('v1/room-v-view', RoomVViewController::class)->only(['index', 'show']);
    /// Icd List
    Route::apiResource('v1/icd-list-v-view', IcdListVViewController::class)->only(['index', 'show']);
    /// Treatment Room Group
    Route::apiResource('v1/treatment-bed-room-group-v-view', TreatmentRoomGroupVViewController::class)->only(['index']);
    /// ServiceReqList danh sách y lệnh
    Route::apiResource('v1/service-req-list-v-view', ServiceReqListVViewController::class)->only(['index']);
    /// Tracking List
    Route::apiResource('v1/tracking-list-v-view', TrackingListVViewController::class)->only(['index', 'show']);
    /// SereServ List
    Route::apiResource('v1/sere-serv-list-v-view', SereServListVViewController::class)->only(['index']);
    /// SereServ Detail
    Route::apiResource('v1/sere-serv-detail-v-view', SereServDetailVViewController::class)->only(['show']);
    /// Loại văn bản
    Route::apiResource('v1/document-type', DocumentTypeController::class)->only(['index', 'show']);
    /// Danh sách văn bản
    Route::apiResource('v1/document-list-v-view', DocumentListVViewController::class)->only(['index', 'show']);
    /// SereServ Cls List
    Route::apiResource('v1/sere-serv-cls-list-v-view', SereServClsListVViewController::class)->only(['index']);
    /// SereServ Cls List
    Route::apiResource('v1/treatment-list-v-view', TreatmentListVViewController::class)->only(['index']);
    /// Result Cls
    Route::apiResource('v1/result-cls-v-view', ResultClsVViewController::class)->only(['index']);
    /// ServiceReqStt
    Route::apiResource('v1/service-req-stt', ServiceReqSttController::class)->only(['index', 'show']);
    /// Signer
    Route::apiResource('v1/signer', SignerController::class)->only(['index']);
    /// MedicineList
    Route::apiResource('v1/medicine-list-v-view', MedicineListVViewController::class)->only(['index']);
    /// MedicineList
    Route::apiResource('v1/speed-unit', SpeedUnitController::class)->only(['index']);
    /// Phieutdvacsbnc2Phieumau
    Route::apiResource('v1/phieutdvacsbnc2-phieumau', Phieutdvacsbnc2PhieumauController::class)->only(['index']);
    /// Repay Reason
    Route::apiResource('v1/repay-reason', RepayReasonController::class)->only(['index']);
    /// Fund
    Route::apiResource('v1/fund', FundController::class)->only(['index']);
    /// UserAccountBookVView
    Route::apiResource('v1/user-account-book-v-view', UserAccountBookVViewController::class)->only(['index']);
    /// Bảng kê
    Route::apiResource('v1/bang-ke-v-view', BangKeVViewController::class)->only(['index', 'update']);
    /// Bảng kê biểu mẫu
    Route::get('v1/bang-ke-bieu-mau', [BangKeVViewController::class, "handleBieuMau"]);
    /// Yêu cầu khám cls/pttt
    Route::apiResource('v1/yeu-cau-kham-cls-pttt-v-view', YeuCauKhamClsPtttVViewController::class)->only(['index', 'show']);
    Route::apiResource('v1/yeu-cau-kham-cls-pttt-data', YeuCauKhamClsPtttDataController::class)->only(['show']);
    /// Hợp đồng khám sức khỏe
    Route::apiResource('v1/ksk-contract', KskContractController::class)->only(['index']);
    /// Hướng điều trị tiếp theo
    Route::apiResource('v1/next-trea-intr', NextTreaIntrController::class)->only(['index', 'show']);
    /// Xếp loại khám sức khỏe
    Route::apiResource('v1/health-exam-rank', HealthExamRankController::class)->only(['index', 'show']);
    /// Thông tin bổ sung
    Route::apiResource('v1/treatment-end-type-ext', TreatmentEndTypeExtController::class)->only(['index']);
    /// Sổ chứng tử
    Route::apiResource('v1/death-cert-book', DeathCertBookController::class)->only(['index']);
    /// Lý do chuyển viện
    Route::apiResource('v1/tran-pati-reason', TranPatiReasonController::class)->only(['index']);
    /// Thuốc - Vật tư kê đơn
    Route::apiResource('v1/thuoc-vat-tu-bean', ThuocVatTuBeanVViewController::class)->only(['index']);
    /// Khung giờ hẹn khám
    Route::apiResource('v1/appointment-period', AppointmentPeriodController::class)->only(['index']);
    /// Cấu hình hệ thống
    Route::apiResource('v1/config', ConfigController::class)->only(['index']);
    /// Mẫu đơn 
    Route::apiResource('v1/exp-mest-template', ExpMestTemplateController::class)->only(['index', 'show']);
    /// Sổ chứng từ
    Route::apiResource('v1/document-book', DocumentBookController::class)->only(['index']);
    /// Đơn
    Route::apiResource('v1/don-v-view', DonVViewController::class)->only(['index', 'show']);
    /// Mẫu chuyển viện
    Route::apiResource('v1/tran-pati-temp', TranPatiTempController::class)->only(['index', 'show']);
});
