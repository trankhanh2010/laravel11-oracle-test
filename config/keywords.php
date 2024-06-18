<?php

return [
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
    ],
    // Khu vực - Area
    'area' => [
        'department_id' => 'Id khoa phòng',
        'area_name' => 'Tên khu vực',
        'area_code' => 'Mã khu vực',
    ]
];