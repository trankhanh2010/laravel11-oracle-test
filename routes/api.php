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
        Route::get("v1/department", [HISController::class, "department"]);
        Route::get("v1/department/{id}", [HISController::class, "department"]);
    });

    /// Buồng bệnh
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedRoomList'], function () {
        Route::get("v1/bed-room", [HISController::class, "bed_room"]);
        Route::get("v1/bed-room/{id}", [HISController::class, "bed_room"]);
    });

    /// Phòng khám/cls/pttt
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRoom'], function () {
        Route::get("v1/execute-room", [HISController::class, "execute_room"]);
        Route::get("v1/execute-room/{id}", [HISController::class, "execute_room"]);
    });

    /// Chuyên khoa
    Route::group(['as' => 'HIS.Desktop.Plugins.HisSpeciality'], function () {
        Route::get("v1/speciality", [HISController::class, "speciality"]);
        Route::get("v1/speciality/{id}", [HISController::class, "speciality_id"]);
    });

    /// Diện điều trị
    Route::get("v1/treatment-type", [HISController::class, "treatment_type"]);
    Route::get("v1/treatment-type/{id}", [HISController::class, "treatment_type"]);

    /// Cơ sở khám chữa bệnh ban đầu
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediOrg'], function () {
        Route::get("v1/medi-org", [HISController::class, "medi_org"]);
        Route::get("v1/medi-org/{id}", [HISController::class, "medi_org_id"]);
    });

    /// Cơ sở/Xã phường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBranch'], function () {
        Route::get("v1/branch", [HISController::class, "branch"]);
        Route::get("v1/branch/{id}", [HISController::class, "branch_id"]);
    });

    /// Huyện
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaDistrict'], function () {
        Route::get("v1/district", [HISController::class, "district"]);
        Route::get("v1/district/{id}", [HISController::class, "district_id"]);
    });

    /// Kho
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMediStock'], function () {
        Route::get("v1/medi-stock", [HISController::class, "medi_stock"]);
        Route::get("v1/medi-stock/{id}", [HISController::class, "medi_stock_id"]);
    });

    /// Khu đón tiếp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisReceptionRoom'], function () {
        Route::get("v1/reception-room", [HISController::class, "reception_room"]);
        Route::get("v1/reception-room/{id}", [HISController::class, "reception_room_id"]);
    });

    /// Khu vực
    Route::group(['as' => 'HIS.Desktop.Plugins.HisArea'], function () {
        Route::get("v1/area", [HISController::class, "area"]);
        Route::get("v1/area/{id}", [HISController::class, "area_id"]);
    });

    /// Nhà ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRefectory'], function () {
        Route::get("v1/refectory", [HISController::class, "refectory"]);
        Route::get("v1/refectory/{id}", [HISController::class, "refectory_id"]);
    });

    /// Nhóm thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteGroup'], function () {
        Route::get("v1/execute-group", [HISController::class, "execute_group"]);
        Route::get("v1/execute-group/{id}", [HISController::class, "execute_group_id"]);
    });

    /// Phòng thu ngân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCashierRoom'], function () {
        Route::get("v1/cashier-room", [HISController::class, "cashier_room"]);
        Route::get("v1/cashier-room/{id}", [HISController::class, "cashier_room"]);
    });

    /// Quốc gia
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaNational'], function () {
        Route::get("v1/national", [HISController::class, "national"]);
        Route::get("v1/national/{id}", [HISController::class, "national_id"]);
    });

    /// Tỉnh
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaProvince'], function () {
        Route::get("v1/province", [HISController::class, "province"]);
        Route::get("v1/province/{id}", [HISController::class, "province"]);
    });

    /// Tủ bệnh án
    Route::group(['as' => 'HIS.Desktop.Plugins.HisDataStore'], function () {
        Route::get("v1/data-store", [HISController::class, "data_store"]);
        Route::get("v1/data-store/{id}", [HISController::class, "data_store_id"]);
    });

    /// Vai trò thực hiện
    Route::group(['as' => 'HIS.Desktop.Plugins.HisExecuteRole'], function () {
        Route::get("v1/execute-role", [HISController::class, "execute_role"]);
        Route::get("v1/execute-role/{id}", [HISController::class, "execute_role"]);
    });

    /// Xã
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaCommune'], function () {
        Route::get("v1/commune", [HISController::class, "commune"]);
        Route::get("v1/commune/{id}", [HISController::class, "commune"]);
    });

    /// Dịch vụ kỹ thuật
    Route::group(['as' => 'HIS.Desktop.Plugins.HisService'], function () {
        Route::get("v1/service", [HISController::class, "service"]);
        Route::get("v1/service/{id}", [HISController::class, "service_id"]);
        Route::get("v1/service/by-code/{type_id}", [HISController::class, "service_by_code"]);
    });

    /// Chính sách dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServicePatyList'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-paty", [HISController::class, "service_paty"]);
        Route::get("v1/service-paty/{id}", [HISController::class, "service_paty_id"]);
        // Trả về tất cả dịch vụ cùng loại bệnh nhân
        Route::get("v1/service/all/patient-type", [HISController::class, "service_with_patient_type"]);
        Route::get("v1/service/{id}/patient-type", [HISController::class, "service_with_patient_type"]);
        // Trả về tất cả loại bệnh nhân cùng dịch vụ
        Route::get("v1/patient-type/all/service", [HISController::class, "patient_type_with_service"]);
        Route::get("v1/patient-type/{id}/service", [HISController::class, "patient_type_with_service"]);
    });

    /// Dịch vụ máy
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceMachine'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-machine", [HISController::class, "service_machine"]);
        Route::get("v1/service-machine/{id}", [HISController::class, "service_machine_id"]);
        // Trả về tất cả dịch vụ cùng máy
        Route::get("v1/service/all/machine", [HISController::class, "service_with_machine"]);
        Route::get("v1/service/{id}/machine", [HISController::class, "service_with_machine"]);
        // Trả về tất cả máy cùng dịch vụ
        Route::get("v1/machine/all/service", [HISController::class, "machine_with_service"]);
        Route::get("v1/machine/{id}/service", [HISController::class, "machine_with_service"]);
    });

    /// Máy / Máy cận lâm sàn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisMachine'], function () {
        Route::get("v1/machine", [HISController::class, "machine"]);
        Route::get("v1/machine/{id}", [HISController::class, "machine_id"]);
    });

    /// Dịch vụ phòng
    Route::group(['as' => 'HIS.Desktop.Plugins.RoomService'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-room", [HISController::class, "service_room"]);
        Route::get("v1/service-room/{id}", [HISController::class, "service_room"]);
        // Trả về tất cả dịch vụ cùng phòng
        Route::get("v1/service/all/room", [HISController::class, "service_with_room"]);
        Route::get("v1/service/{id}/room", [HISController::class, "service_with_room"]);
        // Trả về tất cả phòng cùng dịch vụ
        Route::get("v1/room/all/service", [HISController::class, "room_with_service"]);
        Route::get("v1/room/{id}/service", [HISController::class, "room_with_service"]);
    });

    /// Phòng
    Route::get("v1/room", [HISController::class, "room"]);
    Route::get("v1/room/{id}", [HISController::class, "room"]);

    /// Dịch vụ đi kèm
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceFollow'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/service-follow", [HISController::class, "service_follow"]);
        Route::get("v1/service-follow/{id}", [HISController::class, "service_follow_id"]);
        // Trả về tất cả dịch vụ cùng dịch vụ đi kèm
        Route::get("v1/service/all/follow", [HISController::class, "service_with_follow"]);
        Route::get("v1/service/{id}/follow", [HISController::class, "service_with_follow"]);
        // Trả về tất cả dịch vụ đi kèm cùng dịch vụ
        Route::get("v1/follow/all/service", [HISController::class, "follow_with_service"]);
        Route::get("v1/follow/{id}/service", [HISController::class, "follow_with_service"]);
    });

    /// Giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBed'], function () {
        Route::get("v1/bed", [HISController::class, "bed"]);
        Route::get("v1/bed/{id}", [HISController::class, "bed"]);
    });

    /// Giường - Dịch vụ giường
    Route::group(['as' => 'HIS.Desktop.Plugins.BedBsty'], function () {
        // Trả về tất cả mối quan hệ
        Route::get("v1/bed-bsty", [HISController::class, "bed_bsty"]);
        Route::get("v1/bed-bsty/{id}", [HISController::class, "bed_bsty_id"]);
        // Trả về tất cả dịch vụ cùng giường
        Route::get("v1/service/all/bed", [HISController::class, "service_with_bed"]);
        Route::get("v1/service/{id}/bed", [HISController::class, "service_with_bed"]);
        // Trả về tất cả giường cùng dịch vụ
        Route::get("v1/bed/all/service", [HISController::class, "bed_with_service"]);
        Route::get("v1/bed/{id}/service", [HISController::class, "bed_with_service"]);
    });

    /// Loại giường
    Route::group(['as' => 'HIS.Desktop.Plugins.HisBedTypeList'], function () {
        Route::get("v1/bed-type", [HISController::class, "bed_type"]);
        Route::get("v1/bed-type/{id}", [HISController::class, "bed_type"]);
    });

    /// Nhóm dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServSegr'], function () {
        Route::get("v1/serv-segr", [HISController::class, "serv_segr"]);
        Route::get("v1/serv-segr/{id}", [HISController::class, "serv_segr"]);
        Route::get("v1/service-group", [HISController::class, "service_group"]);
        Route::get("v1/service-group/{id}", [HISController::class, "service_group_id"]);
    });

    /// Tài khoản nhân viên
    Route::group(['as' => 'HIS.Desktop.Plugins.EmpUser'], function () {
        Route::get("v1/emp-user", [HISController::class, "emp_user"]);
        Route::get("v1/emp-user/{id}", [HISController::class, "emp_user_id"]);
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
        Route::get("v1/role", [HISController::class, "role"]);
        Route::get("v1/role/{id}", [HISController::class, "role"]);
    });

    /// Vai trò - Chức năng 
    Route::group(['as' => 'ACS.Desktop.Plugins.AcsModuleRole'], function () {
        Route::get("v1/module-role", [HISController::class, "module_role"]);
        Route::get("v1/module-role/{id}", [HISController::class, "module_role"]);
    });

    /// Dân tộc
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaEthnic'], function () {
        Route::get("v1/ethnic", [HISController::class, "ethnic"]);
        Route::get("v1/ethnic/{id}", [HISController::class, "ethnic_id"]);
    });

    /// Đối tượng bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientType'], function () {
        Route::get("v1/patient-type", [HISController::class, "patient_type"]);
        Route::get("v1/patient-type/{id}", [HISController::class, "patient_type_id"]);
    });

    /// Đối tượng ưu tiên
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPriorityType'], function () {
        Route::get("v1/priority-type", [HISController::class, "priority_type"]);
        Route::get("v1/priority-type/{id}", [HISController::class, "priority_type_id"]);
    });

    /// Mối quan hệ
    Route::group(['as' => 'HIS.Desktop.Plugins.EmrRelationList'], function () {
        Route::get("v1/relation-list", [HISController::class, "relation_list"]);
        Route::get("v1/relation-list/{id}", [HISController::class, "relation_list_id"]);
    });

    /// Nghề nghiệp
    Route::group(['as' => 'HIS.Desktop.Plugins.HisCareer'], function () {
        Route::get("v1/career", [HISController::class, "career"]);
        Route::get("v1/career/{id}", [HISController::class, "career_id"]);
    });

    /// Phân loại bệnh nhân
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPatientClassify'], function () {
        Route::get("v1/patient-classify", [HISController::class, "patient_classify"]);
        Route::get("v1/patient-classify/{id}", [HISController::class, "patient_classify"]);
    });

    /// Tôn giáo
    Route::group(['as' => 'SDA.Desktop.Plugins.SdaReligion'], function () {
        Route::get("v1/religion", [HISController::class, "religion"]);
        Route::get("v1/religion/{id}", [HISController::class, "religion_id"]);
    });

    /// Đơn vị tính
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceUnitEdit'], function () {
        Route::get("v1/service-unit", [HISController::class, "service_unit"]);
        Route::get("v1/service-unit/{id}", [HISController::class, "service_unit"]);
    });

    /// Loại dịch vụ
    Route::group(['as' => 'HIS.Desktop.Plugins.HisServiceType'], function () {
        Route::get("v1/service-type", [HISController::class, "service_type"]);
        Route::get("v1/service-type/{id}", [HISController::class, "service_type"]);
    });

    /// Nhóm xuất ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationGroup'], function () {
        Route::get("v1/ration-group", [HISController::class, "ration_group"]);
        Route::get("v1/ration-group/{id}", [HISController::class, "ration_group_id"]);
    });

    /// Loại y lệnh 
    Route::group(['as' => 'HIS.Desktop.Plugins.ServiceReqType'], function () {
        Route::get("v1/service-req-type", [HISController::class, "service_req_type"]);
        Route::get("v1/service-req-type/{id}", [HISController::class, "service_req_type_id"]);
    });

    /// Bữa ăn
    Route::group(['as' => 'HIS.Desktop.Plugins.HisRationTime'], function () {
        Route::get("v1/ration-time", [HISController::class, "ration_time"]);
        Route::get("v1/ration-time/{id}", [HISController::class, "ration_time_id"]);
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
        Route::get("v1/sale-profit-cfg", [HISController::class, "sale_profit_cfg"]);
        Route::get("v1/sale-profit-cfg/{id}", [HISController::class, "sale_profit_cfg"]);
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
        Route::get("v1/bhyt-whitelist", [HISController::class, "bhyt_whitelist"]);
        Route::get("v1/bhyt-whitelist/{id}", [HISController::class, "bhyt_whitelist"]);
    });

    /// Nhóm dịch vụ BHYT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisHeinServiceType'], function () {
        Route::get("v1/hein-service-type", [HISController::class, "hein_service_type"]);
        Route::get("v1/hein-service-type/{id}", [HISController::class, "hein_service_type"]);
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

    /// ICD
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
        Route::get("v1/pttt-group", [HISController::class, "pttt_group"]);
        Route::get("v1/pttt-group/{id}", [HISController::class, "pttt_group"]);
    });

    /// Phương pháp PTTT
    Route::group(['as' => 'HIS.Desktop.Plugins.HisPtttMethod'], function () {
        //Trả về tất cả nhóm pttt cùng nhóm dịch vụ 
        Route::get("v1/pttt-method", [HISController::class, "pttt_method"]);
        Route::get("v1/pttt-method/{id}", [HISController::class, "pttt_method"]);
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
        Route::get("v1/test-sample-type", [HISController::class, "test_sample_type"]);
        Route::get("v1/test-sample-type/{id}", [HISController::class, "test_sample_type"]);
    });

    /// Nhân viên - Phòng
    // Trả về nhân viên cùng phòng
    Route::get("v1/user-room", [HISController::class, "user_with_room"]);

    // Debate
    Route::get("v1/debate", [HISController::class, "debate"]);

    // Debate User
    Route::get("v1/debate-user", [HISController::class, "debate_user"]);

    // Debate Ekip User
    Route::get("v1/debate-ekip-user", [HISController::class, "debate_ekip_user"]);

    // Debate Type
    Route::get("v1/debate-type", [HISController::class, "debate_type"]);
    Route::get("v1/debate-type/{id}", [HISController::class, "debate_type"]);

    // Service Req
    Route::get("v1/service-req/get-L-view", [HISController::class, "service_req_get_L_view"]);

    // Tracking
    Route::get("v1/tracking/get", [HISController::class, "tracking"]);
    Route::get("v1/tracking/get-data", [HISController::class, "tracking_get_data"]);

    // Sere Serv
    Route::get("v1/sere-serv/get", [HISController::class, "sere_serv"]);

});
