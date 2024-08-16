<?php

return [
    // Lỗi
    'error' => [
        'required' => ' không được bỏ trống!',
        'string' => ' phải là chuỗi tring!',
        'string_max' => ' có số kí tự tối đa là :max!',
        'unique' => ' = :input đã tồn tại!',
        'exists' => ' = :input không tồn tại hoặc đang bị tạm khóa!',
        'integer' => ' phải là số nguyên!',
        'integer_min' => ' phải lớn hơn hoặc bằng :min!',
        'integer_max' => ' phải bé hơn hoặc bằng :max!',
        'in' => ' chỉ chấp nhận các giá trị :values',
        'not_in' => ' không thể nhận giá trị :values',
        'email' => ' không đúng định dạng email!',
        'regex_19_4' => ' chỉ chấp nhận số có tối đa 19 chữ số trong đó có tối đa 4 chữ số thập phân!',
        'regex_19_2' => ' chỉ chấp nhận số có tối đa 19 chữ số trong đó có tối đa 2 chữ số thập phân!',
        'regex_phone' => ' không đúng định dạng!',
        'regex_ymdhis' => ' phải được định dạng yyyymmddhhiiss và chuyển sang số nguyên có 14 chữ số!',
        'regex_hhmm' => ' phải được định dạng hhmm trong đó hh có giá trị từ 01 đến 23, mm có giá trị 00, 15 ,30 hoặc 45!',
        'regex_year' => ' phải có đủ 4 chữ số!',
        'numeric' => ' phải là số!',
        'prohibited_unless_service_type' => ' chỉ được nhập khi mã loại dịch vụ thuộc ',
        'declined_if'=> ' chỉ được nhập khi ',
        'lte' => ' phải bé hơn hoặc bằng :value!',
        'gte' => ' phải lớn hơn hoặc bằng :value!',
        'lt' => ' phải bé hơn :value!',
        'gt' => ' phải lớn hơn :value!',

        /// Error logic db
        'not_in_service_type_id' => ' Hoặc không thuộc nhóm dịch vụ đã chọn!',
        'not_in_service_id' => ' Hoặc không thuộc dịch vụ đã chọn!',
        'not_in_loginname' => ' Hoặc không khớp với loginname',
        'not_in_module_group_mhc' => ' Hoặc không phải là link màn hình chờ!',
        'not_active' => ' Hoặc bị tạm khóa!',
        'not_find_or_not_active_in_list' => ' trong danh sách không tồn tại hoặc đang bị tạm khóa!',
        'parent_not_in_id' => 'Id cha không được trùng với Id hiện tại!',
        'not_in_department_id' => ' không thuộc khoa đã chọn!',
        'not_in_room_type_XL' => ' không thuộc kiểu phòng khám/cls/pttt!',
        'not_in_service_type_GI' => ' không thuộc loại dịch vụ giường!',

        /// Error validate param
        'format' => 'Kiểu dữ liệu không hợp lệ!',
        'order_by_name' => 'Tên trường cần sắp xếp không hợp lệ!',
        'record_id' => 'Id không hợp lệ!',
        'decode_param' => 'Tham số param không hợp lệ!'
    ],
    // Dùng chung
    'all' => [
        'is_active' => 'Trường trạng thái',
    ],
    // Khoa phòng - Department
    'department' => [
        'department' => 'Khoa phòng',
        'department_id' => 'Id khoa phòng',
        'department_name' => 'Tên khoa phòng',
        'department_code' => 'Mã khoa phòng',
        'default_instr_patient_type_id' => 'Id đối tượng thanh toán mặc định khi chỉ định dịch vụ CLS',
        'theory_patient_count' => 'Số giường kế hoạch',
        'reality_patient_count' => 'Số giường thực tế',
        'req_surg_treatment_type_id' => 'Id diện điều trị được dùng khi tính công phẫu thuật thủ thuật đối với khoa chỉ định dịch vụ',
        'phone' => 'Số điện thoại',
        'head_loginname' => 'Loginname của trưởng khoa',
        'head_username' => 'Username của trưởng khoa',
        'is_exam' => 'Trường là khoa khám bệnh',
        'is_clinical' => 'Trường là khoa lâm sàng',
        'allow_assign_package_price' => 'Trường cho phép nhập giá gói lúc chỉ định gói',
        'auto_bed_assign_option' => 'Trường tự động cảnh báo và cho phép chỉ định giường, dịch vụ giường khi chuyển khoa, kết thúc điều trị',
        'is_emergency' => 'Trường là khoa cấp cứu',
        'is_auto_receive_patient' => 'Trường tự động tiếp nhận bệnh nhân vào khoa',
        'allow_assign_surgery_price' => 'Trường cho phép nhập giá lúc chỉ định phẫu thuật',
        'is_in_dep_stock_moba' => 'Trường mặc định chọn kho thu hồi là kho thuộc khoa',
        'warning_when_is_no_surg' => 'Trường cảnh báo khi chưa chỉ định dịch vụ phẫu thuật',
        'allow_treatment_type_ids' => 'Danh sách id diện điều trị',
        'accepted_icd_codes' => 'Danh sách icd code diện điều trị',
        'g_code' => 'Mã đơn vị',
        'bhyt_code' => 'Mã BHYT',
        'branch_id' => 'Id chi nhánh',
        'num_order' => 'Trường sắp xếp',
    ],
    // Khu vực - Area
    'area' => [
        'department_id' => 'Id khoa phòng',
        'area_name' => 'Tên khu vực',
        'area_code' => 'Mã khu vực',
    ],
    // Buồng bệnh - Bed Room
    'bed_room' => [
        'bed_room_code' => 'Mã buồng bệnh',
        'bed_room_name' => 'Tên buồng bệnh',
        'department_id' => 'Id khoa phòng',
        'area_id' => 'Id khu vực',
        'speciality_id' => 'Id chuyên khoa',
        'treatment_type_ids' => 'Danh sách diện điều trị',
        'default_cashier_room_id' => 'Id phòng thu ngân',
        'default_instr_patient_type_id' => 'Id đối tượng thanh toán mặc định khi chỉ định dịch vụ CLS',
        'is_surgery' => 'Trường là buồng phẫu thuật',
        'is_restrict_req_service' => 'Trường giới hạn dịch vụ chỉ định',
        'is_pause' => 'Trường tạm dừng',
        'is_restrict_execute_room' => 'Trường kiểm soát sử dụng phòng xử lý theo dịch vụ',
        'room_type_id' => 'Id loại phòng',
    ],
    // Phòng khám
    'execute_room' => [
        'execute_room_code' => 'Mã phòng khám',
        'execute_room_name' => 'Tên phòng khám',
        'department_id' => 'Id khoa phòng',
        'room_group_id' => 'Id nhóm phòng',
        'room_type_id' => 'Id loại phòng',
        'order_issue_code' => 'Mã sinh STT',
        'num_order' => 'STT',
        'test_type_code' => 'Mã loại xét nghiệm',
        'max_request_by_day' => 'Số lượt xử lý tối đa / ngày',
        'max_appointment_by_day' => 'Số lượt hẹn khám tối đa / ngày',
        'hold_order' => 'STT ưu tiên',
        'speciality_id' => 'Id chuyên khoa',
        'address' => 'Địa chỉ',
        'max_req_bhyt_by_day' => 'Số lượt BHYT thực hiện tối đa / ngày',
        'max_patient_by_day' => 'Số bệnh nhân xử lý tối đa / ngày',
        'average_eta' => 'Thời gian trung bình thực hiện một yêu cầu',
        'responsible_loginname' => 'Loginname bác sĩ phụ trách phòng',
        'responsible_username' => 'Username bác sĩ phụ trách phòng',
        'default_instr_patient_type_id' => 'Id đối tượng thanh toán mặc định',
        'default_drug_store_ids' => 'Danh sách id nhà thuốc mặc định',
        'default_cashier_room_id' => 'Id phòng thu ngân mặc định',
        'area_id' => 'Id khu vực',
        'screen_saver_module_link' => 'Link màn hình chờ',
        'bhyt_code' => 'Mã BHYT',
        'deposit_account_book_id' => 'Id sổ tạm ứng',
        'bill_account_book_id' => 'Id sổ thanh toán',
        'is_emergency' => 'Trường là phòng cấp cứu',
        'is_exam' => 'Trường là phòng khám',
        'is_speciality' => 'Trường là phòng chuyên khoa',
        'is_use_kiosk' => 'Trường là phòng KIOSK',
        'is_restrict_execute_room' => 'Trường giới hạn chỉ định phòng thực hiện',
        'is_restrict_time' => 'Trường giới hạn thời gian hoạt động',
        'is_vaccine' => 'Trường là phòng khám sàng lọc tiêm chủng',
        'is_restrict_req_service' => 'Trường kiểm soát dịch vụ chỉ định',
        'allow_not_choose_service' => 'Trường không bắt buộc chọn dịch vụ khi chỉ định công khám',
        'is_kidney' => 'Trường là phòng chạy thận',
        'kidney_shift_count' => 'Số ca chạy thận trong ngày',
        'is_surgery' => 'Trường là phòng mổ',
        'is_auto_expend_add_exam' => 'Tự động kiểm tra hao phí đối với các chỉ định khám thêm tại phòng này',
        'is_allow_no_icd' => 'Trường không cho phép nhập ICD',
        'is_pause' => 'Trường tạm dừng',
        'is_restrict_medicine_type' => 'Trường giới hạn sử dụng thuốc',
        'is_pause_enclitic' => 'Trường tạm ngừng tiếp nhận thêm yêu cầu xử lý',
        'is_vitamin_a' => 'Trường là phòng cho uống Vitamin A',
        'is_restrict_patient_type' => 'Trường giới hạn đối tượng bệnh nhân',
        'is_block_num_order' => 'Trường cấp số thứ tự theo khung thời gian',
        'default_service_id' => 'Id dịch vụ mặc định',
    ],
    // Nhóm phòng
    'room_group' => [
        'group_code' => 'Mã đơn vị',
        'room_group_name' => 'Tên nhóm phòng',
        'room_group_code' => 'Mã nhóm phòng',
    ],
    // Chuyên khoa
    'speciality' => [
        'speciality_code' => 'Mã chuyên khoa',
        'speciality_name' => 'Tên chuyên khoa',
        'bhyt_limit' => 'Giới hạn chi phí BHYT chi trả theo chuyên khoa'
    ],
    // Diện điều trị
    'treatment_type' => [
        'treatment_type_code' => 'Mã điều trị',
        'treatment_type_name' => 'Tên điều trị',
        'hein_treatment_type_code' => 'Mã diện điều trị theo BHYT',
        'end_code_prefix' => 'Tiền tố sinh sổ ra viện',
        'required_service_id' => 'Id dịch vụ bắt buộc khi chỉ định ra viện',
        'is_allow_reception' => 'Trường cho phép tiếp đón',
        'is_not_allow_unpause' => 'Trường chặn mở sau khi kết thúc điều trị',
        'allow_hospitalize_when_pres' => 'Trường cho phép nhập viện khi có đơn',
        'is_not_allow_share_bed' => 'Trường không cho phép nằm ghép giường',
        'is_required_service_bed' => 'Trường bắt buộc chỉ định giường',
        'is_dis_service_repay' => 'Trường không cho phép hoàn ứng dịch vụ ở chức năng viện phí',
        'dis_service_deposit_option' => 'Trường không cho phép tạm ứng dịch vụ',
        'dis_deposit_option' => 'Trường không cho phép tạm ứng',
        'unsign_doc_finish_option' => 'Trường tùy chọn khi kết thúc điều trị nếu có văn bản chưa ký hết',
        'trans_time_out_time_option' => 'Trường tùy chọn khi thời gian thanh toán < thời gian ra viện',
        'fee_debt_option' => 'Trường kết thúc điều trị khi nợ viện phí'
    ],
    // Cơ sở khám chứa bệnh ban đầu
    'medi_org' => [
        'medi_org_code' => 'Mã bệnh viện',
        'medi_org_name' => 'Tên bệnh viện',
        'province_code' => 'Mã',
        'province_name' => 'Tên',
        'district_code' => 'Mã',
        'district_name' => 'Tên',
        'commune_code' => 'Mã',
        'commune_name' => 'Tên',
        'address' => 'Địa chỉ',
        'rank_code' => 'Hạng bệnh viện',
        'level_code' => 'Tuyến bệnh viện',
    ],
    // Cơ sở/ Xã phường - Chi nhánh
    'branch' => [
        'branch_code' => 'Mã cơ sở',
        'branch_name' => 'Tên cơ sở',
        'hein_medi_org_code' => 'Mã theo đăng ký BHYT',
        'accept_hein_medi_org_code' => 'Danh sách mã đăng ký khám chữa bệnh ban đầu tuyến dưới',
        'sys_medi_org_code' => 'Danh sách mã đăng ký khám chữa bệnh ban đầu cùng hệ thống',
        'province_code' => 'Mã',
        'province_name' => 'Tên',
        'district_code' => 'Mã',
        'district_name' => 'Tên',
        'commune_code' => 'Mã',
        'commune_name' => 'Tên',
        'address' => 'Địa chỉ',
        'parent_organization_name' => 'Tên đơn vị trực thuộc',
        'hein_province_code' => 'Mã tỉnh theo qui định của BHYT',
        'hein_level_code' => 'Tuyến',
        'do_not_allow_hein_level_code' => 'Tuyến của nơi đăng ký khám chữa bệnh ban đầu không được hưởng BHYT',
        'tax_code' => 'Mã số thuế',
        'account_number' => 'Tài khoản ngân hàng',
        'phone' => 'Số điện thoại',
        'representative' => 'Người đại diện',
        'position' => 'Chức vụ người đại diện',
        'representative_hein_code' => 'Mã số định danh y tế của người đại diện',
        'auth_letter_issue_date' => 'Ngày cấp giấy ủy quyền',
        'auth_letter_num' => 'Trường giấy ủy quyền số',
        'bank_info' => 'Trường thông tin ngân hàng',
        'the_branch_code' => 'Mã cơ sở/ chi nhánh hệ thống thẻ',
        'director_loginname' => 'Loginname giám đốc',
        'director_username' => 'Username giám đốc',
        'venture' => 'Trường liên doanh, liên kết',
        'type' => 'Trường loại hình',
        'form' => 'Trường hình thức tổ chức',
        'bed_approved' => 'Số giường phê duyệt',
        'bed_actual' => 'Số giường thực kê',
        'bed_resuscitation' => 'Số giường hồi sức tích cực',
        'bed_resuscitation_emg' => 'Số giường hồi sức cấp cứu',
        'is_use_branch_time' => 'Trường sử dụng giờ hành chính'
    ],
    // Huyện
    'district' => [
        'district_code' => 'Mã',
        'district_name' => 'Tên',
        'initial_name' => 'Tên đơn vị',
        'search_code' => 'Tên viết tắt',
        'province_id' => 'Id tỉnh / thành phố',
    ],
    // Phân loại bệnh nhân
    'patient_classify' => [
        'patient_classify_code' => 'Mã phân loại',
        'patient_classify_name' => 'Tên phân loại',
        'display_color' => 'Giá trị màu',
        'patient_type_id' => 'Id đối tượng bệnh nhân',
        'other_pay_source_id' => 'Id nguồn chi trả khác',
        'bhyt_whitelist_ids' => 'Danh sách id đầu mã thẻ BHYT',
        'military_rank_ids' => 'Danh sách id quân hàm',
        'is_police' => 'Trường là công an'
    ],
    // Nguồn chi trả khác
    'other_pay_source' => [
        'other_pay_source_code' => 'Mã nguồn chi trả khác',
        'other_pay_source_name' => 'Tên nguồn chi trả khác',
        'hein_pay_source_type_id' => 'Id loại ngân sách',
        'is_not_for_treatment' => 'Trường không gán cho hồ sơ',
        'is_not_paid_diff' => 'Trường không chi trả tiền chênh lệch',
        'is_paid_all' => 'Trường chi trả toàn bộ chi phí'
    ],
    // Kho
    'medi_stock' => [
        'medi_stock_code' => 'Mã kho',
        'medi_stock_name' => 'Tên kho',
        'department_id' => 'Id khoa phòng',
        'room_type_id' => 'Id loại phòng',
        'bhyt_head_code' => 'Đầu mã thẻ BHYT',
        'not_in_bhyt_head_code' => 'Đầu mã thẻ BHYT không cho phép',
        'parent_id' => 'Id kho cha',
        'is_allow_imp_supplier' => 'Trường cho phép nhập từ NCC',
        'do_not_imp_medicine' => "Trường không cho phép nhập thuốc ở chức năng 'nhập thuốc vật tư'",
        'do_not_imp_material' => "Trường không cho phép nhập vật tư ở chức năng 'nhập thuốc vật tư'",
        'is_odd' => 'Trường là kho thuốc lẻ',
        'is_blood' => 'Trường là kho máu',
        'is_show_ddt' => 'Trường cho phép hiển thị đơn điều trị ở danh sách xuất',
        'is_planning_trans_as_default' => 'Trường sử dụng hình thức xuất kế hoạch là hình thức xuất mặc định khi xuất chuyển kho',
        'is_auto_create_chms_imp' => 'Trường tự động tạo yêu nhập chuyển kho',
        'is_auto_create_reusable_imp' => 'Trường tự động tạo yêu cầu nhập vật tư tái sử dụng sau khi thực xuất',
        'is_goods_restrict' => 'Trường quản lí hạn chế các loại thuốc, vật tư được lưu trữ',
        'is_show_inpatient_return_pres' => 'Trường hiển thị đơn nội trú trả lại (đơn tủ trực trả lại, điều trị trả lại',
        'is_moba_change_amount' => 'Trường cho phép sửa số lượng khi duyệt phiếu trả',
        'is_for_rejected_moba' => 'Trường là kho thuốc/ vật tư bị từ chối duyệt phiếu trả',
        'is_show_anticipate' => 'Trường cho phép tiếp nhận phiếu dự trù',
        'is_cabinet' => 'Trường là tủ trực',
        'is_new_medicine' => 'Trường là kho tân dược',
        'is_traditional_medicine' => 'Trường là kho y học cổ truyền',
        'is_drug_store' => 'Trường là quầy thuốc',
        'is_show_drug_store' => 'Trường chỉ hiển thị danh mục thuốc, vật tư quầy thuốc',
        'is_business' => 'Trường là kho kinh doanh (nhà thuốc)',
        'is_expend' => 'Trường là kho hao phí',
        'patient_classify_ids' => 'Trường danh sách id các phân loại bệnh nhân mà kho cho phép kê thuốc',
        'cabinet_manage_option' => 'Trường quản lí cơ số tủ trực',
        'medi_stock_exty' => 'Trường chuỗi json danh sách id cấu hình xuất',
        'medi_stock_imty' => 'Trường chuỗi json danh sách id cấu hình nhập',
        'is_auto_approve' => 'Trường tự động duyệt',
        'is_auto_execute' => 'Trường tự động xuất',
        
    ],
    // Khu tiếp đón
    'reception_room' => [
        'reception_room_code' => 'Mã khu tiếp đón',
        'reception_room_name' => 'Tên khu tiếp đón',
        'department_id' => 'Id khoa phòng',
        'area_id' => 'Id khu vực',
        'patient_type_ids' => 'Id các đối tượng cho phép tiếp đón',
        'default_cashier_room_id' => 'Id phòng thu ngân',
        'deposit_account_book_id' => 'Id sổ tạm ứng',
        'screen_saver_module_link' => 'Link module màn hình chờ',
        'is_pause' => 'Trường tạm dừng',
        'is_allow_no_icd' => 'Trường không cho phép nhập Icd',
        'is_restrict_execute_room' => 'Trường giới hạn phòng thực hiện',
        'room_type_id' => 'Id loại phòng',
    ],
    // Nhà ăn
    'refectory' => [
        'refectory_code' => 'Mã nhà ăn',
        'refectory_name' => 'Tên nhà ăn',
        'department_id' => 'Id khoa phòng',
        'room_type_id' => 'Id loại phòng',
    ],
    // Nhóm thực hiện
    'execute_group' => [
        'execute_group_code' => 'Mã nhóm thực hiện',
        'execute_group_name' => 'Tên nhóm thực hiện',
    ],
    // Phòng thu ngân
    'cashier_room' => [
        'cashier_room_code' => 'Mã phòng thu ngân',
        'cashier_room_name' => 'Tên phòng thu ngân',
        'department_id' => 'Id khoa phòng',
        'room_type_id' => 'Id loại phòng',
        'area_id' => 'Id khu vực',
        'einvoice_room_code' => 'Mã địa điểm',
        'einvoice_room_name' => 'Tên địa điểm',
    ],
    // Quốc gia
    'national' => [
        'nationnal_code' => 'Mã quốc gia',
        'national_name' => 'Tên quốc gia',
        'mps_national_code' => 'Mã quốc gia theo bộ công an',
    ],
    // Tỉnh
    'province' => [
        'province_code' => 'Mã tỉnh',
        'province_name' => 'Tên tỉnh',
        'national_id' => 'Id quốc gia',
        'search_code' => 'Tên viết tắt',
    ],
    // Tủ bệnh án
    'data_store' => [
        'data_store_code' => 'Mã tủ bệnh án',
        'data_store_name' => 'Tên tủ bệnh án',
        'department_id' => 'Id khoa phòng quản lý',
        'room_type_id' => 'Id loại phòng',
        'parent_id' => 'Id tủ bệnh án cha',
        'stored_department_id' => 'Id khoa có hồ sơ được lưu',
        'stored_room_id' => 'Id phòng có hồ sơ được lưu',
        'treatment_end_type_ids' => 'Danh sách id loại kết thúc điều trị',
        'treatment_type_ids' => 'Danh sách id điện điều trị',
    ],
    // Vai trò thực hiện
    'execute_role' => [
        'execute_role_code' => 'Mã vai trò thực hiện',
        'execute_role_name' => 'Tên vai trò thực hiện',
        'is_title' => 'Trường là chức danh',
        'is_surgry' => 'Trường là vai trò dùng trong PTTT',
        'is_stock' => 'Trường là vai trò dược',
        'is_position' => 'Trường là chức vụ',
        'is_surg_main' => 'Trường là phẫu thuật viên chính',
        'is_subclinical' => 'Trường là kỹ thuật viên',
        'is_subclinical_result' => 'Trường là người đọc kết quả',
        'allow_simultaneity' => 'Trường không chặn thực hiện cùng lúc',
        'is_single_in_ekip' => 'Trường chỉ cho phép 1 người trong kíp thực hiện',
        'is_disable_in_ekip' => 'Trường không hiển thị ở kíp thực hiện'
    ],
    // Xã
    'commune' => [
        'commune_code' => 'Mã',
        'commune_name' => 'Tên',
        'search_code' => 'Tên viết tắt',
        'initial_name' => 'Tên đơn vị',
        'district_id' => 'Id quận / huyện',
    ],
    // ICD - CM
    'icd_cm' => [
        'icd_cm_code' => 'Mã',
        'icd_cm_name' => 'Tên',
        'icd_cm_chapter_code' => 'Mã chương',
        'icd_cm_chapter_name' => 'Tên chương',
        'icd_cm_group_code' => 'Mã nhóm',
        'icd_cm_group_name' => 'Tên nhóm',
        'icd_cm_sub_group_code' => 'Mã nhóm phụ',
        'icd_cm_sub_group_name' => 'Tên nhóm phụ',
    ],
    // Bộ phận cơ thể
    'body_part' => [
        'body_part_code' => 'Mã bộ phận cơ thể',
        'body_part_name' => 'Tên bộ phận cơ thể'
    ],
    // Dịch vụ kỹ thuật
    'service' => [
        'service_type_id' => 'Id loại dịch vụ',
        'service_code' => 'Mã dịch vụ',
        'service_name' => 'Tên dịch vụ',
        'service_unit_id' => 'Id đơn vị tính',
        'speciality_code' => 'Mã chuyên khoa',
        'hein_service_type_id' => 'Id loại dịch vụ BHYT',
        'hein_service_bhyt_code' => 'Mã BHYT',
        'hein_service_bhyt_name' => 'Tên dịch vụ BHYT',
        'hein_order' => 'Số thứ tự BHYT',
        'parent_id' => 'Id dịch vụ cha',
        'package_id' => 'Id gói',
        'package_price' => 'Giá gói',
        'bill_option' => 'Loại hình hóa đơn',
        'bill_patient_type_id' => 'ID đối tượng phụ thu mặc định',
        'pttt_method_id' => 'Id phương pháp phẫu thuật thủ thuật',
        'is_not_change_bill_paty' => 'Trường không cho phép sửa DTPT mặc định khi chỉ định dịch vụ',
        'applied_patient_classify_ids' => 'Danh sách id đối tượng chi tiết áp dụng',
        'applied_patient_type_ids' => 'Danh sách id đối tượng thanh toán áp dụng',
        'testing_technique' => 'Trường kỹ thuật xét nghiệm',
        'default_patient_type_id' => 'Id đối tượng thanh toán mặc định',
        'pttt_group_id' => 'Id nhóm PTTT',
        'hein_limit_price_old' => 'Giá trần BHYT cũ',
        'icd_cm_id' => 'ICD-CM',
        'hein_limit_price_in_time' => 'Thời gian áp dụng giá trần mới theo ngày vào viện',
        'hein_limit_price' => 'Giá trần BHYT mới',
        'cogs' => 'Giá vốn', 
        'ration_symbol' => 'Ký hiệu suất ăn',
        'ration_group_id' => 'Id nhóm suất ăn',
        'num_order' => 'Trường thứ tự',
        'pacs_type_code' => 'Mã nhóm dịch vụ Pacs',
        'diim_type_id' => 'Id loại dịch vụ chẩn đoán hình ảnh',
        'fuex_type_id' => 'Id loại dịch vụ thăm dò chức năng',
        'test_type_id' => 'Id loại xét nghiệm',
        'sample_type_code' => 'Mã loại mẫu xét nghiệm',
        'max_expend' => 'Trường giới hạn số tiền hao phí',
        'number_of_film' => 'Số phim',
        'film_size_id' => 'Cỡ phim',
        'min_process_time' => 'Trường thời gian xử lý tối thiểu',
        'min_proc_time_except_paty_ids' => 'Danh sách id đối tượng thanh toán không áp dụng thời gian tối thiểu',
        'estimate_duration' => 'Trường thời gian ước tính',
        'max_process_time' => 'Trường thời gian xử lý tối đa',
        'max_proc_time_except_paty_ids' => 'Danh sách id đối tượng thanh toán không áp dụng khi nhập thời gian xử lý tối đa',
        'age_from' => 'Trường giới hạn tuổi từ',
        'age_to' => 'Trường giới hạn tuổi đến',
        'max_total_process_time' => 'Trường tổng thời gian xử lý tối đa',
        'total_time_except_paty_ids' => 'Danh sách id đối tượng thanh toán không áp dụng khi nhập tổng thời gian xử lý tối đa',
        'gender_id' => 'Id giới tính',
        'min_duration' => 'Thời gian tối thiểu',
        'max_amount' => 'Số lượng tối đa',
        'body_part_ids' => 'Danh sách id bộ phận cơ thể',
        'capacity' => 'Trường dung tích',
        'warning_sampling_time' => 'Trường thời gian lấy mẫu',
        'exe_service_module_id' => 'Id module xử lý',
        'suim_index_id' => 'Id chỉ số',
        'is_kidney' => 'Trường chạy thận',
        'is_antibiotic_resistance' => 'Trường kháng sinh đồ',
        'is_disallowance_no_execute' => 'Trường chặn không thực hiện',
        'is_multi_request' => 'Trường chỉ định khác 1',
        'is_split_service_req' => 'Trường tách y lệnh',
        'is_out_parent_fee' => 'Trường chi phí ngoài gói',
        'is_allow_expend' => 'Trường cho phép tích là hao phí',
        'is_auto_expend' => 'Trường tự động tích hao phí',
        'is_out_of_drg' => 'Trường ngoài định suất / DRG',
        'is_out_of_management' => 'Trường là dịch vụ quản lý ngoài',
        'is_other_source_paid' => 'Trường có nguồn chi trả khác',
        'is_enable_assign_price' => 'Trường cho chỉ định giá',
        'is_not_show_tracking' => 'Trường không hiển thị trên tờ điều trị',
        'must_be_consulted' => 'Trường bắt buộc có biên bản hội chẩn',
        'is_block_department_tran' => 'Trường chặn chuyển khoa',
        'allow_simultaneity' => 'Trường không chặn thực hiện cùng lúc',
        'is_not_required_complete' => 'Trường không bắt buộc hoàn thành',
        'do_not_use_bhyt' => 'Trường không hưởng BHYT',
        'allow_send_pacs' => 'Trường cho phép gửi sang Pacs',
        'other_pay_source_id' => 'Id nguồn chi trả khác',
        'attach_assign_print_type_code' => 'Mã mẫu in kèm',
        'description' => 'Trường diễn giải',
        'notice' => 'Trường ghi chú',
        'tax_rate_type' => 'Trường loại thuế suất',
        'process_code' => 'Trường mã quy trình',
    ],
    // Chính sách giá dịch vụ
    'service_paty' => [
        'service_type_id' => 'Id loại dịch vụ',
        'service_id' => 'Id dịch vụ',
        'branch_ids' => 'Danh sách id chi nhánh',
        'patient_type_id' => 'Id đối tượng thanh toán',
        'patient_classify_id' => 'Id đối tượng chi tiết',
        'price' => 'Giá',
        'vat_ratio' => 'Trường VAT%',
        'overtime_price' => 'Giá chênh lệch',
        'actual_price' => 'Giá thực tế',
        'priority' => 'Trường độ ưu tiên',
        'ration_time_id' => 'Id bữa ăn',
        'package_id' => 'Id gói dịch vụ',
        'service_condition_id' => 'Id điều kiện dịch vụ',
        'intruction_number_from' => 'Trường từ lần chỉ định thứ',
        'intruction_number_to' => 'Trường đến lần chỉ định thứ',
        'instr_num_by_type_from' => 'Trường từ lần chỉ định thứ (tính theo loại DV)',
        'instr_num_by_type_to' => 'Trường đến lần chỉ định thứ (tính theo loại DV)',
        'from_time' => 'Trường áp dụng từ',
        'to_time' => 'Trường áp dụng đến',
        'treatment_from_time' => 'Trường điều trị từ',
        'treatment_to_time' => 'Trường điều trị đến',
        'day_from' => 'Trường thứ từ',
        'day_to' => 'Trường thứ đến',
        'hour_from' => 'Trường giờ từ',
        'hour_to' => 'Trường giờ đến',
        'execute_room_ids' => 'Danh sách id phòng thực hiện',
        'request_deparment_ids' => 'Danh sách id khoa yêu cầu',
        'request_room_ids' => 'Danh sách id phòng yêu cầu',
    ],
    // Bộ phận thương tích
    'accident_body_part' => [
        'accident_body_part_code' => 'Mã bộ phận thương tích',
        'accident_body_part_name' => 'Tên bộ phận thương tích',
    ],
    // Xử lý sau tai nạn
    'accident_care' => [
        'accident_care_code' => 'Mã xử lý sau tai nạn',
        'accident_care_name' => 'Tên xử lý sau tai nạn',
    ],
    // Nguyên nhân tai nạn
    'accident_hurt_type' => [
        'accident_hurt_type_code' => 'Mã nguyên nhân tai nạn',
        'accident_hurt_type_name' => 'Tên nguyên nhân tai nạn',
    ],
    // Địa điểm tai nạn
    'accident_location' => [
        'accident_location_code' => 'Mã địa điểm tai nạn',
        'accident_location_name' => 'Tên địa điểm tai nạn',
    ],
    // Nhóm ATC
    'atc_group' => [
        'atc_group_code' => 'Mã nhóm ATC',
        'atc_group_name' => 'Tên nhóm ATC',
    ],
    // Ý thức
    'awareness' => [
        'awareness_code' => 'Mã ý thức',
        'awareness_name' => 'Tên ý thức',
    ],
    // Giường
    'bed' => [
        'bed_code' => 'Mã giường',
        'bed_name' => 'Tên giường',
        'bed_type_id' => 'Id loại giường',
        'bed_room_id' => 'Id buồng bệnh',
        'max_capacity' => 'Sức chứa tối đa',
        'is_bed_stretcher' => 'Trường là giường cáng',
    ],
    // Thẻ BHYT không hợp lệ
    'bhyt_blacklist' => [
        'hein_card_number' => 'Số thẻ',
    ],
    // Tham số BHYT
    'bhyt_param' => [
        'base_salary' => 'Lương cơ bản',
        'min_total_by_salary' => 'Tổng tiền tối thiểu',
        'max_total_package_by_salary' => 'Trần gói VTYT',
        'second_stent_paid_ratio' => 'Tỉ lệ thanh toán của stent thứ 2',
        'priority' => 'Sự ưu tiên',
        'from_time' => 'Hiệu lực từ',
        'to_time' => 'Hiệu lực đến',
    ],
    // Đầu thẻ BHYT
    'bhyt_whitelist' => [
        'bhyt_whitelist_code' => 'Đầu thẻ',
        'career_id' => 'Id nghề nghiệp',
        'is_not_check_bhyt' => 'Trường không kiểm tra trên cổng',
    ],
    // Loại thầu
    'bid_type' => [
        'bid_type_code' => 'Mã loại thầu',
        'bid_type_name' => 'Tên loại thầu',
    ],
    // Nhóm máu
    'blood_group' => [
        'blood_group_code' => 'Mã nhóm máu',
        'blood_group_name' => 'Tên nhóm máu',
        'blood_erythrocyte' => 'Trường là khối hồng cầu',
        'blood_plasma' => 'Trường là khối huyết tương, tiểu cầu',
    ],
    // Dung tích máu
    'blood_volume' => [
        'volume' => 'Dung tích',
        'is_donation' => 'Trường là hiến máu',
    ],
    // Ngôi thai
    'born_position' => [
        'born_position_code' => 'Mã ngôi thai',
        'born_position_name' => 'Tên ngôi thai',
    ],
    // Lý do hủy giao dịch
    'cancel_reason' => [
        'cancel_reason_code' => 'Mã lý do hủy giao dịch',
        'cancel_reason_name' => 'Tên lý do hủy giao dịch',
    ],
    // Nghề nghiệp
    'career' => [
        'career_code' => 'Mã nghề nghiệp',
        'career_name' => 'Tên nghề nghiệp',
    ],
    // Nghề nghiệp nhân viên
    'career_title' => [
        'career_title_code' => 'Mã nghề nghiệp nhân viên',
        'career_title_name' => 'Tên nghề nghiệp nhân viên',
    ],
    // Chống chỉ định
    'contraindication' => [
        'contraindication_code' => 'Mã chống chỉ định',
        'contraindication_name' => 'Tên chống chỉ định',
    ],
    // Thời gian tử vong
    'death_within' => [
        'death_within_code' => 'Mã thời gian tử vong',
        'death_within_name' => 'Tên thời gian tử vong',
    ],
    // Lý do hội chẩn
    'debate_reason' => [
        'debate_reason_code' => 'Mã lý do hội chẩn',
        'debate_reason_name' => 'Tên lý do hội chẩn',
    ],
    // Dạng bào chế
    'dosage_form' => [
        'dosage_form_code' => 'Mã dạng bào chế',
        'dosage_form_name' => 'Tên dạng bào chế',
    ],
    // Phương pháp vô cảm
    'emotionless_method' => [
        'emotionless_method_code' => 'Mã phương pháp vô cảm',
        'emotionless_method_name' => 'Tên phương pháp vô cảm',
        'is_first' => 'Trường phương pháp 1',
        'is_second' => 'Trường phương pháp 2',
        'is_anaesthesia' => 'Trường gây tê',
        'hein_code' => 'Mã BHYT',
    ],
    // Tài khoản nhân viên
    'emp_user' => [
        'loginname' => 'Tên đăng nhập',
        'tdl_username' => 'Họ tên',
        'dob' => 'Ngày sinh',
        'gender_id' => 'Id giới tính',
        'ethnic_code' => 'Mã dân tộc',

        'tdl_email' => 'Email',
        'tdl_mobile' => 'Số điện thoại',
        'diploma' => 'Chứng chỉ',
        'diploma_date' => 'Ngày cấp chứng chỉ hành nghề',
        'diploma_place' => 'Nơi cấp chứng chỉ hành nghề',
        'title' => 'Chức danh',

        'medicine_type_rank' => 'Hạng thuốc kê đơn',
        'max_bhyt_service_req_per_day' => 'Số lượt BHYT xử lý tối đa / ngày',
        'max_service_req_per_day' => 'Số lượt bệnh nhân tối đa / ngày',
        'is_service_req_exam' => 'Trường chỉ tính công khám',
        'account_number' => 'Số tài khoản',
        'bank' => 'Tên ngân hàng',

        'department_id' => 'Id khoa',
        'default_medi_stock_ids' => 'Danh sách Id kho mặc định',
        'erx_loginname' => 'Tên đăng nhập ERX',
        'erx_password' => 'Mật khẩu ERX',
        'identification_number' => 'Số CMT/CCCD/HC',
        'social_insurance_number' => 'Sổ bảo hiểm xã hội',
        'career_title_id' => 'Id nghề nghiệp',

        'position' => 'Vị trí',
        'speciality_codes' => 'Danh sách mã phạm vi chuyên môn',
        'type_of_time' => 'Thời gian đăng ký',
        'branch_id' => 'Id cơ sở khám chữa bệnh',
        'medi_org_codes' => 'Danh sách mã cơ sở khám chữa bệnh khác',
        'is_doctor' => 'Trường là bác sĩ',

        'is_nurse' => 'Trường là y tá',
        'is_admin' => 'Trường là quản trị hệ thống',
        'allow_update_other_sclinical' => 'Trường sửa KQ CLS',
        'do_not_allow_simultaneity' => 'Trường chặn thực hiện CLS cùng lúc',
        'is_limit_schedule' => 'Trường giới hạn thời gian làm việc',
        'is_need_sign_instead' => 'Trường cần ký thay',
    ],
    // Dân tộc
    'ethnic' => [
        'ethnic_code' => 'Mã dân tộc',
        'ethnic_name' => 'Tên dân tộc',
        'other_name' => 'Tên gọi khác',
    ],
    // Loại giấy tờ
    'file_type' => [
        'file_type_code' => 'Mã loại giấy tờ',
        'file_type_name' => 'Tên loại giấy tờ',
    ],
    // Lý do nhập viện
    'hospitalize_reason' => [
        'hospitalize_reason_code' => 'Mã lý do nhập viện',
        'hospitalize_reason_name' => 'Tên lý do nhập viện',
    ],
    // Icd
    'icd' => [
        'icd_code' => 'Mã ICD',
        'icd_name' => 'Tên ICD',
        'icd_name_en' => 'Tên tiếng Anh',
        'icd_name_common' => 'Tên thường gọi',
        'icd_group_id' => 'Id nhóm ICD',
        'attach_icd_codes' => 'Danh sách mã ICD đi kèm',

        'age_from' => 'Tuổi từ',
        'age_to' => 'Tuổi đến',
        'age_type_id' => 'Id loại tuổi',
        'gender_id' => 'Id giới tính',
        'is_sword' => 'Trường là mã kiếm',
        'is_subcode' => 'Trường là bệnh phụ',

        'is_latent_tuberculosis' => 'Trường là bệnh lao tiềm ẩn',
        'is_cause' => 'Trường là nguyên nhân gây bệnh',
        'is_hein_nds' => 'Trường ngoài định suất',
        'is_require_cause' => 'Trường bắt buộc nhập nguyên nhân ngoài',
        'is_traditional' => 'Trường là ICD y học cổ truyền',
        'unable_for_treatment' => 'Trường chặn điều trị',

        'do_not_use_hein' => 'Trường không sử dụng cho đối tượng BHYT',
        'is_covid' => 'Trường là bệnh Covid',
    ],
    // Lý do kê đơn tương tác
    'interaction_reason' => [
        'interaction_reason_code' => 'Mã lý do kê đơn tương tác',
        'interaction_reason_name' => 'Tên lý do kê đơn tương tác',
    ],
    // Hạng lái xe
    'license_class' => [
        'license_class_code' => 'Mã hạng lái xe',
        'license_class_name' => 'Tên hạng lái xe',
    ],
    // Vị trí hồ sơ bệnh án
    'location_store' => [
        'location_store_code' => 'Mã vị trí hồ sơ bệnh án',
        'location_store_name' => 'Tên vị trí hồ sơ bệnh án',
        'data_store_id' => 'Id tủ bệnh án',
    ],
    // Máy cận lâm sàng
    'machine' => [
        'machine_code' => 'Mã máy',
        'machine_name' => 'Tên máy',
        'serial_number' => 'Số Seri',
        'source_code' => 'Nguồn kinh phí',
        'machine_group_code' => 'Mã nhóm máy',
        'symbol' => 'Ký hiệu',

        'manufacturer_name' => 'Công ty sản xuất',
        'national_name' => 'Nước sản xuất',
        'manufactured_year' => 'Năm sản xuất',
        'used_year' => 'Năm sử dụng',
        'circulation_number' => 'Số lưu hành',
        'integrate_address' => 'Địa chỉ tích hợp',

        'max_service_per_day' => 'Số lượng dịch vụ / ngày',
        'department_id' => 'Id khoa',
        'room_ids' => 'Danh sách Id phòng',
        'room_id' => 'Id phòng',
        'is_kidney' => 'Trường chạy thận',
    ],
    // Hãng sản xuất
    'manufacturer' => [
        'manufacturer_code' => 'Mã hãng sản xuất',
        'manufacturer_name' => 'Tên hãng sản xuất',
        'manufacturer_short_name' => ' Tên viết tắt',
        'email' => 'Email',
        'phone' => 'Số điện thoại',
        'address' => 'Địa chỉ',
    ],
    // Chính sách giá thuốc
    'medicine_paty' => [
        'medicine_id' => 'Id thuốc',
        'patient_type_id' => 'Id đối tượng thanh toán',
        'exp_price' => 'Giá',
        'exp_vat_ratio' => 'VAT (%)',
    ],
    // Đường dùng thuốc
    'medicine_use_form' => [
        'medicine_use_form_code' => 'Mã đường dùng thuốc',
        'medicine_use_form_name' => 'Tên đường dùng thuốc',
        'num_order' => 'Số thứ tự',
    ],
    // Loại bệnh án
    'medi_record_type' => [
        'medi_record_type_code' => 'Mã loại bệnh án',
        'medi_record_type_name' => 'Tên loại bệnh án',
    ],
    // Vị trí
    'position' => [
        'position_code' => 'Mã vị trí',
        'position_name' => 'Tên vị trí',
        'description' => 'Ghi chú',
    ],
    // Chế phẩm máu
    'preparations_blood' => [
        'preparations_blood_code' => 'Mã chế phẩm máu',
        'preparations_blood_name' => 'Tên chế phẩm máu',
    ],
    // Đối tượng ưu tiên
    'priority_type' => [
        'priority_type_code' => 'Mã loại đối tượng ưu tiên',
        'priority_type_name' => 'Tên loại đối tượng ưu tiên',
        'age_from' => 'Tuối từ',
        'age_to' => 'Tuổi đến',
        'bhyt_prefixs' => 'Đầu thẻ BHYT',
        'is_for_exam_subclinical' => 'Trường ưu tiên khám và cận lâm sàng',
        'is_for_prescription' => 'Trường ưu tiên phát thuốc',
    ],
    // Tai biến PTTT
    'pttt_catastrophe' => [
        'pttt_catastrophe_code' => 'Mã tai biến PTTT',
        'pttt_catastrophe_name' => 'Tên tai biến PTTT',
    ],
    // Tình trạng PTTT
    'pttt_condition' => [
        'pttt_condition_code' => 'Mã tình trạng PTTT',
        'pttt_condition_name' => 'Tên tình trạng PTTT',
    ],
    // Nhóm PTTT
    'pttt_group' => [
        'pttt_group_code' => 'Mã nhóm',
        'pttt_group_name' => 'Tên nhóm',
        'num_order' => 'Số thứ tự',
        'remuneration' => 'Thù lao người thực hiện',
        'bed_service_type_ids' => 'Danh sách Id dịch vụ giường',
    ],
    // Phương pháp PTTT
    'pttt_method' => [
        'pttt_method_code' => 'Mã phương pháp PTTT',
        'pttt_method_name' => 'Tên phương pháp PTTT',
        'pttt_group_id' => 'Id nhóm PTTT',
    ],
    // Bàn mổ
    'pttt_table' => [
        'pttt_table_code' => 'Mã bàn mổ',
        'pttt_table_name' => 'Tên bàn mổ',
        'execute_room_id' => 'Id phòng khám',
    ],
    // Nhóm suất ăn
    'ration_group' => [
        'ration_group_code' => 'Mã nhóm suất ăn',
        'ration_group_name' => 'Tên nhóm suất ăn',
    ],
    // Bữa ăn
    'ration_time' => [
        'ration_time_code' => 'Mã bữa ăn',
        'ration_time_name' => 'Tên bữa ăn',
    ],
    // Mối quan hệ
    'relation_list' => [
        'relation_code' => 'Mã mối quan hệ',
        'relation_name' => 'Tên mối quan hệ',
    ],
    // Tôn giáo
    'religion' => [
        'religion_code' => 'Mã tôn giáo',
        'religion_name' => 'Tên tôn giáo',
    ],
    // Vai trò
    'role' => [
        'role_code' => 'Mã vai trò',
        'role_name' => 'Tên vai trò',
        'is_full' => 'Trường toàn quyền',
    ],
    // Thiết lập lợi nhuận xuất bán
    'sale_profit_cfg' => [
        'ratio' => 'Tỉ lệ thiết lập',
        'imp_price_from' => 'Giá nhập từ',
        'imp_price_to' => 'Giá nhập đến',
        'is_medicine' => 'Trường là thuốc',
        'is_material' => 'Trường là vật tư',
        'is_common_medicine' => 'Trường là thuốc thường',
        'is_functional_food' => 'Trường là thực phẩm chức năng',
        'is_drug_store' => 'Trường là thuốc thuộc quầy thuốc',
    ],
];