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
Route::fallback(function(){
    return return_404_error_page_not_found();
});
Route::group([
    "middleware" => ["check_module:api"]
], function () {

    /// Khoa phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDepartment'], function () {
        Route::get("v1/department", [DepartmentController::class, "department"]);
        Route::get("v1/department/{id}", [DepartmentController::class, "department"])->name('.api.department.index_with_id');
        Route::get("v1/department-check", [CheckDepartmentController::class, "check_code"]);
        // Route::get("v1/department/deleted", [DepartmentController::class, "department"]);
        // Route::get("v1/department/{id}/restore", [DepartmentController::class, "department_restore"]);
        Route::post("v1/department", [DepartmentController::class, "department_create"]);        
        Route::put("v1/department/{id}", [DepartmentController::class, "department_update"]);       
        Route::delete("v1/department/{id}", [DepartmentController::class, "department_delete"]);         
    });

    /// Đơn vị
    Route::get("v1/group", [GroupController::class, "group"]);       
    Route::get("v1/group/{id}", [GroupController::class, "group"]);       

    /// Loại phòng
    Route::get("v1/room-type", [RoomTypeController::class, "room_type"]);    
    Route::get("v1/room-type/{id}", [RoomTypeController::class, "room_type"]);          

    /// Nhóm phòng
    Route::get("v1/room-group", [RoomGroupController::class, "room_group"]);    
    Route::get("v1/room-group/{id}", [RoomGroupController::class, "room_group"]);    
    Route::post("v1/room-group", [RoomGroupController::class, "room_group_create"]);    

    /// Link màn hình chờ
    Route::get("v1/screen-saver-module-link", [ScreenSaverModuleLinkController::class, "screen_saver_module_link"]);    

    /// Buồng bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedRoomList'], function () {
        Route::get("v1/bed-room", [BedRoomController::class, "bed_room"]);
        Route::get("v1/bed-room/{id}", [BedRoomController::class, "bed_room"])->name('.api.bed_room.index_with_id');
        Route::get("v1/bed-room-check", [CheckBedRoomController::class, "check_code"]);
        Route::post("v1/bed-room", [BedRoomController::class, "bed_room_create"]);        
        Route::put("v1/bed-room/{id}", [BedRoomController::class, "bed_room_update"]);       
        Route::delete("v1/bed-room/{id}", [BedRoomController::class, "bed_room_delete"]);       
    });

    /// Loại xét nghiệm
    Route::get("v1/test-type", [TestTypeController::class, "test_type"]);
    Route::get("v1/test-type/{id}", [TestTypeController::class, "test_type"]);

    /// Phòng khám/cls/pttt
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRoom'], function () {
        Route::get("v1/execute-room", [ExecuteRoomController::class, "execute_room"]);
        Route::get("v1/execute-room/{id}", [ExecuteRoomController::class, "execute_room"]);
        Route::get("v1/execute-room-check", [CheckExecuteRoomController::class, "check_code"]);
        Route::post("v1/execute-room", [ExecuteRoomController::class, "execute_room_create"]);        
        Route::put("v1/execute-room/{id}", [ExecuteRoomController::class, "execute_room_update"]);       
        Route::delete("v1/execute-room/{id}", [ExecuteRoomController::class, "execute_room_delete"]);  
    });

    /// Chuyên khoa
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSpeciality'], function () {
        Route::get("v1/speciality", [SpecialityController::class, "speciality"]);
        Route::get("v1/speciality/{id}", [SpecialityController::class, "speciality"]);
        Route::get("v1/speciality-check", [CheckSpecialityController::class, "check_code"]);
        Route::post("v1/speciality", [SpecialityController::class, "speciality_create"]);        
        Route::put("v1/speciality/{id}", [SpecialityController::class, "speciality_update"]);       
        Route::delete("v1/speciality/{id}", [SpecialityController::class, "speciality_delete"]);  
    });

    /// Diện điều trị
    Route::group(['as' => 'HIS.Desktop.Plugins.TreatmentType'], function () {
        Route::get("v1/treatment-type", [TreatmentTypeController::class, "treatment_type"]);
        Route::get("v1/treatment-type/{id}", [TreatmentTypeController::class, "treatment_type"]);
        Route::get("v1/treatment-type-check", [CheckTreatmentTypeController::class, "check_code"]);
        Route::post("v1/treatment-type", [TreatmentTypeController::class, "treatment_type_create"]);        
        Route::put("v1/treatment-type/{id}", [TreatmentTypeController::class, "treatment_type_update"]);       
        Route::delete("v1/treatment-type/{id}", [TreatmentTypeController::class, "treatment_type_delete"]);  
    });

    /// Cơ sở khám chữa bệnh ban đầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediOrg'], function () {
        Route::get("v1/medi-org", [MediOrgController::class, "medi_org"]);
        Route::get("v1/medi-org/{id}", [MediOrgController::class, "medi_org"]);
        Route::get("v1/medi-org-check", [CheckMediOrgController::class, "check_code"]);
        Route::post("v1/medi-org", [MediOrgController::class, "medi_org_create"]);        
        Route::put("v1/medi-org/{id}", [MediOrgController::class, "medi_org_update"]);       
        Route::delete("v1/medi-org/{id}", [MediOrgController::class, "medi_org_delete"]);  
    });

    /// Cơ sở/Xã phường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBranch'], function () {
        Route::get("v1/branch", [BranchController::class, "branch"]);
        Route::get("v1/branch/{id}", [BranchController::class, "branch"]);
        Route::get("v1/branch-check", [CheckBranchController::class, "check_code"]);
        Route::post("v1/branch", [BranchController::class, "branch_create"]);        
        Route::put("v1/branch/{id}", [BranchController::class, "branch_update"]);       
        Route::delete("v1/branch/{id}", [BranchController::class, "branch_delete"]);  
    });

    /// Huyện
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaDistrict'], function () {
        Route::get("v1/district", [DistrictController::class, "district"]);
        Route::get("v1/district/{id}", [DistrictController::class, "district"]);
        Route::get("v1/district-check", [CheckDistrictController::class, "check_code"]);
        Route::post("v1/district", [DistrictController::class, "district_create"]);        
        Route::put("v1/district/{id}", [DistrictController::class, "district_update"]);       
        Route::delete("v1/district/{id}", [DistrictController::class, "district_delete"]);  
    });    

    /// Nguồn chi trả khác
    Route::group(['as' => 'HIS.Desktop.Plugins.HisOtherPaySource'], function () {
        Route::get("v1/other-pay-source", [OtherPaySourceController::class, "other_pay_source"]);
        Route::get("v1/other-pay-source/{id}", [OtherPaySourceController::class, "other_pay_source"]);
        Route::get("v1/other-pay-source-check", [CheckOtherPaySourceController::class, "check_code"]);
        Route::post("v1/other-pay-source", [OtherPaySourceController::class, "other_pay_source_create"]);        
        Route::put("v1/other-pay-source/{id}", [OtherPaySourceController::class, "other_pay_source_update"]);       
        Route::delete("v1/other-pay-source/{id}", [OtherPaySourceController::class, "other_pay_source_delete"]);  
    });

    /// Quân hàm
    Route::get("v1/military-rank", [MilitaryRankController::class, "military_rank"]);
    Route::get("v1/military-rank/{id}", [MilitaryRankController::class, "military_rank"]);


    /// Kho
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediStock'], function () {
        Route::get("v1/medi-stock", [MediStockController::class, "medi_stock"])->name('.api.medi_stock.index');
        Route::get("v1/medi-stock/{id}", [MediStockController::class, "medi_stock"]);
        Route::get("v1/medi-stock-check", [CheckMediStockController::class, "check_code"]);
        Route::post("v1/medi-stock", [MediStockController::class, "medi_stock_create"]);        
        Route::put("v1/medi-stock/{id}", [MediStockController::class, "medi_stock_update"]);       
        Route::delete("v1/medi-stock/{id}", [MediStockController::class, "medi_stock_delete"]);
    });

    /// Khu đón tiếp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisReceptionRoom'], function () {
        Route::get("v1/reception-room", [ReceptionRoomController::class, "reception_room"]);
        Route::get("v1/reception-room/{id}", [ReceptionRoomController::class, "reception_room"]);
        Route::get("v1/reception-room-check", [CheckReceptionRoomController::class, "check_code"]);
        Route::post("v1/reception-room", [ReceptionRoomController::class, "reception_room_create"]);        
        Route::put("v1/reception-room/{id}", [ReceptionRoomController::class, "reception_room_update"]);       
        Route::delete("v1/reception-room/{id}", [ReceptionRoomController::class, "reception_room_delete"]);  
    });
    
    /// Khu vực
    Route::group(['as' => 'HIS.Desktop.Plugins.HisArea'], function () {
        Route::get("v1/area", [AreaController::class, "area"]);
        Route::get("v1/area/{id}", [AreaController::class, "area"]);
        Route::get("v1/area-check", [CheckAreaController::class, "check_code"]);
        Route::post("v1/area", [AreaController::class, "area_create"]);
        Route::put("v1/area/{id}", [AreaController::class, "area_update"]);
        Route::delete("v1/area/{id}", [AreaController::class, "area_delete"]);
    });

    /// Nhà ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRefectory'], function () {
        Route::get("v1/refectory", [RefectoryController::class, "refectory"]);
        Route::get("v1/refectory/{id}", [RefectoryController::class, "refectory"]);
        Route::get("v1/refectory-check", [CheckRefectoryController::class, "check_code"]);
        Route::post("v1/refectory", [RefectoryController::class, "refectory_create"]);
        Route::put("v1/refectory/{id}", [RefectoryController::class, "refectory_update"]);
        Route::delete("v1/refectory/{id}", [RefectoryController::class, "refectory_delete"]);
    });

    /// Nhóm thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteGroup'], function () {
        Route::get("v1/execute-group", [ExecuteGroupController::class, "execute_group"]);
        Route::get("v1/execute-group/{id}", [ExecuteGroupController::class, "execute_group"]);
        Route::get("v1/execute-group-check", [CheckExecuteGroupController::class, "check_code"]);
        Route::post("v1/execute-group", [ExecuteGroupController::class, "execute_group_create"]);
        Route::put("v1/execute-group/{id}", [ExecuteGroupController::class, "execute_group_update"]);
        Route::delete("v1/execute-group/{id}", [ExecuteGroupController::class, "execute_group_delete"]);
    });

    /// Phòng thu ngân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCashierRoom'], function () {
        Route::get("v1/cashier-room", [CashierRoomController::class, "cashier_room"]);
        Route::get("v1/cashier-room/{id}", [CashierRoomController::class, "cashier_room"]);
        Route::get("v1/cashier-room-check", [CheckCashierRoomController::class, "check_code"]);
        Route::post("v1/cashier-room", [CashierRoomController::class, "cashier_room_create"]);
        Route::put("v1/cashier-room/{id}", [CashierRoomController::class, "cashier_room_update"]);
        Route::delete("v1/cashier-room/{id}", [CashierRoomController::class, "cashier_room_delete"]);
    });

    /// Quốc gia
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaNational'], function () {
        Route::get("v1/national", [NationalController::class, "national"]);
        Route::get("v1/national/{id}", [NationalController::class, "national"]);
        Route::get("v1/national-check", [CheckNationalController::class, "check_code"]);
        Route::post("v1/national", [NationalController::class, "national_create"]);
        Route::put("v1/national/{id}", [NationalController::class, "national_update"]);
        Route::delete("v1/national/{id}", [NationalController::class, "national_delete"]);
    });
    
    /// Tỉnh
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaProvince'], function () {
        Route::get("v1/province", [ProvinceController::class, "province"]);
        Route::get("v1/province/{id}", [ProvinceController::class, "province"]);
        Route::get("v1/province-check", [CheckProvinceController::class, "check_code"]);
        Route::post("v1/province", [ProvinceController::class, "province_create"]);
        Route::put("v1/province/{id}", [ProvinceController::class, "province_update"]);
        Route::delete("v1/province/{id}", [ProvinceController::class, "province_delete"]);
    });

    /// Tủ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDataStore'], function () {
        Route::get("v1/data-store", [DataStoreController::class, "data_store"]);
        Route::get("v1/data-store/{id}", [DataStoreController::class, "data_store"]);
        Route::get("v1/data-store-check", [CheckDataStoreController::class, "check_code"]);
        Route::post("v1/data-store", [DataStoreController::class, "data_store_create"]);
        Route::put("v1/data-store/{id}", [DataStoreController::class, "data_store_update"]);
        Route::delete("v1/data-store/{id}", [DataStoreController::class, "data_store_delete"]);
    });

    /// Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRole'], function () {
        Route::get("v1/execute-role", [ExecuteRoleController::class, "execute_role"]);
        Route::get("v1/execute-role/{id}", [ExecuteRoleController::class, "execute_role"]);
        Route::get("v1/execute-role-check", [CheckExecuteRoleController::class, "check_code"]);
        Route::post("v1/execute-role", [ExecuteRoleController::class, "execute_role_create"]);
        Route::put("v1/execute-role/{id}", [ExecuteRoleController::class, "execute_role_update"]);
        Route::delete("v1/execute-role/{id}", [ExecuteRoleController::class, "execute_role_delete"]);
    });

    /// Xã
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaCommune'], function () {
        Route::get("v1/commune", [CommuneController::class, "commune"]);
        Route::get("v1/commune/{id}", [CommuneController::class, "commune"]);
        Route::get("v1/commune-check", [CheckCommuneController::class, "check_code"]);
        Route::post("v1/commune", [CommuneController::class, "commune_create"]);
        Route::put("v1/commune/{id}", [CommuneController::class, "commune_update"]);
        Route::delete("v1/commune/{id}", [CommuneController::class, "commune_delete"]);
    });

    /// Icd - Cm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisIcdCm'], function () {
        Route::get("v1/icd-cm", [IcdCmController::class, "icd_cm"]);
        Route::get("v1/icd-cm/{id}", [IcdCmController::class, "icd_cm"]);
        Route::get("v1/icd-cm-check", [CheckIcdCmController::class, "check_code"]);
        Route::post("v1/icd-cm", [IcdCmController::class, "icd_cm_create"]);
        Route::put("v1/icd-cm/{id}", [IcdCmController::class, "icd_cm_update"]);
        Route::delete("v1/icd-cm/{id}", [IcdCmController::class, "icd_cm_delete"]);
    });

    /// Loại chẩn đoán hình ảnh
    Route::get("v1/diim-type", [DiimTypeController::class, "diim_type"]);
    Route::get("v1/diim-type/{id}", [DiimTypeController::class, "diim_type"]);

    /// Loại thăm dò chức năng
    Route::get("v1/fuex-type", [FuexTypeController::class, "fuex_type"]);
    Route::get("v1/fuex-type/{id}", [FuexTypeController::class, "fuex_type"]);

    /// Cỡ phim
    Route::get("v1/film-size", [FilmSizeController::class, "film_size"]);
    Route::get("v1/film-size/{id}", [FilmSizeController::class, "film_size"]);

    /// Giới tính
    Route::get("v1/gender", [GenderController::class, "gender"]);
    Route::get("v1/gender/{id}", [GenderController::class, "gender"]);

    /// Bộ phận cơ thể
    Route::get("v1/body-part", [BodyPartController::class, "body_part"]);
    Route::get("v1/body-part/{id}", [BodyPartController::class, "body_part"]);
    Route::get("v1/body-part-check", [CheckBodyPartController::class, "check_code"]);
    Route::post("v1/body-part", [BodyPartController::class, "body_part_create"]);
    Route::put("v1/body-part/{id}", [BodyPartController::class, "body_part_update"]);
    Route::delete("v1/body-part/{id}", [BodyPartController::class, "body_part_delete"]);

    /// Module xử lý dịch vụ
    Route::get("v1/exe-service-module", [ExeServiceModuleController::class, "exe_service_module"]);
    Route::get("v1/exe-service-module/{id}", [ExeServiceModuleController::class, "exe_service_module"]);

    /// Chỉ số
    Route::get("v1/suim-index", [SuimIndexController::class, "suim_index"]);
    Route::get("v1/suim-index/{id}", [SuimIndexController::class, "suim_index"]);

    /// Gói
    Route::get("v1/package", [PackageController::class, "package"]);
    Route::get("v1/package/{id}", [PackageController::class, "package"]);

    /// Dịch vụ kỹ thuật
    Route::group(['as' => 'HIS.Desktop.Plugins.HisService'], function () {
        Route::get("v1/service", [ServiceController::class, "service"]);
        Route::get("v1/service/{id}", [ServiceController::class, "service"]);
        // Route::get("v1/service/by-code/{type_id}", [ServiceController::class, "service_by_code"]);
        Route::get("v1/service/service-type/{id}", [ServiceController::class, "service_by_service_type"]);
        Route::get("v1/service-check", [CheckServiceController::class, "check_code"]);
        Route::post("v1/service", [ServiceController::class, "service_create"]);
        Route::put("v1/service/{id}", [ServiceController::class, "service_update"]);
        Route::delete("v1/service/{id}", [ServiceController::class, "service_delete"]);
    });

    /// Chính sách giá dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServicePatyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-paty", [ServicePatyController::class, "service_paty"]);
        Route::get("v1/service-paty/{id}", [ServicePatyController::class, "service_paty"]);
        Route::post("v1/service-paty", [ServicePatyController::class, "service_paty_create"]);
        Route::put("v1/service-paty/{id}", [ServicePatyController::class, "service_paty_update"]);
        Route::delete("v1/service-paty/{id}", [ServicePatyController::class, "service_paty_delete"]);
        // // Trả về tất cả dịch vụ cùng loại bệnh nhân
        // Route::get("v1/service/all/patient-type", [ServicePatyController::class, "service_with_patient_type"]);
        // Route::get("v1/service/{id}/patient-type", [ServicePatyController::class, "service_with_patient_type"]);
        // // Trả về tất cả loại bệnh nhân cùng dịch vụ
        // Route::get("v1/patient-type/all/service", [ServicePatyController::class, "patient_type_with_service"]);
        // Route::get("v1/patient-type/{id}/service", [ServicePatyController::class, "patient_type_with_service"]);

    });

    /// Điều kiện dịch vụ
    Route::get("v1/service-condition", [ServiceConditionController::class, "service_condition"]);
    Route::get("v1/service-condition/{id}", [ServiceConditionController::class, "service_condition"]);

    /// Dịch vụ máy
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceMachine'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-machine", [ServiceMachineController::class, "service_machine"]);
        Route::get("v1/service-machine/{id}", [ServiceMachineController::class, "service_machine"]);
        // Trả về tất cả dịch vụ cùng máy
        Route::get("v1/service/all/machine", [ServiceMachineController::class, "service_with_machine"]);
        Route::get("v1/service/{id}/machine", [ServiceMachineController::class, "service_with_machine"]);
        // Trả về tất cả máy cùng dịch vụ
        Route::get("v1/machine/all/service", [ServiceMachineController::class, "machine_with_service"]);
        Route::get("v1/machine/{id}/service", [ServiceMachineController::class, "machine_with_service"]);
    });

    /// Máy / Máy cận lâm sàn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMachine'], function () {
        Route::get("v1/machine", [MachineController::class, "machine"]);
        Route::get("v1/machine/{id}", [MachineController::class, "machine"]);
    });

    /// Dịch vụ phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomService'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-room", [ServiceRoomController::class, "service_room"]);
        Route::get("v1/service-room/{id}", [ServiceRoomController::class, "service_room"]);
        // // Trả về tất cả dịch vụ cùng phòng
        // Route::get("v1/service/all/room", [HISController::class, "service_with_room"]);
        // Route::get("v1/service/{id}/room", [HISController::class, "service_with_room"]);
        // // Trả về tất cả phòng cùng dịch vụ
        // Route::get("v1/room/all/service", [HISController::class, "room_with_service"]);
        // Route::get("v1/room/{id}/service", [HISController::class, "room_with_service"]);
    });

    /// Phòng
    Route::get("v1/room", [RoomController::class, "room"]);
    // Route::get("v1/room/department/{id}", [RoomController::class, "room_with_department"]);

    /// Dịch vụ đi kèm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceFollow'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-follow", [ServiceFollowController::class, "service_follow"]);
        Route::get("v1/service-follow/{id}", [ServiceFollowController::class, "service_follow"]);
        // // Trả về tất cả dịch vụ cùng dịch vụ đi kèm
        // Route::get("v1/service/all/follow", [HISController::class, "service_with_follow"]);
        // Route::get("v1/service/{id}/follow", [HISController::class, "service_with_follow"]);
        // // Trả về tất cả dịch vụ đi kèm cùng dịch vụ
        // Route::get("v1/follow/all/service", [HISController::class, "follow_with_service"]);
        // Route::get("v1/follow/{id}/service", [HISController::class, "follow_with_service"]);
    });

    /// Giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBed'], function () {
        Route::get("v1/bed", [BedController::class, "bed"]);
        Route::get("v1/bed/{id}", [BedController::class, "bed"]);
    });

    /// Giường - Dịch vụ giường
    Route::group(['as' => 'HIS.Desktop.Plugins.BedBsty'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/bed-bsty", [BedBstyController::class, "bed_bsty"]);
        Route::get("v1/bed-bsty/{id}", [BedBstyController::class, "bed_bsty"]);
        // // Trả về tất cả dịch vụ cùng giường
        // Route::get("v1/service/all/bed", [HISController::class, "service_with_bed"]);
        // Route::get("v1/service/{id}/bed", [HISController::class, "service_with_bed"]);
        // // Trả về tất cả giường cùng dịch vụ
        // Route::get("v1/bed/all/service", [HISController::class, "bed_with_service"]);
        // Route::get("v1/bed/{id}/service", [HISController::class, "bed_with_service"]);
    });

    /// Loại giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedTypeList'], function () {
        Route::get("v1/bed-type", [BedTypeController::class, "bed_type"]);
        Route::get("v1/bed-type/{id}", [BedTypeController::class, "bed_type"]);
    });

    /// Nhóm dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServSegr'], function () {
        Route::get("v1/serv-segr", [HISController::class, "serv_segr"]);
        Route::get("v1/serv-segr/{id}", [HISController::class, "serv_segr"]);
    });
    Route::get("v1/service-group", [HISController::class, "service_group"]);
    Route::get("v1/service-group/{id}", [HISController::class, "service_group_id"]);

    /// Tài khoản nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.EmpUser'], function () {
        Route::get("v1/emp-user", [EmpUserController::class, "emp_user"]);
        Route::get("v1/emp-user/{id}", [EmpUserController::class, "emp_user"]);
    });

    /// Thông tin tài khoản
    Route::group(['as' => 'HIS.Desktop.Plugins.InfoUser'], function () {
        Route::get("v1/info-user/{id}", [HISController::class, "info_user_id"]);
    });

    /// Tài khoản - Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.ExecuteRoleUser'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/execute-role-user", [HISController::class, "execute_role_user"]);
        Route::get("v1/execute-role-user/{id}", [HISController::class, "execute_role_user"]);
        // Trả về tất cả tài khoản cùng vai trò thực hiện
        Route::get("v1/user/all/execute-role", [HISController::class, "user_with_execute_role"]);
        Route::get("v1/user/{id}/execute-role", [HISController::class, "user_with_execute_role"]);
        // Trả về tất cả vai trò thực hiện cùng tài khoản
        Route::get("v1/execute-role/all/user", [HISController::class, "execute_role_with_user"]);
        Route::get("v1/execute-role/{id}/user", [HISController::class, "execute_role_with_user"]);
    });

    /// Vai trò
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsRole'], function () {
        Route::get("v1/role", [RoleController::class, "role"]);
        Route::get("v1/role/{id}", [RoleController::class, "role"]);
    });

    /// Vai trò - Chức năng 
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModuleRole'], function () {
        Route::get("v1/module-role", [HISController::class, "module_role"]);
        Route::get("v1/module-role/{id}", [HISController::class, "module_role"]);
    });

    /// Dân tộc
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaEthnic'], function () {
        Route::get("v1/ethnic", [EthnicController::class, "ethnic"]);
        Route::get("v1/ethnic/{id}", [EthnicController::class, "ethnic"]);
    });

    /// Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientType'], function () {
        Route::get("v1/patient-type", [PatientTypeController::class, "patient_type"]);
        // Route::get("v1/patient-type/is-addition", [PatientTypeController::class, "patient_type_is_addition"]);
        Route::get("v1/patient-type/{id}", [PatientTypeController::class, "patient_type"]);
    });

    /// Đối tượng ưu tiên
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPriorityType'], function () {
        Route::get("v1/priority-type", [PriorityTypeController::class, "priority_type"]);
        Route::get("v1/priority-type/{id}", [PriorityTypeController::class, "priority_type"]);
    });

    /// Mối quan hệ
    Route::group(['as' => 'HIS.Desktop.Plugins.EmrRelationList'], function () {
        Route::get("v1/relation-list", [RelationController::class, "relation_list"]);
        Route::get("v1/relation-list/{id}", [RelationController::class, "relation_list"]);
    });

    /// Nghề nghiệp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCareer'], function () {
        Route::get("v1/career", [CareerController::class, "career"]);
        Route::get("v1/career/{id}", [CareerController::class, "career"]);
    });

    /// Phân loại bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientClassify'], function () {
        Route::get("v1/patient-classify", [PatientClassifyController::class, "patient_classify"]);
        Route::get("v1/patient-classify/{id}", [PatientClassifyController::class, "patient_classify"]);
        Route::get("v1/patient-classify-check", [CheckPatientClassifyController::class, "check_code"]);
        Route::post("v1/patient-classify", [PatientClassifyController::class, "patient_classify_create"]);        
        Route::put("v1/patient-classify/{id}", [PatientClassifyController::class, "patient_classify_update"]);       
        Route::delete("v1/patient-classify/{id}", [PatientClassifyController::class, "patient_classify_delete"]);  
    });

    /// Tôn giáo
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaReligion'], function () {
        Route::get("v1/religion", [ReligionController::class, "religion"]);
        Route::get("v1/religion/{id}", [ReligionController::class, "religion"]);
    });

    /// Đơn vị tính
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceUnitEdit'], function () {
        Route::get("v1/service-unit", [ServiceUnitController::class, "service_unit"]);
        Route::get("v1/service-unit/{id}", [ServiceUnitController::class, "service_unit"]);
    });

    /// Loại dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceType'], function () {
        Route::get("v1/service-type", [ServiceTypeController::class, "service_type"]);
        Route::get("v1/service-type/{id}", [ServiceTypeController::class, "service_type"]);
    });

    /// Nhóm xuất ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationGroup'], function () {
        Route::get("v1/ration-group", [RationGroupController::class, "ration_group"]);
        Route::get("v1/ration-group/{id}", [RationGroupController::class, "ration_group"]);
    });

    /// Loại y lệnh 
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqType'], function () {
        Route::get("v1/service-req-type", [ServiceReqTypeController::class, "service_req_type"]);
        Route::get("v1/service-req-type/{id}", [ServiceReqTypeController::class, "service_req_type"]);
    });

    /// Bữa ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationTime'], function () {
        Route::get("v1/ration-time", [RationTimeController::class, "ration_time"]);
        Route::get("v1/ration-time/{id}", [RationTimeController::class, "ration_time"]);
    });

    /// Kho - Đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestPatientType'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/mest-patient-type", [HISController::class, "mest_patient_type"]);
        Route::get("v1/mest-patient-type/{id}", [HISController::class, "mest_patient_type"]);
        // Trả về tất cả kho cùng đối tượng
        Route::get("v1/medi-stock/all/patient-type", [HISController::class, "medi_stock_with_patient_type"]);
        Route::get("v1/medi-stock/{id}/patient-type", [HISController::class, "medi_stock_with_patient_type"]);
        // Trả về tất cả đối tượng cùng kho
        Route::get("v1/patient-type/all/medi-stock", [HISController::class, "patient_type_with_medi_stock"]);
        Route::get("v1/patient-type/{id}/medi-stock", [HISController::class, "patient_type_with_medi_stock"]);
    });

    /// Kho - Loại thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMetyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/medi-stock-mety-list", [HISController::class, "medi_stock_mety_list"]);
        Route::get("v1/medi-stock-mety-list/{id}", [HISController::class, "medi_stock_mety_list"]);
        // Trả về tất cả kho cùng loại thuốc 
        Route::get("v1/medi-stock/all/medicine-type", [HISController::class, "medi_stock_with_medicine_type"]);
        Route::get("v1/medi-stock/{id}/medicine-type", [HISController::class, "medi_stock_with_medicine_type"]);
        // Trả về tất cả loại thuốc cùng kho
        Route::get("v1/medicine-type/all/medi-stock", [HISController::class, "medicine_type_with_medi_stock"]);
        Route::get("v1/medicine-type/{id}/medi-stock", [HISController::class, "medicine_type_with_medi_stock"]);
    });

    /// Kho - Loại vật tư
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMatyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/medi-stock-maty-list", [HISController::class, "medi_stock_maty_list"]);
        Route::get("v1/medi-stock-maty-list/{id}", [HISController::class, "medi_stock_maty_list"]);
        // Trả về tất cả kho cùng loại vật tư 
        Route::get("v1/medi-stock/all/material-type", [HISController::class, "medi_stock_with_material_type"]);
        Route::get("v1/medi-stock/{id}/material-type", [HISController::class, "medi_stock_with_material_type"]);
        // Trả về tất cả loại vật tư cùng kho
        Route::get("v1/material-type/all/medi-stock", [HISController::class, "material_type_with_medi_stock"]);
        Route::get("v1/material-type/{id}/medi-stock", [HISController::class, "material_type_with_medi_stock"]);
    });

    /// Kho - Phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestExportRoom'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/mest-export-room", [HISController::class, "mest_export_room"]);
        Route::get("v1/mest-export-room/{id}", [HISController::class, "mest_export_room"]);
        // Trả về tất cả kho cùng phòng
        Route::get("v1/medi-stock/all/room", [HISController::class, "medi_stock_with_room"]);
        Route::get("v1/medi-stock/{id}/room", [HISController::class, "medi_stock_with_room"]);
        // Trả về tất cả phòng cùng kho
        Route::get("v1/room/all/medi-stock", [HISController::class, "room_with_medi_stock"]);
        Route::get("v1/room/{id}/medi-stock", [HISController::class, "room_with_medi_stock"]);
    });

    /// Phòng chỉ định - Phòng thực hiện 
    Route::group(['as' => 'HIS.Desktop.Plugins.ExroRoom'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/exro-room", [HISController::class, "exro_room"]);
        Route::get("v1/exro-room/{id}", [HISController::class, "exro_room"]);
        // Trả về tất cả phòng thực hiện cùng phòng chỉ định
        Route::get("v1/execute-room/all/room", [HISController::class, "execute_room_with_room"]);
        Route::get("v1/execute-room/{id}/room", [HISController::class, "execute_room_with_room"]);
        // Trả về tất cả phòng chỉ định cùng phòng thực hiện
        Route::get("v1/room/all/execute-room", [HISController::class, "room_with_execute_room"]);
        Route::get("v1/room/{id}/execute-room", [HISController::class, "room_with_execute_room"]);
    });

    /// Phòng thực hiện - Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeRoom'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/patient-type-room", [HISController::class, "patient_type_room"]);
        Route::get("v1/patient-type-room/{id}", [HISController::class, "patient_type_room"]);
        // Trả về tất cả phòng thực hiện cùng đối tượng bệnh nhân
        Route::get("v1/room/all/patient-type", [HISController::class, "room_with_patient_type"]);
        Route::get("v1/room/{id}/patient-type", [HISController::class, "room_with_patient_type"]);
        // Trả về tất cả đối tượng bệnh nhân cùng phòng thực hiện
        Route::get("v1/patient-type/all/room", [HISController::class, "patient_type_with_room"]);
        Route::get("v1/patient-type/{id}/room", [HISController::class, "patient_type_with_room"]);
    });

    /// Thiết lập lợi nhuận xuất bán
    Route::group(['as' => 'HIS.Desktop.Plugins.EstablishSaleProfitCFG'], function () {
        Route::get("v1/sale-profit-cfg", [SaleProfitCfgController::class, "sale_profit_cfg"]);
        Route::get("v1/sale-profit-cfg/{id}", [SaleProfitCfgController::class, "sale_profit_cfg"]);
    });

    /// Chuyển đổi đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.PatientTypeAllow'], function () {
        Route::get("v1/patient-type-allow", [HISController::class, "patient_type_allow"]);
        Route::get("v1/patient-type-allow/{id}", [HISController::class, "patient_type_allow"]);
    });

    /// Chức vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPosition'], function () {
        Route::get("v1/position", [HISController::class, "position"]);
        Route::get("v1/position/{id}", [HISController::class, "position"]);
    });

    /// Nơi làm việc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisWorkPlace'], function () {
        Route::get("v1/work-place", [HISController::class, "work_place"]);
        Route::get("v1/work-place/{id}", [HISController::class, "work_place"]);
    });

    /// Ngôi thai
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBornPosition'], function () {
        Route::get("v1/born-position", [HISController::class, "born_position"]);
        Route::get("v1/born-position/{id}", [HISController::class, "born_position"]);
    });

    /// Trường hợp bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientCase'], function () {
        Route::get("v1/patient-case", [HISController::class, "patient_case"]);
        Route::get("v1/patient-case/{id}", [HISController::class, "patient_case"]);
    });

    /// Đầu mã thẻ BHYT
    Route::group(['as' => 'BHYT HIS.Desktop.Plugins.HisBhytWhitelist'], function () {
        Route::get("v1/bhyt-whitelist", [BhytWhitelistController::class, "bhyt_whitelist"]);
        Route::get("v1/bhyt-whitelist/{id}", [BhytWhitelistController::class, "bhyt_whitelist"]);
    });

    /// Nhóm dịch vụ BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisHeinServiceType'], function () {
        Route::get("v1/hein-service-type", [HeinServiceTypeController::class, "hein_service_type"]);
        Route::get("v1/hein-service-type/{id}", [HeinServiceTypeController::class, "hein_service_type"]);
    });

    /// Tham số BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBHYTParam'], function () {
        Route::get("v1/bhyt-param", [HISController::class, "bhyt_param"]);
        Route::get("v1/bhyt-param/{id}", [HISController::class, "bhyt_param"]);
    });

    /// Thẻ BHYT không hợp lệ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBhytBlacklist'], function () {
        Route::get("v1/bhyt-blacklist", [HISController::class, "bhyt_blacklist"]);
        Route::get("v1/bhyt-blacklist/{id}", [HISController::class, "bhyt_blacklist"]);
    });

    /// Chính sách giá thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicinePaty'], function () {
        Route::get("v1/medicine-paty", [HISController::class, "medicine_paty"]);
        Route::get("v1/medicine-paty/{id}", [HISController::class, "medicine_paty"]);
    });

    /// Bộ phận thương tích
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentBodyPart'], function () {
        Route::get("v1/accident-body-part", [HISController::class, "accident_body_part"]);
        Route::get("v1/accident-body-part/{id}", [HISController::class, "accident_body_part"]);
    });

    /// Chế phẩm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPreparationsBlood'], function () {
        Route::get("v1/preparations-blood", [HISController::class, "preparations_blood"]);
        Route::get("v1/preparations-blood/{id}", [HISController::class, "preparations_blood"]);
    });

    /// Chống chỉ định
    Route::group(['as' => 'HIS.Desktop.Plugins.HisContraindication'], function () {
        Route::get("v1/contraindication", [HISController::class, "contraindication"]);
        Route::get("v1/contraindication/{id}", [HISController::class, "contraindication"]);
    });

    /// Dạng bào chế
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDosageForm'], function () {
        Route::get("v1/dosage-form", [HISController::class, "dosage_form"]);
        Route::get("v1/dosage-form/{id}", [HISController::class, "dosage_form"]);
    });

    /// Địa điểm tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentLocation'], function () {
        Route::get("v1/accident-location", [HISController::class, "accident_location"]);
        Route::get("v1/accident-location/{id}", [HISController::class, "accident_location"]);
    });

    /// Hạng lái xe
    Route::group(['as' => 'HIS.Desktop.Plugins.LicenseClass'], function () {
        Route::get("v1/license-class", [HISController::class, "license_class"]);
        Route::get("v1/license-class/{id}", [HISController::class, "license_class"]);
    });

    /// Hãng sản xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisManufacturer'], function () {
        Route::get("v1/manufacturer", [HISController::class, "manufacturer"]);
        Route::get("v1/manufacturer/{id}", [HISController::class, "manufacturer"]);
    });

    /// ICD - Accepted Icd - Chẩn đoán
    Route::group(['as' => 'HIS.Desktop.Plugins.HisIcd'], function () {
        Route::get("v1/icd", [HISController::class, "icd"]);
        Route::get("v1/icd/{id}", [HISController::class, "icd"]);
    });

    /// Loại bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediRecordType'], function () {
        Route::get("v1/medi-record-type", [HISController::class, "medi_record_type"]);
        Route::get("v1/medi-record-type/{id}", [HISController::class, "medi_record_type"]);
    });

    /// Loại giấy tờ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisFileType'], function () {
        Route::get("v1/file-type", [HISController::class, "file_type"]);
        Route::get("v1/file-type/{id}", [HISController::class, "file_type"]);
    });

    /// Loại ra viện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTreatmentEndType'], function () {
        Route::get("v1/treatment-end-type", [HISController::class, "treatment_end_type"]);
        Route::get("v1/treatment-end-type/{id}", [HISController::class, "treatment_end_type"]);
    });

    /// Lý do chuyển tuyến chuyên môn
    Route::group(['as' => 'HIS.Desktop.Plugins.TranPatiTech'], function () {
        Route::get("v1/tran-pati-tech", [HISController::class, "tran_pati_tech"]);
        Route::get("v1/tran-pati-tech/{id}", [HISController::class, "tran_pati_tech"]);
    });

    /// Lý do hội chẩn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDebateReason'], function () {
        Route::get("v1/debate-reason", [HISController::class, "debate_reason"]);
        Route::get("v1/debate-reason/{id}", [HISController::class, "debate_reason"]);
    });

    /// Lý do hủy giao dịch
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCancelReason'], function () {
        Route::get("v1/cancel-reason", [HISController::class, "cancel_reason"]);
        Route::get("v1/cancel-reason/{id}", [HISController::class, "cancel_reason"]);
    });

    /// Lý do kê đơn tương tác
    Route::group(['as' => 'HIS.Desktop.Plugins.InteractionReason'], function () {
        Route::get("v1/interaction-reason", [HISController::class, "interaction_reason"]);
        Route::get("v1/interaction-reason/{id}", [HISController::class, "interaction_reason"]);
    });

    /// Lý do mở trần
    Route::group(['as' => 'HIS.Desktop.Plugins.HisUnlimitReason'], function () {
        Route::get("v1/unlimit-reason", [HISController::class, "unlimit_reason"]);
        Route::get("v1/unlimit-reason/{id}", [HISController::class, "unlimit_reason"]);
    });

    /// Lý do nhập viện
    Route::group(['as' => 'HIS.Desktop.Plugins.HospitalizeReason'], function () {
        Route::get("v1/hospitalize-reason", [HISController::class, "hospitalize_reason"]);
        Route::get("v1/hospitalize-reason/{id}", [HISController::class, "hospitalize_reason"]);
    });

    /// Lý do xuất
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExpMestReason'], function () {
        Route::get("v1/exp-mest-reason", [HISController::class, "exp_mest_reason"]);
        Route::get("v1/exp-mest-reason/{id}", [HISController::class, "exp_mest_reason"]);
    });

    /// Nghề nghiệp nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.CareerTitle'], function () {
        Route::get("v1/career-title", [HISController::class, "career_title"]);
        Route::get("v1/career-title/{id}", [HISController::class, "career_title"]);
    });

    /// Nguyên nhân tai nạn 
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentHurtType'], function () {
        Route::get("v1/accident-hurt-type", [HISController::class, "accident_hurt_type"]);
        Route::get("v1/accident-hurt-type/{id}", [HISController::class, "accident_hurt_type"]);
    });

    /// Nhà cung cấp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSupplier'], function () {
        Route::get("v1/supplier", [HISController::class, "supplier"]);
        Route::get("v1/supplier/{id}", [HISController::class, "supplier"]);
    });

    /// Phương pháp ché biến
    Route::group(['as' => 'HIS.Desktop.Plugins.HisProcessing'], function () {
        Route::get("v1/processing-method", [HISController::class, "processing_method"]);
        Route::get("v1/processing-method/{id}", [HISController::class, "processing_method"]);
    });

    /// Thời gian tử vong
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDeathWithin'], function () {
        Route::get("v1/death-within", [HISController::class, "death_within"]);
        Route::get("v1/death-within/{id}", [HISController::class, "death_within"]);
    });

    /// Vị trí hồ sơ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.LocationTreatment'], function () {
        Route::get("v1/location-treatment", [HISController::class, "location_treatment"]);
        Route::get("v1/location-treatment/{id}", [HISController::class, "location_treatment"]);
    });

    /// Xử lý sau tai nạn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAccidentCare'], function () {
        Route::get("v1/accident-care", [HISController::class, "accident_care"]);
        Route::get("v1/accident-care/{id}", [HISController::class, "accident_care"]);
    });

    /// Bàn mổ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttTable'], function () {
        Route::get("v1/pttt-table", [HISController::class, "pttt_table"]);
        Route::get("v1/pttt-table/{id}", [HISController::class, "pttt_table"]);
    });

    /// Nhóm PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttGroup'], function () {
        //Trả về tất cả nhóm pttt cùng nhóm dịch vụ 
        Route::get("v1/pttt-group", [PtttGroupController::class, "pttt_group"]);
        Route::get("v1/pttt-group/{id}", [PtttGroupController::class, "pttt_group"]);
    });

    /// Phương pháp PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttMethod'], function () {
        //Trả về tất cả nhóm pttt cùng nhóm dịch vụ 
        Route::get("v1/pttt-method", [PtttMethodController::class, "pttt_method"]);
        Route::get("v1/pttt-method/{id}", [PtttMethodController::class, "pttt_method"]);
    });

    /// Phương pháp vô cảm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisEmotionlessMethod'], function () {
        Route::get("v1/emotionless-method", [HISController::class, "emotionless_method"]);
        Route::get("v1/emotionless-method/{id}", [HISController::class, "emotionless_method"]);
    });

    /// Tai biến PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttCatastrophe'], function () {
        Route::get("v1/pttt-catastrophe", [HISController::class, "pttt_catastrophe"]);
        Route::get("v1/pttt-catastrophe/{id}", [HISController::class, "pttt_catastrophe"]);
    });

    /// Tình trạng PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttCondition'], function () {
        Route::get("v1/pttt-condition", [HISController::class, "pttt_condition"]);
        Route::get("v1/pttt-condition/{id}", [HISController::class, "pttt_condition"]);
    });

    /// Ý thức
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAwareness'], function () {
        Route::get("v1/awareness", [HISController::class, "awareness"]);
        Route::get("v1/awareness/{id}", [HISController::class, "awareness"]);
    });

    /// Dòng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineLine'], function () {
        Route::get("v1/medicine-line", [HISController::class, "medicine_line"]);
        Route::get("v1/medicine-line/{id}", [HISController::class, "medicine_line"]);
    });

    /// Dung tích túi máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodVolume'], function () {
        Route::get("v1/blood-volume", [HISController::class, "blood_volume"]);
        Route::get("v1/blood-volume/{id}", [HISController::class, "blood_volume"]);
    });

    /// Đường dùng thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineUseForm'], function () {
        Route::get("v1/medicine-use-form", [HISController::class, "medicine_use_form"]);
        Route::get("v1/medicine-use-form/{id}", [HISController::class, "medicine_use_form"]);
    });

    /// Loại thầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBidType'], function () {
        Route::get("v1/bid-type", [HISController::class, "bid_type"]);
        Route::get("v1/bid-type/{id}", [HISController::class, "bid_type"]);
    });

    /// Loại thuốc - Hoạt chất
    Route::group(['as' => 'HIS.Desktop.Plugins.MedicineTypeActiveIngredient'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/medicine-type-acin", [HISController::class, "medicine_type_acin"]);
        Route::get("v1/medicine-type-acin/{id}", [HISController::class, "medicine_type_acin"]);
        // Trả về tất cả loại thuốc cùng hoạt chất
        Route::get("v1/medicine-type/all/active-ingredient", [HISController::class, "medicine_type_with_active_ingredient"]);
        Route::get("v1/medicine-type/{id}/active-ingredient", [HISController::class, "medicine_type_with_active_ingredient"]);
        // Trả về tất cả hoạt chất cùng loại thuốc
        Route::get("v1/active-ingredient/all/medicine-type", [HISController::class, "active_ingredient_with_medicine_type"]);
        Route::get("v1/active-ingredient/{id}/medicine-type", [HISController::class, "active_ingredient_with_medicine_type"]);
    });

    /// Nhóm ATC
    Route::group(['as' => 'HIS.Desktop.Plugins.HisAtcGroup'], function () {
        Route::get("v1/atc-group", [HISController::class, "atc_group"]);
        Route::get("v1/atc-group/{id}", [HISController::class, "atc_group"]);
    });

    /// Nhóm máu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBloodGroup'], function () {
        Route::get("v1/blood-group", [HISController::class, "blood_group"]);
        Route::get("v1/blood-group/{id}", [HISController::class, "blood_group"]);
    });

    /// Nhóm thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMedicineGroup'], function () {
        Route::get("v1/medicine-group", [HISController::class, "medicine_group"]);
        Route::get("v1/medicine-group/{id}", [HISController::class, "medicine_group"]);
    });

    /// Chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndex'], function () {
        Route::get("v1/test-index", [HISController::class, "test_index"]);
        Route::get("v1/test-index/{id}", [HISController::class, "test_index"]);
    });

    /// Đơn vị tính chỉ số xét nghiệm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestIndexUnit'], function () {
        Route::get("v1/test-index-unit", [HISController::class, "test_index_unit"]);
        Route::get("v1/test-index-unit/{id}", [HISController::class, "test_index_unit"]);
    });

    /// Loại mẫu bệnh phẩm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisTestSampleType'], function () {
        Route::get("v1/test-sample-type", [TestSampleTypeController::class, "test_sample_type"]);
        Route::get("v1/test-sample-type/{id}", [TestSampleTypeController::class, "test_sample_type"]);
    });

    /// Nhân viên - Phòng
    // Trả về nhân viên cùng phòng
    Route::get("v1/user-room/get-view", [UserRoomController::class, "user_with_room"]);

    // Debate
    Route::get("v1/debate/get", [DebateController::class, "debate_get"]);
    Route::get("v1/debate/get-view", [DebateController::class, "debate_get_view"]);

    Route::get("v2/debate/get", [DebateController::class, "debate_get_v2"]);
    Route::get("v2/debate/get-view", [DebateController::class, "debate_get_view_v2"]);
    

    // Debate User
    Route::get("v1/debate-user/get", [DebateUserController::class, "debate_user"]);
    Route::get("v2/debate-user/get", [DebateUserController::class, "debate_user_v2"]);


    // Debate Ekip User
    Route::get("v1/debate-ekip-user/get", [DebateEkipUserController::class, "debate_ekip_user"]);
    Route::get("v2/debate-ekip-user/get", [DebateEkipUserController::class, "debate_ekip_user_v2"]);


    // Debate Type
    Route::get("v1/debate-type", [HISController::class, "debate_type"]);
    Route::get("v1/debate-type/{id}", [HISController::class, "debate_type"]);

    // Service Req
    Route::get("v1/service-req/get-L-view", [ServiceReqController::class, "service_req_get_L_view"]);
    Route::get("v2/service-req/get-L-view", [ServiceReqController::class, "service_req_get_L_view_v2"]);
    Route::get("v3/service-req/get-L-view", [ServiceReqController::class, "service_req_get_L_view_v3"]);



    // Tracking
    Route::get("v1/tracking/get", [TrackingController::class, "tracking_get"]);
    Route::get("v1/tracking/get-data", [TrackingController::class, "tracking_get_data"]);

    Route::get("v2/tracking/get", [TrackingController::class, "tracking_get_v2"]);
    Route::get("v2/tracking/get-data", [TrackingController::class, "tracking_get_data_v2"]);

    // Sere Serv
    Route::get("v1/sere-serv/get", [SereServController::class, "sere_serv_get"]);

    Route::get("v2/sere-serv/get", [SereServController::class, "sere_serv_get_v2"]);
    Route::get("v2/sere-serv/get-count", [SereServController::class, "sere_serv_get_count_v2"]);

    Route::get("v3/sere-serv/get", [SereServController::class, "sere_serv_get_v3"]);
    Route::get("v3/sere-serv/get-count", [SereServController::class, "sere_serv_get_count_v3"]);


    // Patient Type Alter
    Route::get("v1/patient-type-alter/get-view", [PatientTypeAlterController::class, "patient_type_alter_get_view"]);
    Route::get("v2/patient-type-alter/get-view", [PatientTypeAlterController::class, "patient_type_alter_get_view_v2"]);


    // Treatment
    Route::get("v1/treatment/get-L-view", [TreatmentController::class, "treatment_get_L_view"]);
    Route::get("v1/treatment/get-treatment-with-patient-type-info-sdo", [TreatmentController::class, "treatment_get_treatment_with_patient_type_info_sdo"]);
    Route::get("v1/treatment/get-fee-view", [TreatmentController::class, "treatment_get_fee_view"]);

    Route::get("v2/treatment/get-L-view", [TreatmentController::class, "treatment_get_L_view_v2"]);
    Route::get("v2/treatment/get-treatment-with-patient-type-info-sdo", [TreatmentController::class, "treatment_get_treatment_with_patient_type_info_sdo_v2"]);

    // Treatment Bed Room
    Route::get("v1/treatment-bed-room/get-L-view", [TreatmentBedRoomController::class, "treatment_bed_room_get_L_view"]);
    Route::get("v2/treatment-bed-room/get-L-view", [TreatmentBedRoomController::class, "treatment_bed_room_get_L_view_v2"]);


    // DHST
    Route::get("v1/dhst/get", [DhstController::class, "dhst_get"]);
    Route::get("v2/dhst/get", [DhstController::class, "dhst_get_v2"]);
    Route::get("v3/dhst/get", [DhstController::class, "dhst_get_v3"]);


    // Sere Serv Ext
    Route::get("v1/sere-serv-ext/get", [SereServExtController::class, "sere_serv_ext"]);
    Route::get("v2/sere-serv-ext/get", [SereServExtController::class, "sere_serv_ext_v2"]);

    // Sere Serv Tein
    Route::get("v1/sere-serv-tein/get", [SereServTeinController::class, "sere_serv_tein_get"]);
    Route::get("v1/sere-serv-tein/get-view", [SereServTeinController::class, "sere_serv_tein_get_view"]);

    Route::get("v2/sere-serv-tein/get", [SereServTeinController::class, "sere_serv_tein_get_v2"]);
    Route::get("v2/sere-serv-tein/get-view", [SereServTeinController::class, "sere_serv_tein_get_view_v2"]);

});
