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
use App\Http\Controllers\Api\CacheControllers\EmpUserController;
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

Route::fallback(function () {
    return return_404_error_page_not_found();
});
Route::group([
    "middleware" => ["check_module:api"]
], function () {

    /// Khoa phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDepartment'], function () {
        Route::get("v1/department", [DepartmentController::class, "department"])->name('.get');
        Route::get("v1/department/{id}", [DepartmentController::class, "department"])->name('.get_id');
        Route::get("v1/department-check", [CheckDepartmentController::class, "check_code"])->name('.check');
        // Route::get("v1/department/deleted", [DepartmentController::class, "department"]);
        // Route::get("v1/department/{id}/restore", [DepartmentController::class, "department_restore"]);
        Route::post("v1/department", [DepartmentController::class, "department_create"])->name('.create');
        Route::put("v1/department/{id}", [DepartmentController::class, "department_update"])->name('.update');
        Route::delete("v1/department/{id}", [DepartmentController::class, "department_delete"])->name('.delete');
    });

    /// Đơn vị
    Route::get("v1/group", [GroupController::class, "group"])->name('.get_group');
    Route::get("v1/group/{id}", [GroupController::class, "group"])->name('.get_group_id');

    /// Loại phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomTypeModule'], function () {
    Route::get("v1/room-type", [RoomTypeController::class, "room_type"])->name('.get');
    Route::get("v1/room-type/{id}", [RoomTypeController::class, "room_type"])->name('.get_id');
});

    /// Nhóm phòng
    Route::get("v1/room-group", [RoomGroupController::class, "room_group"])->name('.get_room_group');
    Route::get("v1/room-group/{id}", [RoomGroupController::class, "room_group"])->name('.get_room_group_id');
    Route::post("v1/room-group", [RoomGroupController::class, "room_group_create"])->name('.create_room_group');

    /// Link màn hình chờ
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModule'], function () {
    Route::get("v1/screen-saver-module-link", [ScreenSaverModuleLinkController::class, "screen_saver_module_link"])->name('.get');
});

    /// Buồng bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedRoomList'], function () {
        Route::get("v1/bed-room", [BedRoomController::class, "bed_room"])->name('.get');
        Route::get("v1/bed-room/{id}", [BedRoomController::class, "bed_room"])->name('.api.bed_room.index_with_id')->name('.get_id');
        Route::get("v1/bed-room-check", [CheckBedRoomController::class, "check_code"])->name('.check');
        Route::post("v1/bed-room", [BedRoomController::class, "bed_room_create"])->name('.create');
        Route::put("v1/bed-room/{id}", [BedRoomController::class, "bed_room_update"])->name('.update');
        Route::delete("v1/bed-room/{id}", [BedRoomController::class, "bed_room_delete"])->name('.delete');
    });

    /// Loại xét nghiệm
    Route::get("v1/test-type", [TestTypeController::class, "test_type"])->name('.get_test_type');
    Route::get("v1/test-type/{id}", [TestTypeController::class, "test_type"])->name('.get_test_type_id');

    /// Phòng khám/cls/pttt
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRoom'], function () {
        Route::get("v1/execute-room", [ExecuteRoomController::class, "execute_room"])->name('.get');
        Route::get("v1/execute-room/{id}", [ExecuteRoomController::class, "execute_room"])->name('.get_id');
        Route::get("v1/execute-room-check", [CheckExecuteRoomController::class, "check_code"])->name('.check');
        Route::post("v1/execute-room", [ExecuteRoomController::class, "execute_room_create"])->name('.create');
        Route::put("v1/execute-room/{id}", [ExecuteRoomController::class, "execute_room_update"])->name('.update');
        Route::delete("v1/execute-room/{id}", [ExecuteRoomController::class, "execute_room_delete"])->name('.delete');
    });

    /// Chuyên khoa
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSpeciality'], function () {
        Route::get("v1/speciality", [SpecialityController::class, "speciality"])->name('.get');
        Route::get("v1/speciality/{id}", [SpecialityController::class, "speciality"])->name('.get_id');
        Route::get("v1/speciality-check", [CheckSpecialityController::class, "check_code"])->name('.check');
        Route::post("v1/speciality", [SpecialityController::class, "speciality_create"])->name('.create');
        Route::put("v1/speciality/{id}", [SpecialityController::class, "speciality_update"])->name('.update');
        Route::delete("v1/speciality/{id}", [SpecialityController::class, "speciality_delete"])->name('.delete');
    });

    /// Diện điều trị
    Route::group(['as' => 'HIS.Desktop.Plugins.TreatmentType'], function () {
        Route::get("v1/treatment-type", [TreatmentTypeController::class, "treatment_type"])->name('.get');
        Route::get("v1/treatment-type/{id}", [TreatmentTypeController::class, "treatment_type"])->name('.get_id');
        Route::get("v1/treatment-type-check", [CheckTreatmentTypeController::class, "check_code"])->name('.check');
        Route::post("v1/treatment-type", [TreatmentTypeController::class, "treatment_type_create"])->name('.create');
        Route::put("v1/treatment-type/{id}", [TreatmentTypeController::class, "treatment_type_update"])->name('.update');
        Route::delete("v1/treatment-type/{id}", [TreatmentTypeController::class, "treatment_type_delete"])->name('.delete');
    });

    /// Cơ sở khám chữa bệnh ban đầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediOrg'], function () {
        Route::get("v1/medi-org", [MediOrgController::class, "medi_org"])->name('.get');
        Route::get("v1/medi-org/{id}", [MediOrgController::class, "medi_org"])->name('.get_id');
        Route::get("v1/medi-org-check", [CheckMediOrgController::class, "check_code"])->name('.check');
        Route::post("v1/medi-org", [MediOrgController::class, "medi_org_create"])->name('.create');
        Route::put("v1/medi-org/{id}", [MediOrgController::class, "medi_org_update"])->name('.update');
        Route::delete("v1/medi-org/{id}", [MediOrgController::class, "medi_org_delete"])->name('.delete');
    });

    /// Cơ sở/Xã phường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBranch'], function () {
        Route::get("v1/branch", [BranchController::class, "branch"])->name('.get');
        Route::get("v1/branch/{id}", [BranchController::class, "branch"])->name('.get_id');
        Route::get("v1/branch-check", [CheckBranchController::class, "check_code"])->name('.check');
        Route::post("v1/branch", [BranchController::class, "branch_create"])->name('.create');
        Route::put("v1/branch/{id}", [BranchController::class, "branch_update"])->name('.update');
        Route::delete("v1/branch/{id}", [BranchController::class, "branch_delete"])->name('.delete');
    });

    /// Huyện
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaDistrict'], function () {
        Route::get("v1/district", [DistrictController::class, "district"])->name('.get');
        Route::get("v1/district/{id}", [DistrictController::class, "district"])->name('.get_id');
        Route::get("v1/district-check", [CheckDistrictController::class, "check_code"])->name('.check');
        Route::post("v1/district", [DistrictController::class, "district_create"])->name('.create');
        Route::put("v1/district/{id}", [DistrictController::class, "district_update"])->name('.update');
        Route::delete("v1/district/{id}", [DistrictController::class, "district_delete"])->name('.delete');
    });

    /// Nguồn chi trả khác
    Route::group(['as' => 'HIS.Desktop.Plugins.HisOtherPaySource'], function () {
        Route::get("v1/other-pay-source", [OtherPaySourceController::class, "other_pay_source"])->name('.get');
        Route::get("v1/other-pay-source/{id}", [OtherPaySourceController::class, "other_pay_source"])->name('.get_id');
        Route::get("v1/other-pay-source-check", [CheckOtherPaySourceController::class, "check_code"])->name('.check');
        Route::post("v1/other-pay-source", [OtherPaySourceController::class, "other_pay_source_create"])->name('.create');
        Route::put("v1/other-pay-source/{id}", [OtherPaySourceController::class, "other_pay_source_update"])->name('.update');
        Route::delete("v1/other-pay-source/{id}", [OtherPaySourceController::class, "other_pay_source_delete"])->name('.delete');
    });

    /// Quân hàm
    Route::get("v1/military-rank", [MilitaryRankController::class, "military_rank"])->name('.get_military_rank');
    Route::get("v1/military-rank/{id}", [MilitaryRankController::class, "military_rank"])->name('.get_military_rank_id');


    /// Kho
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediStock'], function () {
        Route::get("v1/medi-stock", [MediStockController::class, "medi_stock"])->name('.get');
        Route::get("v1/medi-stock/{id}", [MediStockController::class, "medi_stock"])->name('.get_id');
        Route::get("v1/medi-stock-check", [CheckMediStockController::class, "check_code"])->name('.check');
        Route::post("v1/medi-stock", [MediStockController::class, "medi_stock_create"])->name('.create');
        Route::put("v1/medi-stock/{id}", [MediStockController::class, "medi_stock_update"])->name('.update');
        Route::delete("v1/medi-stock/{id}", [MediStockController::class, "medi_stock_delete"])->name('.delete');
    });

    /// Khu đón tiếp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisReceptionRoom'], function () {
        Route::get("v1/reception-room", [ReceptionRoomController::class, "reception_room"])->name('.get');
        Route::get("v1/reception-room/{id}", [ReceptionRoomController::class, "reception_room"])->name('.get_id');
        Route::get("v1/reception-room-check", [CheckReceptionRoomController::class, "check_code"])->name('.check');
        Route::post("v1/reception-room", [ReceptionRoomController::class, "reception_room_create"])->name('.create');
        Route::put("v1/reception-room/{id}", [ReceptionRoomController::class, "reception_room_update"])->name('.update');
        Route::delete("v1/reception-room/{id}", [ReceptionRoomController::class, "reception_room_delete"])->name('.delete');
    });

    /// Khu vực
    Route::group(['as' => 'HIS.Desktop.Plugins.HisArea'], function () {
        Route::get("v1/area", [AreaController::class, "area"])->name('.get');
        Route::get("v1/area/{id}", [AreaController::class, "area"])->name('.get_id');
        Route::get("v1/area-check", [CheckAreaController::class, "check_code"])->name('.check');
        Route::post("v1/area", [AreaController::class, "area_create"])->name('.create');
        Route::put("v1/area/{id}", [AreaController::class, "area_update"])->name('.update');
        Route::delete("v1/area/{id}", [AreaController::class, "area_delete"])->name('.delete');
    });

    /// Nhà ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRefectory'], function () {
        Route::get("v1/refectory", [RefectoryController::class, "refectory"])->name('.get');
        Route::get("v1/refectory/{id}", [RefectoryController::class, "refectory"])->name('.get_id');
        Route::get("v1/refectory-check", [CheckRefectoryController::class, "check_code"])->name('.check');
        Route::post("v1/refectory", [RefectoryController::class, "refectory_create"])->name('.create');
        Route::put("v1/refectory/{id}", [RefectoryController::class, "refectory_update"])->name('.update');
        Route::delete("v1/refectory/{id}", [RefectoryController::class, "refectory_delete"])->name('.delete');
    });

    /// Nhóm thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteGroup'], function () {
        Route::get("v1/execute-group", [ExecuteGroupController::class, "execute_group"])->name('.get');
        Route::get("v1/execute-group/{id}", [ExecuteGroupController::class, "execute_group"])->name('.get_id');
        Route::get("v1/execute-group-check", [CheckExecuteGroupController::class, "check_code"])->name('.check');
        Route::post("v1/execute-group", [ExecuteGroupController::class, "execute_group_create"])->name('.create');
        Route::put("v1/execute-group/{id}", [ExecuteGroupController::class, "execute_group_update"])->name('.update');
        Route::delete("v1/execute-group/{id}", [ExecuteGroupController::class, "execute_group_delete"])->name('.delete');
    });

    /// Phòng thu ngân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCashierRoom'], function () {
        Route::get("v1/cashier-room", [CashierRoomController::class, "cashier_room"])->name('.get');
        Route::get("v1/cashier-room/{id}", [CashierRoomController::class, "cashier_room"])->name('.get_id');
        Route::get("v1/cashier-room-check", [CheckCashierRoomController::class, "check_code"])->name('.check');
        Route::post("v1/cashier-room", [CashierRoomController::class, "cashier_room_create"])->name('.create');
        Route::put("v1/cashier-room/{id}", [CashierRoomController::class, "cashier_room_update"])->name('.update');
        Route::delete("v1/cashier-room/{id}", [CashierRoomController::class, "cashier_room_delete"])->name('.delete');
    });

    /// Quốc gia
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaNational'], function () {
        Route::get("v1/national", [NationalController::class, "national"])->name('.get');
        Route::get("v1/national/{id}", [NationalController::class, "national"])->name('.get_id');
        Route::get("v1/national-check", [CheckNationalController::class, "check_code"])->name('.check');
        Route::post("v1/national", [NationalController::class, "national_create"])->name('.create');
        Route::put("v1/national/{id}", [NationalController::class, "national_update"])->name('.update');
        Route::delete("v1/national/{id}", [NationalController::class, "national_delete"])->name('.delete');
    });

    /// Tỉnh
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaProvince'], function () {
        Route::get("v1/province", [ProvinceController::class, "province"])->name('.get');
        Route::get("v1/province/{id}", [ProvinceController::class, "province"])->name('.get_id');
        Route::get("v1/province-check", [CheckProvinceController::class, "check_code"])->name('.check');
        Route::post("v1/province", [ProvinceController::class, "province_create"])->name('.create');
        Route::put("v1/province/{id}", [ProvinceController::class, "province_update"])->name('.update');
        Route::delete("v1/province/{id}", [ProvinceController::class, "province_delete"])->name('.delete');
    });

    /// Tủ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDataStore'], function () {
        Route::get("v1/data-store", [DataStoreController::class, "data_store"])->name('.get');
        Route::get("v1/data-store/{id}", [DataStoreController::class, "data_store"])->name('.get_id');
        Route::get("v1/data-store-check", [CheckDataStoreController::class, "check_code"])->name('.check');
        Route::post("v1/data-store", [DataStoreController::class, "data_store_create"])->name('.create');
        Route::put("v1/data-store/{id}", [DataStoreController::class, "data_store_update"])->name('.update');
        Route::delete("v1/data-store/{id}", [DataStoreController::class, "data_store_delete"])->name('.delete');
    });

    /// Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRole'], function () {
        Route::get("v1/execute-role", [ExecuteRoleController::class, "execute_role"])->name('.get');
        Route::get("v1/execute-role/{id}", [ExecuteRoleController::class, "execute_role"])->name('.get_id');
        Route::get("v1/execute-role-check", [CheckExecuteRoleController::class, "check_code"])->name('.check');
        Route::post("v1/execute-role", [ExecuteRoleController::class, "execute_role_create"])->name('.create');
        Route::put("v1/execute-role/{id}", [ExecuteRoleController::class, "execute_role_update"])->name('.update');
        Route::delete("v1/execute-role/{id}", [ExecuteRoleController::class, "execute_role_delete"])->name('.delete');
    });

    /// Xã
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaCommune'], function () {
        Route::get("v1/commune", [CommuneController::class, "commune"])->name('.get');
        Route::get("v1/commune/{id}", [CommuneController::class, "commune"])->name('.get_id');
        Route::get("v1/commune-check", [CheckCommuneController::class, "check_code"])->name('.check');
        Route::post("v1/commune", [CommuneController::class, "commune_create"])->name('.create');
        Route::put("v1/commune/{id}", [CommuneController::class, "commune_update"])->name('.update');
        Route::delete("v1/commune/{id}", [CommuneController::class, "commune_delete"])->name('.delete');
    });

    /// Icd - Cm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisIcdCm'], function () {
        Route::get("v1/icd-cm", [IcdCmController::class, "icd_cm"])->name('.get');
        Route::get("v1/icd-cm/{id}", [IcdCmController::class, "icd_cm"])->name('.get_id');
        Route::get("v1/icd-cm-check", [CheckIcdCmController::class, "check_code"])->name('.check');
        Route::post("v1/icd-cm", [IcdCmController::class, "icd_cm_create"])->name('.create');
        Route::put("v1/icd-cm/{id}", [IcdCmController::class, "icd_cm_update"])->name('.update');
        Route::delete("v1/icd-cm/{id}", [IcdCmController::class, "icd_cm_delete"])->name('.delete');
    });

    /// Nhóm ICD
    Route::get("v1/icd-group", [IcdGroupController::class, "icd_group"])->name('.get_icd_group');
    Route::get("v1/icd-group/{id}", [IcdGroupController::class, "icd_group"])->name('.get_icd_group_id');

    /// Loại tuổi
    Route::get("v1/age-type", [AgeTypeController::class, "age_type"])->name('.get_age_type');
    Route::get("v1/age-type/{id}", [AgeTypeController::class, "age_type"])->name('.get_age_type_id');

    /// Loại chẩn đoán hình ảnh
    Route::get("v1/diim-type", [DiimTypeController::class, "diim_type"])->name('.get_diim_type');
    Route::get("v1/diim-type/{id}", [DiimTypeController::class, "diim_type"])->name('.get_diim_type_id');

    /// Loại thăm dò chức năng
    Route::get("v1/fuex-type", [FuexTypeController::class, "fuex_type"])->name('.get_fuex_type');
    Route::get("v1/fuex-type/{id}", [FuexTypeController::class, "fuex_type"])->name('.get_fuex_type_id');

    /// Cỡ phim
    Route::group(['as' => 'HIS.Desktop.Plugins.HisFilmSize'], function () {
    Route::get("v1/film-size", [FilmSizeController::class, "film_size"])->name('.get');
    Route::get("v1/film-size/{id}", [FilmSizeController::class, "film_size"])->name('.get_id');
});

    /// Giới tính
    Route::get("v1/gender", [GenderController::class, "gender"])->name('.get_gender');
    Route::get("v1/gender/{id}", [GenderController::class, "gender"])->name('.get_gender_id');

    /// Bộ phận cơ thể
    Route::get("v1/body-part", [BodyPartController::class, "body_part"])->name('.get_body_part');
    Route::get("v1/body-part/{id}", [BodyPartController::class, "body_part"])->name('.get_body_part_id');
    Route::get("v1/body-part-check", [CheckBodyPartController::class, "check_code"])->name('.check_bodypart');
    Route::post("v1/body-part", [BodyPartController::class, "body_part_create"])->name('.create_body_part');
    Route::put("v1/body-part/{id}", [BodyPartController::class, "body_part_update"])->name('.update_body_part');
    Route::delete("v1/body-part/{id}", [BodyPartController::class, "body_part_delete"])->name('.delete_body_part');

    /// Module xử lý dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExeServiceModule'], function () {
    Route::get("v1/exe-service-module", [ExeServiceModuleController::class, "exe_service_module"])->name('.get');
    Route::get("v1/exe-service-module/{id}", [ExeServiceModuleController::class, "exe_service_module"])->name('.get_id');
});

    /// Chỉ số
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSuimIndex'], function () {
    Route::get("v1/suim-index", [SuimIndexController::class, "suim_index"])->name('.get');
    Route::get("v1/suim-index/{id}", [SuimIndexController::class, "suim_index"])->name('.get_id');
});

    /// Gói
    Route::get("v1/package", [PackageController::class, "package"])->name('.get_v');
    Route::get("v1/package/{id}", [PackageController::class, "package"])->name('.get_package_id');

    /// Dịch vụ kỹ thuật
    Route::group(['as' => 'HIS.Desktop.Plugins.HisService'], function () {
        Route::get("v1/service", [ServiceController::class, "service"])->name('.get');
        Route::get("v1/service/{id}", [ServiceController::class, "service"])->name('.get_id');
        // Route::get("v1/service/by-code/{type_id}", [ServiceController::class, "service_by_code"]);
        Route::get("v1/service/service-type/{id}", [ServiceController::class, "service_by_service_type"])->name('.get_service_type_id');
        Route::get("v1/service-check", [CheckServiceController::class, "check_code"])->name('.check');
        Route::post("v1/service", [ServiceController::class, "service_create"])->name('.create');
        Route::put("v1/service/{id}", [ServiceController::class, "service_update"])->name('.update');
        Route::delete("v1/service/{id}", [ServiceController::class, "service_delete"])->name('.delete');
    });

    /// Chính sách giá dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServicePatyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-paty", [ServicePatyController::class, "service_paty"])->name('.get');
        Route::get("v1/service-paty/{id}", [ServicePatyController::class, "service_paty"])->name('.get_id');
        Route::post("v1/service-paty", [ServicePatyController::class, "service_paty_create"])->name('.create');
        Route::put("v1/service-paty/{id}", [ServicePatyController::class, "service_paty_update"])->name('.update');
        Route::delete("v1/service-paty/{id}", [ServicePatyController::class, "service_paty_delete"])->name('.delete');
        // // Trả về tất cả dịch vụ cùng loại bệnh nhân
        // Route::get("v1/service/all/patient-type", [ServicePatyController::class, "service_with_patient_type"]);
        // Route::get("v1/service/{id}/patient-type", [ServicePatyController::class, "service_with_patient_type"]);
        // // Trả về tất cả loại bệnh nhân cùng dịch vụ
        // Route::get("v1/patient-type/all/service", [ServicePatyController::class, "patient_type_with_service"]);
        // Route::get("v1/patient-type/{id}/service", [ServicePatyController::class, "patient_type_with_service"]);

    });

    /// Điều kiện dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceCondition'], function () {
    Route::get("v1/service-condition", [ServiceConditionController::class, "service_condition"])->name('.get');
    Route::get("v1/service-condition/{id}", [ServiceConditionController::class, "service_condition"])->name('.get_id');
});

    /// Dịch vụ máy
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceMachine'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-machine", [ServiceMachineController::class, "service_machine"])->name('.get');
        Route::get("v1/service-machine/{id}", [ServiceMachineController::class, "service_machine"])->name('.get_id');
        // Trả về tất cả dịch vụ cùng máy
        Route::get("v1/service/all/machine", [ServiceMachineController::class, "service_with_machine"])->name('.get_service_machine');
        Route::get("v1/service/{id}/machine", [ServiceMachineController::class, "service_with_machine"])->name('.get_service_machine_id');
        // Trả về tất cả máy cùng dịch vụ
        Route::get("v1/machine/all/service", [ServiceMachineController::class, "machine_with_service"])->name('.get_machine_serice');
        Route::get("v1/machine/{id}/service", [ServiceMachineController::class, "machine_with_service"])->name('.get_machine_service_id');
    });

    /// Máy / Máy cận lâm sàn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMachine'], function () {
        Route::get("v1/machine", [MachineController::class, "machine"])->name('.get');
        Route::get("v1/machine/{id}", [MachineController::class, "machine"])->name('.get_id');
        Route::post("v1/machine", [MachineController::class, "machine_create"])->name('.create');
        Route::put("v1/machine/{id}", [MachineController::class, "machine_update"])->name('.update');
        Route::delete("v1/machine/{id}", [MachineController::class, "machine_delete"])->name('.delete');
    });

    /// Dịch vụ phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomService'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-room", [ServiceRoomController::class, "service_room"])->name('.get');
        Route::get("v1/service-room/{id}", [ServiceRoomController::class, "service_room"])->name('.get_id');
        // // Trả về tất cả dịch vụ cùng phòng
        // Route::get("v1/service/all/room", [HISController::class, "service_with_room"]);
        // Route::get("v1/service/{id}/room", [HISController::class, "service_with_room"]);
        // // Trả về tất cả phòng cùng dịch vụ
        // Route::get("v1/room/all/service", [HISController::class, "room_with_service"]);
        // Route::get("v1/room/{id}/service", [HISController::class, "room_with_service"]);
    });

    /// Phòng
    Route::get("v1/room", [RoomController::class, "room"])->name('.get_room');
    // Route::get("v1/room/department/{id}", [RoomController::class, "room_with_department"]);

    /// Dịch vụ đi kèm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceFollow'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-follow", [ServiceFollowController::class, "service_follow"])->name('.get');
        Route::get("v1/service-follow/{id}", [ServiceFollowController::class, "service_follow"])->name('.get_id');
        // // Trả về tất cả dịch vụ cùng dịch vụ đi kèm
        // Route::get("v1/service/all/follow", [HISController::class, "service_with_follow"]);
        // Route::get("v1/service/{id}/follow", [HISController::class, "service_with_follow"]);
        // // Trả về tất cả dịch vụ đi kèm cùng dịch vụ
        // Route::get("v1/follow/all/service", [HISController::class, "follow_with_service"]);
        // Route::get("v1/follow/{id}/service", [HISController::class, "follow_with_service"]);
    });

    /// Giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBed'], function () {
        Route::get("v1/bed", [BedController::class, "bed"])->name('.get');
        Route::get("v1/bed/{id}", [BedController::class, "bed"])->name('.get_id');
        Route::post("v1/bed", [BedController::class, "bed_create"])->name('.create');
        Route::put("v1/bed/{id}", [BedController::class, "bed_update"])->name('.update');
        Route::delete("v1/bed/{id}", [BedController::class, "bed_delete"])->name('.delete');
    });

    /// Giường - Dịch vụ giường
    Route::group(['as' => 'HIS.Desktop.Plugins.BedBsty'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/bed-bsty", [BedBstyController::class, "bed_bsty"])->name('.get');
        Route::get("v1/bed-bsty/{id}", [BedBstyController::class, "bed_bsty"])->name('.get_id');
        // // Trả về tất cả dịch vụ cùng giường
        // Route::get("v1/service/all/bed", [HISController::class, "service_with_bed"]);
        // Route::get("v1/service/{id}/bed", [HISController::class, "service_with_bed"]);
        // // Trả về tất cả giường cùng dịch vụ
        // Route::get("v1/bed/all/service", [HISController::class, "bed_with_service"]);
        // Route::get("v1/bed/{id}/service", [HISController::class, "bed_with_service"]);
    });

    /// Loại giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedTypeList'], function () {
        Route::get("v1/bed-type", [BedTypeController::class, "bed_type"])->name('.get');
        Route::get("v1/bed-type/{id}", [BedTypeController::class, "bed_type"])->name('.get_id');
    });

    /// Nhóm dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServSegr'], function () {
        Route::get("v1/serv-segr", [ServSegrController::class, "serv_segr"])->name('.get');
        Route::get("v1/serv-segr/{id}", [ServSegrController::class, "serv_segr"])->name('.get_id');
    });
    Route::get("v1/service-group", [ServiceGroupController::class, "service_group"])->name('.get_service_group');
    Route::get("v1/service-group/{id}", [ServiceGroupController::class, "service_group"])->name('.get_service_group_id');

    /// Tài khoản nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.EmpUser'], function () {
        Route::get("v1/emp-user", [EmpUserController::class, "emp_user"])->name('.get');
        Route::get("v1/emp-user/{id}", [EmpUserController::class, "emp_user"])->name('.get_id');
        Route::post("v1/emp-user", [EmpUserController::class, "emp_user_create"])->name('.create');
        Route::put("v1/emp-user/{id}", [EmpUserController::class, "emp_user_update"])->name('.update');
        Route::delete("v1/emp-user/{id}", [EmpUserController::class, "emp_user_delete"])->name('.delete');
    });

    /// Thông tin tài khoản
    Route::group(['as' => 'HIS.Desktop.Plugins.InfoUser'], function () {
        Route::get("v1/info-user", [InfoUserController::class, "info_user"])->name('.get');
        Route::put("v1/info-user", [InfoUserController::class, "info_user_update"])->name('.update');
    });

    /// Tài khoản - Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.ExecuteRoleUser'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/execute-role-user", [ExecuteRoleUserController::class, "execute_role_user"])->name('.get');
        Route::get("v1/execute-role-user/{id}", [ExecuteRoleUserController::class, "execute_role_user"])->name('.get_id');
        // // Trả về tất cả tài khoản cùng vai trò thực hiện
        // Route::get("v1/user/all/execute-role", [HISController::class, "user_with_execute_role"]);
        // Route::get("v1/user/{id}/execute-role", [HISController::class, "user_with_execute_role"]);
        // // Trả về tất cả vai trò thực hiện cùng tài khoản
        // Route::get("v1/execute-role/all/user", [HISController::class, "execute_role_with_user"]);
        // Route::get("v1/execute-role/{id}/user", [HISController::class, "execute_role_with_user"]);
    });

    /// Vai trò
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsRole'], function () {
        Route::get("v1/role", [RoleController::class, "role"])->name('.get');
        Route::get("v1/role/{id}", [RoleController::class, "role"])->name('.get_id');
    });

    /// Vai trò - Chức năng 
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModuleRole'], function () {
        Route::get("v1/module-role", [ModuleRoleController::class, "module_role"])->name('.get');
        Route::get("v1/module-role/{id}", [ModuleRoleController::class, "module_role"])->name('.get_id');
    });

    /// Dân tộc
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaEthnic'], function () {
        Route::get("v1/ethnic", [EthnicController::class, "ethnic"])->name('.get');
        Route::get("v1/ethnic/{id}", [EthnicController::class, "ethnic"])->name('.get_id');
        Route::post("v1/ethnic", [EthnicController::class, "ethnic_create"])->name('.create');
        Route::put("v1/ethnic/{id}", [EthnicController::class, "ethnic_update"])->name('.update');
        Route::delete("v1/ethnic/{id}", [EthnicController::class, "ethnic_delete"])->name('.delete');
    });

    /// Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientType'], function () {
        Route::get("v1/patient-type", [PatientTypeController::class, "patient_type"])->name('.get');
        // Route::get("v1/patient-type/is-addition", [PatientTypeController::class, "patient_type_is_addition"]);
        Route::get("v1/patient-type/{id}", [PatientTypeController::class, "patient_type"])->name('.get_id');
    });

    /// Đối tượng ưu tiên
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPriorityType'], function () {
        Route::get("v1/priority-type", [PriorityTypeController::class, "priority_type"])->name('.get');
        Route::get("v1/priority-type/{id}", [PriorityTypeController::class, "priority_type"])->name('.get_id');
    });

    /// Mối quan hệ
    Route::group(['as' => 'HIS.Desktop.Plugins.EmrRelationList'], function () {
        Route::get("v1/relation-list", [RelationController::class, "relation_list"])->name('.get');
        Route::get("v1/relation-list/{id}", [RelationController::class, "relation_list"])->name('.get_id');
    });

    /// Nghề nghiệp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCareer'], function () {
        Route::get("v1/career", [CareerController::class, "career"])->name('.get');
        Route::get("v1/career/{id}", [CareerController::class, "career"])->name('.get_id');
        Route::post("v1/career", [CareerController::class, "career_create"])->name('.create');
        Route::put("v1/career/{id}", [CareerController::class, "career_update"])->name('.update');
        Route::delete("v1/career/{id}", [CareerController::class, "career_delete"])->name('.delete');
    });

    /// Phân loại bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientClassify'], function () {
        Route::get("v1/patient-classify", [PatientClassifyController::class, "patient_classify"])->name('.get');
        Route::get("v1/patient-classify/{id}", [PatientClassifyController::class, "patient_classify"])->name('.get_id');
        Route::get("v1/patient-classify-check", [CheckPatientClassifyController::class, "check_code"])->name('.check');
        Route::post("v1/patient-classify", [PatientClassifyController::class, "patient_classify_create"])->name('.create');
        Route::put("v1/patient-classify/{id}", [PatientClassifyController::class, "patient_classify_update"])->name('.update');
        Route::delete("v1/patient-classify/{id}", [PatientClassifyController::class, "patient_classify_delete"])->name('.delete');
    });

    /// Tôn giáo
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaReligion'], function () {
        Route::get("v1/religion", [ReligionController::class, "religion"])->name('.get');
        Route::get("v1/religion/{id}", [ReligionController::class, "religion"])->name('.get_id');
    });

    /// Đơn vị tính
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceUnitEdit'], function () {
        Route::get("v1/service-unit", [ServiceUnitController::class, "service_unit"])->name('.get');
        Route::get("v1/service-unit/{id}", [ServiceUnitController::class, "service_unit"])->name('.get_id');
    });

    /// Loại dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceType'], function () {
        Route::get("v1/service-type", [ServiceTypeController::class, "service_type"])->name('.get');
        Route::get("v1/service-type/{id}", [ServiceTypeController::class, "service_type"])->name('.get_id');
    });

    /// Nhóm xuất ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationGroup'], function () {
        Route::get("v1/ration-group", [RationGroupController::class, "ration_group"])->name('.get');
        Route::get("v1/ration-group/{id}", [RationGroupController::class, "ration_group"])->name('.get_id');
    });

    /// Loại y lệnh 
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqType'], function () {
        Route::get("v1/service-req-type", [ServiceReqTypeController::class, "service_req_type"])->name('.get');
        Route::get("v1/service-req-type/{id}", [ServiceReqTypeController::class, "service_req_type"])->name('.get_id');
    });

    /// Bữa ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationTime'], function () {
        Route::get("v1/ration-time", [RationTimeController::class, "ration_time"])->name('.get');
        Route::get("v1/ration-time/{id}", [RationTimeController::class, "ration_time"])->name('.get_id');
    });

    /// Kho - Đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestPatientType'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/mest-patient-type", [MestPatientTypeController::class, "mest_patient_type"])->name('.get');
        Route::get("v1/mest-patient-type/{id}", [MestPatientTypeController::class, "mest_patient_type"])->name('.get_id');
        // // Trả về tất cả kho cùng đối tượng
        // Route::get("v1/medi-stock/all/patient-type", [HISController::class, "medi_stock_with_patient_type"]);
        // Route::get("v1/medi-stock/{id}/patient-type", [HISController::class, "medi_stock_with_patient_type"]);
        // // Trả về tất cả đối tượng cùng kho
        // Route::get("v1/patient-type/all/medi-stock", [HISController::class, "patient_type_with_medi_stock"]);
        // Route::get("v1/patient-type/{id}/medi-stock", [HISController::class, "patient_type_with_medi_stock"]);
    });

    /// Kho - Loại thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMetyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/medi-stock-mety-list", [MediStockMetyController::class, "medi_stock_mety_list"])->name('.get');
        Route::get("v1/medi-stock-mety-list/{id}", [MediStockMetyController::class, "medi_stock_mety_list"])->name('.get_id');
        // // Trả về tất cả kho cùng loại thuốc 
        // Route::get("v1/medi-stock/all/medicine-type", [HISController::class, "medi_stock_with_medicine_type"]);
        // Route::get("v1/medi-stock/{id}/medicine-type", [HISController::class, "medi_stock_with_medicine_type"]);
        // // Trả về tất cả loại thuốc cùng kho
        // Route::get("v1/medicine-type/all/medi-stock", [HISController::class, "medicine_type_with_medi_stock"]);
        // Route::get("v1/medicine-type/{id}/medi-stock", [HISController::class, "medicine_type_with_medi_stock"]);
    });

    /// Kho - Loại vật tư
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMatyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/medi-stock-maty-list", [MediStockMatyController::class, "medi_stock_maty_list"])->name('.get');
        Route::get("v1/medi-stock-maty-list/{id}", [MediStockMatyController::class, "medi_stock_maty_list"])->name('.get_id');
        // // Trả về tất cả kho cùng loại vật tư 
        // Route::get("v1/medi-stock/all/material-type", [HISController::class, "medi_stock_with_material_type"]);
        // Route::get("v1/medi-stock/{id}/material-type", [HISController::class, "medi_stock_with_material_type"]);
        // // Trả về tất cả loại vật tư cùng kho
        // Route::get("v1/material-type/all/medi-stock", [HISController::class, "material_type_with_medi_stock"]);
        // Route::get("v1/material-type/{id}/medi-stock", [HISController::class, "material_type_with_medi_stock"]);
    });

    /// Kho - Phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestExportRoom'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/mest-export-room", [MestRoomController::class, "mest_export_room"])->name('.get');
        Route::get("v1/mest-export-room/{id}", [MestRoomController::class, "mest_export_room"])->name('.get_id');
        // // Trả về tất cả kho cùng phòng
        // Route::get("v1/medi-stock/all/room", [HISController::class, "medi_stock_with_room"]);
        // Route::get("v1/medi-stock/{id}/room", [HISController::class, "medi_stock_with_room"]);
        // // Trả về tất cả phòng cùng kho
        // Route::get("v1/room/all/medi-stock", [HISController::class, "room_with_medi_stock"]);
        // Route::get("v1/room/{id}/medi-stock", [HISController::class, "room_with_medi_stock"]);
    });

    /// Phòng chỉ định - Phòng thực hiện 
    Route::group(['as' => 'HIS.Desktop.Plugins.ExroRoom'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/exro-room", [ExroRoomController::class, "exro_room"])->name('.get');
        Route::get("v1/exro-room/{id}", [ExroRoomController::class, "exro_room"])->name('.get_id');
        // // Trả về tất cả phòng thực hiện cùng phòng chỉ định
        // Route::get("v1/execute-room/all/room", [HISController::class, "execute_room_with_room"]);
        // Route::get("v1/execute-room/{id}/room", [HISController::class, "execute_room_with_room"]);
        // // Trả về tất cả phòng chỉ định cùng phòng thực hiện
        // Route::get("v1/room/all/execute-room", [HISController::class, "room_with_execute_room"]);
        // Route::get("v1/room/{id}/execute-room", [HISController::class, "room_with_execute_room"]);
    });

    /// Phòng thực hiện - Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeRoom'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/patient-type-room", [PatientTypeRoomController::class, "patient_type_room"])->name('.get');
        Route::get("v1/patient-type-room/{id}", [PatientTypeRoomController::class, "patient_type_room"])->name('.get_id');
        // // Trả về tất cả phòng thực hiện cùng đối tượng bệnh nhân
        // Route::get("v1/room/all/patient-type", [HISController::class, "room_with_patient_type"]);
        // Route::get("v1/room/{id}/patient-type", [HISController::class, "room_with_patient_type"]);
        // // Trả về tất cả đối tượng bệnh nhân cùng phòng thực hiện
        // Route::get("v1/patient-type/all/room", [HISController::class, "patient_type_with_room"]);
        // Route::get("v1/patient-type/{id}/room", [HISController::class, "patient_type_with_room"]);
    });

    /// Thiết lập lợi nhuận xuất bán
    Route::group(['as' => 'HIS.Desktop.Plugins.EstablishSaleProfitCFG'], function () {
        Route::get("v1/sale-profit-cfg", [SaleProfitCfgController::class, "sale_profit_cfg"])->name('.get');
        Route::get("v1/sale-profit-cfg/{id}", [SaleProfitCfgController::class, "sale_profit_cfg"])->name('.get_id');
    });

    /// Chuyển đổi đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeAllow'], function () {
        Route::get("v1/patient-type-allow", [PatientTypeAllowController::class, "patient_type_allow"])->name('.get');
        Route::get("v1/patient-type-allow/{id}", [PatientTypeAllowController::class, "patient_type_allow"])->name('.get_id');
    });

    /// Chức vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPosition'], function () {
        Route::get("v1/position", [PositionController::class, "position"])->name('.get');
        Route::get("v1/position/{id}", [PositionController::class, "position"])->name('.get_id');
    });

    /// Nơi làm việc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisWorkPlace'], function () {
        Route::get("v1/work-place", [WorkPlaceController::class, "work_place"])->name('.get');
        Route::get("v1/work-place/{id}", [WorkPlaceController::class, "work_place"])->name('.get_id');
    });

    /// Ngôi thai
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBornPosition'], function () {
        Route::get("v1/born-position", [BornPositionController::class, "born_position"])->name('.get');
        Route::get("v1/born-position/{id}", [BornPositionController::class, "born_position"])->name('.get_id');
        Route::post("v1/born-position", [BornPositionController::class, "born_position_create"])->name('.create');
        Route::put("v1/born-position/{id}", [BornPositionController::class, "born_position_update"])->name('.update');
        Route::delete("v1/born-position/{id}", [BornPositionController::class, "born_position_delete"])->name('.delete');
    });

    /// Trường hợp bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientCase'], function () {
        Route::get("v1/patient-case", [PatientCaseController::class, "patient_case"])->name('.get');
        Route::get("v1/patient-case/{id}", [PatientCaseController::class, "patient_case"])->name('.get_id');
    });

    /// Đầu mã thẻ BHYT
    Route::group(['as' => 'BHYT HIS.Desktop.Plugins.HisBhytWhitelist'], function () {
        Route::get("v1/bhyt-whitelist", [BhytWhitelistController::class, "bhyt_whitelist"])->name('.get');
        Route::get("v1/bhyt-whitelist/{id}", [BhytWhitelistController::class, "bhyt_whitelist"])->name('.get_id');
        Route::post("v1/bhyt-whitelist", [BhytWhitelistController::class, "bhyt_whitelist_create"])->name('.create');
        Route::put("v1/bhyt-whitelist/{id}", [BhytWhitelistController::class, "bhyt_whitelist_update"])->name('.update');
        Route::delete("v1/bhyt-whitelist/{id}", [BhytWhitelistController::class, "bhyt_whitelist_delete"])->name('.delete');
    });

    /// Nhóm dịch vụ BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisHeinServiceType'], function () {
        Route::get("v1/hein-service-type", [HeinServiceTypeController::class, "hein_service_type"])->name('.get');
        Route::get("v1/hein-service-type/{id}", [HeinServiceTypeController::class, "hein_service_type"])->name('.get_id');
    });

    /// Tham số BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBHYTParam'], function () {
        Route::get("v1/bhyt-param", [BhytParamController::class, "bhyt_param"])->name('.get');
        Route::get("v1/bhyt-param/{id}", [BhytParamController::class, "bhyt_param"])->name('.get_id');
        Route::post("v1/bhyt-param", [BhytParamController::class, "bhyt_param_create"])->name('.create');
        Route::put("v1/bhyt-param/{id}", [BhytParamController::class, "bhyt_param_update"])->name('.update');
        Route::delete("v1/bhyt-param/{id}", [BhytParamController::class, "bhyt_param_delete"])->name('.delete');
    });

    /// Thẻ BHYT không hợp lệ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBhytBlacklist'], function () {
        Route::get("v1/bhyt-blacklist", [BhytBlacklistController::class, "bhyt_blacklist"])->name('.get');
        Route::get("v1/bhyt-blacklist/{id}", [BhytBlacklistController::class, "bhyt_blacklist"])->name('.get_id');
        Route::post("v1/bhyt-blacklist", [BhytBlacklistController::class, "bhyt_blacklist_create"])->name('.create');
        Route::put("v1/bhyt-blacklist/{id}", [BhytBlacklistController::class, "bhyt_blacklist_update"])->name('.update');
        Route::delete("v1/bhyt-blacklist/{id}", [BhytBlacklistController::class, "bhyt_blacklist_delete"])->name('.delete');
    });

    /// Chính sách giá thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicinePaty'], function () {
        Route::get("v1/medicine-paty", [MedicinePatyController::class, "medicine_paty"])->name('.get');
        Route::get("v1/medicine-paty/{id}", [MedicinePatyController::class, "medicine_paty"])->name('.get_id');
    });

    /// Bộ phận thương tích
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentBodyPart'], function () {
        Route::get("v1/accident-body-part", [AccidentBodyPartController::class, "accident_body_part"])->name('.get');
        Route::get("v1/accident-body-part/{id}", [AccidentBodyPartController::class, "accident_body_part"])->name('.get_id');
        Route::post("v1/accident-body-part", [AccidentBodyPartController::class, "accident_body_part_create"])->name('.create');
        Route::put("v1/accident-body-part/{id}", [AccidentBodyPartController::class, "accident_body_part_update"])->name('.update');
        Route::delete("v1/accident-body-part/{id}", [AccidentBodyPartController::class, "accident_body_part_delete"])->name('.delete');
    });

    /// Chế phẩm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPreparationsBlood'], function () {
        Route::get("v1/preparations-blood", [PreparationsBloodController::class, "preparations_blood"])->name('.get');
        Route::get("v1/preparations-blood/{id}", [PreparationsBloodController::class, "preparations_blood"])->name('.get_id');
    });

    /// Chống chỉ định
    Route::group(['as' => 'HIS.Desktop.Plugins.HisContraindication'], function () {
        Route::get("v1/contraindication", [ContraindicationController::class, "contraindication"])->name('.get');
        Route::get("v1/contraindication/{id}", [ContraindicationController::class, "contraindication"])->name('.get_id');
        Route::post("v1/contraindication", [ContraindicationController::class, "contraindication_create"])->name('.create');
        Route::put("v1/contraindication/{id}", [ContraindicationController::class, "contraindication_update"])->name('.update');
        Route::delete("v1/contraindication/{id}", [ContraindicationController::class, "contraindication_delete"])->name('.delete');
    });

    /// Dạng bào chế
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDosageForm'], function () {
        Route::get("v1/dosage-form", [DosageFormController::class, "dosage_form"])->name('.get');
        Route::get("v1/dosage-form/{id}", [DosageFormController::class, "dosage_form"])->name('.get_id');
        Route::post("v1/dosage-form", [DosageFormController::class, "dosage_form_create"])->name('.create');
        Route::put("v1/dosage-form/{id}", [DosageFormController::class, "dosage_form_update"])->name('.update');
        Route::delete("v1/dosage-form/{id}", [DosageFormController::class, "dosage_form_delete"])->name('.delete');
    });

    /// Địa điểm tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentLocation'], function () {
        Route::get("v1/accident-location", [AccidentLocationController::class, "accident_location"])->name('.get');
        Route::get("v1/accident-location/{id}", [AccidentLocationController::class, "accident_location"])->name('.get_id');
        Route::post("v1/accident-location", [AccidentLocationController::class, "accident_location_create"])->name('.create');
        Route::put("v1/accident-location/{id}", [AccidentLocationController::class, "accident_location_update"])->name('.update');
        Route::delete("v1/accident-location/{id}", [AccidentLocationController::class, "accident_location_delete"])->name('.delete');
    });

    /// Hạng lái xe
    Route::group(['as' => 'HIS.Desktop.Plugins.LicenseClass'], function () {
        Route::get("v1/license-class", [LicenseClassController::class, "license_class"])->name('.get');
        Route::get("v1/license-class/{id}", [LicenseClassController::class, "license_class"])->name('.get_id');
        Route::post("v1/license-class", [LicenseClassController::class, "license_class_create"])->name('.create');
        Route::put("v1/license-class/{id}", [LicenseClassController::class, "license_class_update"])->name('.update');
        Route::delete("v1/license-class/{id}", [LicenseClassController::class, "license_class_delete"])->name('.delete');
    });

    /// Hãng sản xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisManufacturer'], function () {
        Route::get("v1/manufacturer", [ManufacturerController::class, "manufacturer"])->name('.get');
        Route::get("v1/manufacturer/{id}", [ManufacturerController::class, "manufacturer"])->name('.get_id');
    });

    /// ICD - Accepted Icd - Chẩn đoán
    Route::group(['as' => 'HIS.Desktop.Plugins.HisIcd'], function () {
        Route::get("v1/icd", [IcdController::class, "icd"])->name('.get');
        Route::get("v1/icd/{id}", [IcdController::class, "icd"])->name('.get_id');
        Route::post("v1/icd", [IcdController::class, "icd_create"])->name('.create');
        Route::put("v1/icd/{id}", [IcdController::class, "icd_update"])->name('.update');
        Route::delete("v1/icd/{id}", [IcdController::class, "icd_delete"])->name('.delete');
    });

    /// Loại bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediRecordType'], function () {
        Route::get("v1/medi-record-type", [MediRecordTypeController::class, "medi_record_type"])->name('.get');
        Route::get("v1/medi-record-type/{id}", [MediRecordTypeController::class, "medi_record_type"])->name('.get_id');
    });

    /// Loại giấy tờ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisFileType'], function () {
        Route::get("v1/file-type", [FileTypeController::class, "file_type"])->name('.get');
        Route::get("v1/file-type/{id}", [FileTypeController::class, "file_type"])->name('.get_id');
        Route::post("v1/file-type", [FileTypeController::class, "file_type_create"])->name('.create');
        Route::put("v1/file-type/{id}", [FileTypeController::class, "file_type_update"])->name('.update');
        Route::delete("v1/file-type/{id}", [FileTypeController::class, "file_type_delete"])->name('.delete');
    });

    /// Loại ra viện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTreatmentEndType'], function () {
        Route::get("v1/treatment-end-type", [TreatmentEndTypeController::class, "treatment_end_type"])->name('.get');
        Route::get("v1/treatment-end-type/{id}", [TreatmentEndTypeController::class, "treatment_end_type"])->name('.get_id');
    });

    /// Lý do chuyển tuyến chuyên môn
    Route::group(['as' => 'HIS.Desktop.Plugins.TranPatiTech'], function () {
        Route::get("v1/tran-pati-tech", [TranPatiTechController::class, "tran_pati_tech"])->name('.get');
        Route::get("v1/tran-pati-tech/{id}", [TranPatiTechController::class, "tran_pati_tech"])->name('.get_id');
    });

    /// Lý do hội chẩn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDebateReason'], function () {
        Route::get("v1/debate-reason", [DebateReasonController::class, "debate_reason"])->name('.get');
        Route::get("v1/debate-reason/{id}", [DebateReasonController::class, "debate_reason"])->name('.get_id');
        Route::post("v1/debate-reason", [DebateReasonController::class, "debate_reason_create"])->name('.create');
        Route::put("v1/debate-reason/{id}", [DebateReasonController::class, "debate_reason_update"])->name('.update');
        Route::delete("v1/debate-reason/{id}", [DebateReasonController::class, "debate_reason_delete"])->name('.delete');
    });

    /// Lý do hủy giao dịch
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCancelReason'], function () {
        Route::get("v1/cancel-reason", [CancelReasonController::class, "cancel_reason"])->name('.get');
        Route::get("v1/cancel-reason/{id}", [CancelReasonController::class, "cancel_reason"])->name('.get_id');
        Route::post("v1/cancel-reason", [CancelReasonController::class, "cancel_reason_create"])->name('.create');
        Route::put("v1/cancel-reason/{id}", [CancelReasonController::class, "cancel_reason_update"])->name('.update');
        Route::delete("v1/cancel-reason/{id}", [CancelReasonController::class, "cancel_reason_delete"])->name('.delete');
    });

    /// Lý do kê đơn tương tác
    Route::group(['as' => 'HIS.Desktop.Plugins.InteractionReason'], function () {
        Route::get("v1/interaction-reason", [InteractionReasonController::class, "interaction_reason"])->name('.get');
        Route::get("v1/interaction-reason/{id}", [InteractionReasonController::class, "interaction_reason"])->name('.get_id');
        Route::post("v1/interaction-reason", [InteractionReasonController::class, "interaction_reason_create"])->name('.create');
        Route::put("v1/interaction-reason/{id}", [InteractionReasonController::class, "interaction_reason_update"])->name('.update');
        Route::delete("v1/interaction-reason/{id}", [InteractionReasonController::class, "interaction_reason_delete"])->name('.delete');
    });

    /// Lý do mở trần
    Route::group(['as' => 'HIS.Desktop.Plugins.HisUnlimitReason'], function () {
        Route::get("v1/unlimit-reason", [UnlimitReasonController::class, "unlimit_reason"])->name('.get');
        Route::get("v1/unlimit-reason/{id}", [UnlimitReasonController::class, "unlimit_reason"])->name('.get_id');
    });

    /// Lý do nhập viện
    Route::group(['as' => 'HIS.Desktop.Plugins.HospitalizeReason'], function () {
        Route::get("v1/hospitalize-reason", [HospitalizeReasonController::class, "hospitalize_reason"])->name('.get');
        Route::get("v1/hospitalize-reason/{id}", [HospitalizeReasonController::class, "hospitalize_reason"])->name('.get_id');
        Route::post("v1/hospitalize-reason", [HospitalizeReasonController::class, "hospitalize_reason_create"])->name('.create');
        Route::put("v1/hospitalize-reason/{id}", [HospitalizeReasonController::class, "hospitalize_reason_update"])->name('.update');
        Route::delete("v1/hospitalize-reason/{id}", [HospitalizeReasonController::class, "hospitalize_reason_delete"])->name('.delete');
    });

    /// Lý do xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExpMestReason'], function () {
        Route::get("v1/exp-mest-reason", [ExpMestReasonController::class, "exp_mest_reason"])->name('.get');
        Route::get("v1/exp-mest-reason/{id}", [ExpMestReasonController::class, "exp_mest_reason"])->name('.get_id');
    });

    /// Nghề nghiệp nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.CareerTitle'], function () {
        Route::get("v1/career-title", [CareerTitleController::class, "career_title"])->name('.get');
        Route::get("v1/career-title/{id}", [CareerTitleController::class, "career_title"])->name('.get_id');
        Route::post("v1/career-title", [CareerTitleController::class, "career_title_create"])->name('.create');
        Route::put("v1/career-title/{id}", [CareerTitleController::class, "career_title_update"])->name('.update');
        Route::delete("v1/career-title/{id}", [CareerTitleController::class, "career_title_delete"])->name('.delete');
    });

    /// Nguyên nhân tai nạn 
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentHurtType'], function () {
        Route::get("v1/accident-hurt-type", [AccidentHurtTypeController::class, "accident_hurt_type"])->name('.get');
        Route::get("v1/accident-hurt-type/{id}", [AccidentHurtTypeController::class, "accident_hurt_type"])->name('.get_id');
        Route::post("v1/accident-hurt-type", [AccidentHurtTypeController::class, "accident_hurt_type_create"])->name('.create');
        Route::put("v1/accident-hurt-type/{id}", [AccidentHurtTypeController::class, "accident_hurt_type_update"])->name('.update');
        Route::delete("v1/accident-hurt-type/{id}", [AccidentHurtTypeController::class, "accident_hurt_type_delete"])->name('.delete');
    });

    /// Nhà cung cấp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSupplier'], function () {
        Route::get("v1/supplier", [SupplierController::class, "supplier"])->name('.get');
        Route::get("v1/supplier/{id}", [SupplierController::class, "supplier"])->name('.get_id');
    });

    /// Phương pháp ché biến
    Route::group(['as' => 'HIS.Desktop.Plugins.HisProcessing'], function () {
        Route::get("v1/processing-method", [ProcessingMethodController::class, "processing_method"])->name('.get');
        Route::get("v1/processing-method/{id}", [ProcessingMethodController::class, "processing_method"])->name('.get_id');
    });

    /// Thời gian tử vong
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDeathWithin'], function () {
        Route::get("v1/death-within", [DeathWithinController::class, "death_within"])->name('.get');
        Route::get("v1/death-within/{id}", [DeathWithinController::class, "death_within"])->name('.get_id');
        Route::post("v1/death-within", [DeathWithinController::class, "death_within_create"])->name('.create');
        Route::put("v1/death-within/{id}", [DeathWithinController::class, "death_within_update"])->name('.update');
        Route::delete("v1/death-within/{id}", [DeathWithinController::class, "death_within_delete"])->name('.delete');
    });

    /// Vị trí hồ sơ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.LocationTreatment'], function () {
        Route::get("v1/location-treatment", [LocationStoreController::class, "location_treatment"])->name('.get');
        Route::get("v1/location-treatment/{id}", [LocationStoreController::class, "location_treatment"])->name('.get_id');
        Route::post("v1/location-treatment", [LocationStoreController::class, "location_treatment_create"])->name('.create');
        Route::put("v1/location-treatment/{id}", [LocationStoreController::class, "location_treatment_update"])->name('.update');
        Route::delete("v1/location-treatment/{id}", [LocationStoreController::class, "location_treatment_delete"])->name('.delete');
    });

    /// Xử lý sau tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentCare'], function () {
        Route::get("v1/accident-care", [AccidentCareController::class, "accident_care"])->name('.get');
        Route::get("v1/accident-care/{id}", [AccidentCareController::class, "accident_care"])->name('.get_id');
        Route::post("v1/accident-care", [AccidentCareController::class, "accident_care_create"])->name('.create');
        Route::put("v1/accident-care/{id}", [AccidentCareController::class, "accident_care_update"])->name('.update');
        Route::delete("v1/accident-care/{id}", [AccidentCareController::class, "accident_care_delete"])->name('.delete');
    });

    /// Bàn mổ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttTable'], function () {
        Route::get("v1/pttt-table", [PtttTableController::class, "pttt_table"])->name('.get');
        Route::get("v1/pttt-table/{id}", [PtttTableController::class, "pttt_table"])->name('.get_id');
    });

    /// Nhóm PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttGroup'], function () {
        //Trả về tất cả nhóm pttt cùng nhóm dịch vụ 
        Route::get("v1/pttt-group", [PtttGroupController::class, "pttt_group"])->name('.get');
        Route::get("v1/pttt-group/{id}", [PtttGroupController::class, "pttt_group"])->name('.get_id');
    });

    /// Phương pháp PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttMethod'], function () {
        //Trả về tất cả nhóm pttt cùng nhóm dịch vụ 
        Route::get("v1/pttt-method", [PtttMethodController::class, "pttt_method"])->name('.get');
        Route::get("v1/pttt-method/{id}", [PtttMethodController::class, "pttt_method"])->name('.get_id');
    });

    /// Phương pháp vô cảm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisEmotionlessMethod'], function () {
        Route::get("v1/emotionless-method", [EmotionlessMethodController::class, "emotionless_method"])->name('.get');
        Route::get("v1/emotionless-method/{id}", [EmotionlessMethodController::class, "emotionless_method"])->name('.get_id');
        Route::post("v1/emotionless-method", [EmotionlessMethodController::class, "emotionless_method_create"])->name('.create');
        Route::put("v1/emotionless-method/{id}", [EmotionlessMethodController::class, "emotionless_method_update"])->name('.update');
        Route::delete("v1/emotionless-method/{id}", [EmotionlessMethodController::class, "emotionless_method_delete"])->name('.delete');
    });

    /// Tai biến PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttCatastrophe'], function () {
        Route::get("v1/pttt-catastrophe", [PtttCatastropheController::class, "pttt_catastrophe"])->name('.get');
        Route::get("v1/pttt-catastrophe/{id}", [PtttCatastropheController::class, "pttt_catastrophe"])->name('.get_id');
    });

    /// Tình trạng PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttCondition'], function () {
        Route::get("v1/pttt-condition", [PtttConditionController::class, "pttt_condition"])->name('.get');
        Route::get("v1/pttt-condition/{id}", [PtttConditionController::class, "pttt_condition"])->name('.get_id');
    });

    /// Ý thức
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAwareness'], function () {
        Route::get("v1/awareness", [AwarenessController::class, "awareness"])->name('.get');
        Route::get("v1/awareness/{id}", [AwarenessController::class, "awareness"])->name('.get_id');
        Route::post("v1/awareness", [AwarenessController::class, "awareness_create"])->name('.create');
        Route::put("v1/awareness/{id}", [AwarenessController::class, "awareness_update"])->name('.update');
        Route::delete("v1/awareness/{id}", [AwarenessController::class, "awareness_delete"])->name('.delete');
    });

    /// Dòng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineLine'], function () {
        Route::get("v1/medicine-line", [MedicineLineController::class, "medicine_line"])->name('.get');
        Route::get("v1/medicine-line/{id}", [MedicineLineController::class, "medicine_line"])->name('.get_id');
    });

    /// Dung tích túi máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodVolume'], function () {
        Route::get("v1/blood-volume", [BloodVolumeController::class, "blood_volume"])->name('.get');
        Route::get("v1/blood-volume/{id}", [BloodVolumeController::class, "blood_volume"])->name('.get_id');
        Route::post("v1/blood-volume", [BloodVolumeController::class, "blood_volume_create"])->name('.create');
        Route::put("v1/blood-volume/{id}", [BloodVolumeController::class, "blood_volume_update"])->name('.update');
        Route::delete("v1/blood-volume/{id}", [BloodVolumeController::class, "blood_volume_delete"])->name('.delete');
    });

    /// Đường dùng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineUseForm'], function () {
        Route::get("v1/medicine-use-form", [MedicineUseFormController::class, "medicine_use_form"])->name('.get');
        Route::get("v1/medicine-use-form/{id}", [MedicineUseFormController::class, "medicine_use_form"])->name('.get_id');
    });

    /// Loại thầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBidType'], function () {
        Route::get("v1/bid-type", [BidTypeController::class, "bid_type"])->name('.get');
        Route::get("v1/bid-type/{id}", [BidTypeController::class, "bid_type"])->name('.get_id');
        Route::post("v1/bid-type", [BidTypeController::class, "bid_type_create"])->name('.create');
        Route::put("v1/bid-type/{id}", [BidTypeController::class, "bid_type_update"])->name('.update');
        Route::delete("v1/bid-type/{id}", [BidTypeController::class, "bid_type_delete"])->name('.delete');
    });

    /// Loại thuốc - Hoạt chất
    Route::group(['as' => 'HIS.Desktop.Plugins.MedicineTypeActiveIngredient'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/medicine-type-acin", [MedicineTypeAcinController::class, "medicine_type_acin"])->name('.get');
        Route::get("v1/medicine-type-acin/{id}", [MedicineTypeAcinController::class, "medicine_type_acin"])->name('.get_id');
        // // Trả về tất cả loại thuốc cùng hoạt chất
        // Route::get("v1/medicine-type/all/active-ingredient", [HISController::class, "medicine_type_with_active_ingredient"]);
        // Route::get("v1/medicine-type/{id}/active-ingredient", [HISController::class, "medicine_type_with_active_ingredient"]);
        // // Trả về tất cả hoạt chất cùng loại thuốc
        // Route::get("v1/active-ingredient/all/medicine-type", [HISController::class, "active_ingredient_with_medicine_type"]);
        // Route::get("v1/active-ingredient/{id}/medicine-type", [HISController::class, "active_ingredient_with_medicine_type"]);
    });

    /// Nhóm ATC
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAtcGroup'], function () {
        Route::get("v1/atc-group", [AtcGroupController::class, "atc_group"])->name('.get');
        Route::get("v1/atc-group/{id}", [AtcGroupController::class, "atc_group"])->name('.get_id');
        Route::post("v1/atc-group", [AtcGroupController::class, "atc_group_create"])->name('.create');
        Route::put("v1/atc-group/{id}", [AtcGroupController::class, "atc_group_update"])->name('.update');
        Route::delete("v1/atc-group/{id}", [AtcGroupController::class, "atc_group_delete"])->name('.delete');
    });

    /// Nhóm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodGroup'], function () {
        Route::get("v1/blood-group", [BloodGroupController::class, "blood_group"])->name('.get');
        Route::get("v1/blood-group/{id}", [BloodGroupController::class, "blood_group"])->name('.get_id');
        Route::post("v1/blood-group", [BloodGroupController::class, "blood_group_create"])->name('.create');
        Route::put("v1/blood-group/{id}", [BloodGroupController::class, "blood_group_update"])->name('.update');
        Route::delete("v1/blood-group/{id}", [BloodGroupController::class, "blood_group_delete"])->name('.delete');
    });

    /// Nhóm thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineGroup'], function () {
        Route::get("v1/medicine-group", [MedicineGroupController::class, "medicine_group"])->name('.get');
        Route::get("v1/medicine-group/{id}", [MedicineGroupController::class, "medicine_group"])->name('.get_id');
    });

    /// Chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndex'], function () {
        Route::get("v1/test-index", [TestIndexController::class, "test_index"])->name('.get');
        Route::get("v1/test-index/{id}", [TestIndexController::class, "test_index"])->name('.get_id');
    });

    /// Đơn vị tính chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndexUnit'], function () {
        Route::get("v1/test-index-unit", [TestIndexUnitController::class, "test_index_unit"])->name('.get');
        Route::get("v1/test-index-unit/{id}", [TestIndexUnitController::class, "test_index_unit"])->name('.get_id');
    });

    /// Loại mẫu bệnh phẩm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestSampleType'], function () {
        Route::get("v1/test-sample-type", [TestSampleTypeController::class, "test_sample_type"])->name('.get');
        Route::get("v1/test-sample-type/{id}", [TestSampleTypeController::class, "test_sample_type"])->name('.get_id');
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


    // Debate Type
    Route::get("v1/debate-type", [DebateTypeController::class, "debate_type"])->name('.get_debate_type');
    Route::get("v1/debate-type/{id}", [DebateTypeController::class, "debate_type"])->name('.get_debate_type_id');

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
