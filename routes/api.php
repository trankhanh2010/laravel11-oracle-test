<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\HISController;
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

Route::group([
    "middleware" => ["check_module:api"]
], function () {

    /// Khoa phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDepartment'], function () {
        Route::get("department", [HISController::class, "department"]);
        Route::get("department/{id}", [HISController::class, "department_id"]);
    });

    /// Buồng bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedRoomList'], function () {
        Route::get("bed-room", [HISController::class, "bed_room"]);
        Route::get("bed-room/{id}", [HISController::class, "bed_room_id"]);
        Route::get('bed-room/{id}/room', [HISController::class, 'bed_room_get_room']);
        Route::get('bed-room/{id}/department', [HISController::class, 'bed_room_get_department']);
        Route::get('bed-room/{id}/area', [HISController::class, 'bed_room_get_area']);
    });

    /// Phòng khám/cls/pttt
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRoom'], function () {
        Route::get("execute-room", [HISController::class, "execute_room"]);
        Route::get("execute-room/{id}", [HISController::class, "execute_room_id"]);
        Route::get('execute-room/{id}/room', [HISController::class, 'execute_room_get_room']);
        Route::get('execute-room/{id}/department', [HISController::class, 'execute_room_get_department']);
    });

    /// Chuyên khoa
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSpeciality'], function () {
        Route::get("speciality", [HISController::class, "speciality"]);
        Route::get("speciality/{id}", [HISController::class, "speciality_id"]);
    });

    /// Diện điều trị
    Route::get("treatment-type", [HISController::class, "treatment_type"]);
    Route::get("treatment-type/{id}", [HISController::class, "treatment_type_id"]);

    /// Cơ sở khám chữa bệnh ban đầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediOrg'], function () {
        Route::get("medi-org", [HISController::class, "medi_org"]);
        Route::get("medi-org/{id}", [HISController::class, "medi_org_id"]);
    });

    /// Cơ sở/Xã phường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBranch'], function () {
        Route::get("branch", [HISController::class, "branch"]);
        Route::get("branch/{id}", [HISController::class, "branch_id"]);
    });

    /// Huyện
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaDistrict'], function () {
        Route::get("district", [HISController::class, "district"]);
        Route::get("district/{id}", [HISController::class, "district_id"]);
    });

    /// Kho
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediStock'], function () {
        Route::get("medi-stock", [HISController::class, "medi_stock"]);
        Route::get("medi-stock/{id}", [HISController::class, "medi_stock_id"]);
        Route::get("medi-stock/{id}/room", [HISController::class, "medi_stock_get_room"]);
        Route::get("medi-stock/{id}/room-type", [HISController::class, "medi_stock_get_room_type"]);
        Route::get("medi-stock/{id}/department", [HISController::class, "medi_stock_get_department"]);
    });

    /// Khu đón tiếp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisReceptionRoom'], function () {
        Route::get("reception-room", [HISController::class, "reception_room"]);
        Route::get("reception-room/{id}", [HISController::class, "reception_room_id"]);
        Route::get("reception-room/{id}/department", [HISController::class, "reception_room_get_department"]);
    });

    /// Khu vực
    Route::group(['as' => 'HIS.Desktop.Plugins.HisArea'], function () {
        Route::get("area", [HISController::class, "area"]);
        Route::get("area/{id}", [HISController::class, "area_id"]);
    });

    /// Nhà ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRefectory'], function () {
        Route::get("refectory", [HISController::class, "refectory"]);
        Route::get("refectory/{id}", [HISController::class, "refectory_id"]);
        Route::get("refectory/{id}/department", [HISController::class, "refectory_get_department"]);
    });

    /// Nhóm thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteGroup'], function () {
        Route::get("execute-group", [HISController::class, "execute_group"]);
        Route::get("execute-group/{id}", [HISController::class, "execute_group_id"]);
    });

    /// Phòng thu ngân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCashierRoom'], function () {
        Route::get("cashier-room", [HISController::class, "cashier_room"]);
        Route::get("cashier-room/{id}", [HISController::class, "cashier_room_id"]);
        Route::get("cashier-room/{id}/room-type", [HISController::class, "cashier_room_get_room_type"]);
        Route::get("cashier-room/{id}/department", [HISController::class, "cashier_room_get_department"]);
        Route::get("cashier-room/{id}/area", [HISController::class, "cashier_room_get_area"]);
    });

    /// Quốc gia
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaNational'], function () {
        Route::get("national", [HISController::class, "national"]);
        Route::get("national/{id}", [HISController::class, "national_id"]);
    });

    /// Tỉnh
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaProvince'], function () {
        Route::get("province", [HISController::class, "province"]);
        Route::get("province/{id}", [HISController::class, "province_id"]);
    });

    /// Tủ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDataStore'], function () {
        Route::get("data-store", [HISController::class, "data_store"]);
        Route::get("data-store/{id}", [HISController::class, "data_store_id"]);
        Route::get("data-store/{id}/department-room", [HISController::class, "data_store_get_department_room"]);
        Route::get("data-store/{id}/department", [HISController::class, "data_store_get_department"]);
    });

    /// Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRole'], function () {
        Route::get("execute-role", [HISController::class, "execute_role"]);
        Route::get("execute-role/{id}", [HISController::class, "execute_role_id"]);
    });

    /// Xã
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaCommune'], function () {
        Route::get("commune", [HISController::class, "commune"]);
        Route::get("commune/{id}", [HISController::class, "commune_id"]);
    });

    /// Dịch vụ kỹ thuật
    Route::group(['as' => 'HIS.Desktop.Plugins.HisService'], function () {
        Route::get("service", [HISController::class, "service"]);
        Route::get("service/{id}", [HISController::class, "service_id"]);
        Route::get("service/by-code/{type_id}", [HISController::class, "service_by_code"]);
    });

    /// Chính sách dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServicePatyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("service-paty", [HISController::class, "service_paty"]);
        Route::get("service-paty/{id}", [HISController::class, "service_paty_id"]);
        // Trả về tất cả dịch vụ cùng loại bệnh nhân
        Route::get("service/all/patient-type", [HISController::class, "service_with_patient_type"]);
        Route::get("service/{id}/patient-type", [HISController::class, "service_with_patient_type"]);
        // Trả về tất cả loại bệnh nhân cùng dịch vụ
        Route::get("patient-type/all/service", [HISController::class, "patient_type_with_service"]);
        Route::get("patient-type/{id}/service", [HISController::class, "patient_type_with_service"]);
    });

    /// Dịch vụ máy
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceMachine'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("service-machine", [HISController::class, "service_machine"]);
        Route::get("service-machine/{id}", [HISController::class, "service_machine_id"]);
        // Trả về tất cả dịch vụ cùng máy
        Route::get("service/all/machine", [HISController::class, "service_with_machine"]);
        Route::get("service/{id}/machine", [HISController::class, "service_with_machine"]);
        // Trả về tất cả máy cùng dịch vụ
        Route::get("machine/all/service", [HISController::class, "machine_with_service"]);
        Route::get("machine/{id}/service", [HISController::class, "machine_with_service"]);
    });

    /// Máy / Máy cận lâm sàn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMachine'], function () {
        Route::get("machine", [HISController::class, "machine"]);
        Route::get("machine/{id}", [HISController::class, "machine_id"]);
    });

    /// Dịch vụ phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomService'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("service-room", [HISController::class, "service_room"]);
        Route::get("service-room/{id}", [HISController::class, "service_room_id"]);
        // Trả về tất cả dịch vụ cùng phòng
        Route::get("service/all/room", [HISController::class, "service_with_room"]);
        Route::get("service/{id}/room", [HISController::class, "service_with_room"]);
        // Trả về tất cả phòng cùng dịch vụ
        Route::get("room/all/service", [HISController::class, "room_with_service"]);
        Route::get("room/{id}/service", [HISController::class, "room_with_service"]);
    });

    /// Phòng
    Route::get("room", [HISController::class, "room"]);
    Route::get("room/{id}", [HISController::class, "room_id"]);

    /// Dịch vụ đi kèm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceFollow'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("service-follow", [HISController::class, "service_follow"]);
        Route::get("service-follow/{id}", [HISController::class, "service_follow_id"]);
        // Trả về tất cả dịch vụ cùng dịch vụ đi kèm
        Route::get("service/all/follow", [HISController::class, "service_with_follow"]);
        Route::get("service/{id}/follow", [HISController::class, "service_with_follow"]);
        // Trả về tất cả dịch vụ đi kèm cùng dịch vụ
        Route::get("follow/all/service", [HISController::class, "follow_with_service"]);
        Route::get("follow/{id}/service", [HISController::class, "follow_with_service"]);
    });

    /// Giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBed'], function () {
        Route::get("bed", [HISController::class, "bed"]);
        Route::get("bed/{id}", [HISController::class, "bed_id"]);
    });

    /// Giường - Dịch vụ giường
    Route::group(['as' => 'HIS.Desktop.Plugins.BedBsty'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("bed-bsty", [HISController::class, "bed_bsty"]);
        Route::get("bed-bsty/{id}", [HISController::class, "bed_bsty_id"]);
        // Trả về tất cả dịch vụ cùng giường
        Route::get("service/all/bed", [HISController::class, "service_with_bed"]);
        Route::get("service/{id}/bed", [HISController::class, "service_with_bed"]);
        // Trả về tất cả giường cùng dịch vụ
        Route::get("bed/all/service", [HISController::class, "bed_with_service"]);
        Route::get("bed/{id}/service", [HISController::class, "bed_with_service"]);
    });

    /// Loại giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedTypeList'], function () {
        Route::get("bed-type", [HISController::class, "bed_type"]);
        Route::get("bed-type/{id}", [HISController::class, "bed_type_id"]);
    });

    /// Nhóm dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServSegr'], function () {
        Route::get("serv-segr", [HISController::class, "serv_segr"]);
        Route::get("serv-segr/{id}", [HISController::class, "serv_segr_id"]);
        Route::get("service-group", [HISController::class, "service_group"]);
        Route::get("service-group/{id}", [HISController::class, "service_group_id"]);
    });

    /// Tài khoản nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.EmpUser'], function () {
        Route::get("emp-user", [HISController::class, "emp_user"]);
        Route::get("emp-user/{id}", [HISController::class, "emp_user_id"]);
    });

    /// Thông tin tài khoản
    Route::group(['as' => 'HIS.Desktop.Plugins.InfoUser'], function () {
        Route::get("info-user/{id}", [HISController::class, "info_user_id"]);
    });

    /// Tài khoản - Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.ExecuteRoleUser'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("execute-role-user", [HISController::class, "execute_role_user"]);
        Route::get("execute-role-user/{id}", [HISController::class, "execute_role_user_id"]);
        // Trả về tất cả tài khoản cùng vai trò thực hiện
        Route::get("user/all/execute-role", [HISController::class, "user_with_execute_role"]);
        Route::get("user/{id}/execute-role", [HISController::class, "user_with_execute_role"]);
        // Trả về tất cả vai trò thực hiện cùng tài khoản
        Route::get("execute-role/all/user", [HISController::class, "execute_role_with_user"]);
        Route::get("execute-role/{id}/user", [HISController::class, "execute_role_with_user"]);
    });

    /// Vai trò
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsRole'], function () {
        Route::get("role", [HISController::class, "role"]);
        Route::get("role/{id}", [HISController::class, "role_id"]);
    });

    /// Vai trò - Chức năng 
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModuleRole'], function () {
        Route::get("module-role", [HISController::class, "module_role"]);
        Route::get("module-role/{id}", [HISController::class, "module_role_id"]);
    });

    /// Dân tộc
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaEthnic'], function () {
        Route::get("ethnic", [HISController::class, "ethnic"]);
        Route::get("ethnic/{id}", [HISController::class, "ethnic_id"]);
    });

    /// Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientType'], function () {
        Route::get("patient-type", [HISController::class, "patient_type"]);
        Route::get("patient-type/{id}", [HISController::class, "patient_type_id"]);
    });

    /// Đối tượng ưu tiên
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPriorityType'], function () {
        Route::get("priority-type", [HISController::class, "priority_type"]);
        Route::get("priority-type/{id}", [HISController::class, "priority_type_id"]);
    });

    /// Mối quan hệ
    Route::group(['as' => 'HIS.Desktop.Plugins.EmrRelationList'], function () {
        Route::get("relation-list", [HISController::class, "relation_list"]);
        Route::get("relation-list/{id}", [HISController::class, "relation_list_id"]);
    });

    /// Nghề nghiệp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCareer'], function () {
        Route::get("career", [HISController::class, "career"]);
        Route::get("career/{id}", [HISController::class, "career_id"]);
    });

    /// Phân loại bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientClassify'], function () {
        Route::get("patient-classify", [HISController::class, "patient_classify"]);
        Route::get("patient-classify/{id}", [HISController::class, "patient_classify_id"]);
    });

    /// Tôn giáo
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaReligion'], function () {
        Route::get("religion", [HISController::class, "religion"]);
        Route::get("religion/{id}", [HISController::class, "religion_id"]);
    });

    /// Đơn vị tính
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceUnitEdit'], function () {
        Route::get("service-unit", [HISController::class, "service_unit"]);
        Route::get("service-unit/{id}", [HISController::class, "service_unit_id"]);
    });

    /// Loại dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceType'], function () {
        Route::get("service-type", [HISController::class, "service_type"]);
        Route::get("service-type/{id}", [HISController::class, "service_type_id"]);
    });

    /// Nhóm xuất ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationGroup'], function () {
        Route::get("ration-group", [HISController::class, "ration_group"]);
        Route::get("ration-group/{id}", [HISController::class, "ration_group_id"]);
    });

    /// Loại y lệnh 
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqType'], function () {
        Route::get("service-req-type", [HISController::class, "service_req_type"]);
        Route::get("service-req-type/{id}", [HISController::class, "service_req_type_id"]);
    });

    /// Bữa ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationTime'], function () {
        Route::get("ration-time", [HISController::class, "ration_time"]);
        Route::get("ration-time/{id}", [HISController::class, "ration_time_id"]);
    });

    /// Kho - Đối tượng
    Route::group(['as' => 'HIS.Desktop.Plugins.MestPatientType'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("mest-patient-type", [HISController::class, "mest_patient_type"]);
        Route::get("mest-patient-type/{id}", [HISController::class, "mest_patient_type_id"]);
        // Trả về tất cả kho cùng đối tượng
        Route::get("medi-stock/all/patient-type", [HISController::class, "medi_stock_with_patient_type"]);
        Route::get("medi-stock/{id}/patient-type", [HISController::class, "medi_stock_with_patient_type"]);
        // Trả về tất cả đối tượng cùng kho
        Route::get("patient-type/all/medi-stock", [HISController::class, "patient_type_with_medi_stock"]);
        Route::get("patient-type/{id}/medi-stock", [HISController::class, "patient_type_with_medi_stock"]);
    });

    /// Kho - Loại thuốc
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMetyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("medi-stock-mety-list", [HISController::class, "medi_stock_mety_list"]);
        Route::get("medi-stock-mety-list/{id}", [HISController::class, "medi_stock_mety_list_id"]);
        // Trả về tất cả kho cùng loại thuốc 
        Route::get("medi-stock/all/medicine-type", [HISController::class, "medi_stock_with_medicine_type"]);
        Route::get("medi-stock/{id}/medicine-type", [HISController::class, "medi_stock_with_medicine_type"]);
        // Trả về tất cả loại thuốc cùng kho
        Route::get("medicine-type/all/medi-stock", [HISController::class, "medicine_type_with_medi_stock"]);
        Route::get("medicine-type/{id}/medi-stock", [HISController::class, "medicine_type_with_medi_stock"]);
    });

    /// Kho - Loại vật tư
    Route::group(['as' => 'HIS.Desktop.Plugins.MediStockMatyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("medi-stock-maty-list", [HISController::class, "medi_stock_maty_list"]);
        Route::get("medi-stock-maty-list/{id}", [HISController::class, "medi_stock_maty_list_id"]);
        // Trả về tất cả kho cùng loại vật tư 
        Route::get("medi-stock/all/material-type", [HISController::class, "medi_stock_with_material_type"]);
        Route::get("medi-stock/{id}/material-type", [HISController::class, "medi_stock_with_material_type"]);
        // Trả về tất cả loại vật tư cùng kho
        Route::get("material-type/all/medi-stock", [HISController::class, "material_type_with_medi_stock"]);
        Route::get("material-type/{id}/medi-stock", [HISController::class, "material_type_with_medi_stock"]);
    });
});
