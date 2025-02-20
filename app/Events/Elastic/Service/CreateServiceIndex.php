<?php

namespace App\Events\Elastic\Service;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateServiceIndex
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public $modelName;
    public $params;
    public function __construct($modelName)
    {
        $this->modelName = $modelName;
        $this->params =  [
            'index' => $this->modelName,
            'body' => [
                'settings' => [
                    'analysis' => [
                        'tokenizer' => [
                            'ngram_tokenizer' => [
                                'type' => 'ngram',
                                'min_gram' => 1,
                                'max_gram' => 1,
                                'token_chars' => ['letter', 'digit', 'whitespace']
                            ]
                        ],
                        'analyzer' => [
                            'my_custom_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'ngram_tokenizer',
                                'filter' => [
                                    'lowercase',       // Chuyển chữ hoa thành chữ thường
                                    'asciifolding',    // Loại bỏ dấu
                                    'my_stop_filter'
                                ]
                            ]
                        ],
                        'filter' => [
                            'my_stop_filter' => [
                                'type' => 'stop',
                                'stopwords' => [] // Danh sách từ dừng tùy chỉnh
                            ]
                        ]
                    ]
                ],
                'mappings' => [
                    'properties' => [
                        'id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'create_time' => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'modify_time' => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'creator' => [
                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                        ],
                        'modifier' => [
                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                        ],
                        'app_creator' => [
                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                        ],
                        'app_modifier' => [
                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                        ],
                        'is_active' => [
                            'type' => 'byte'
                        ],
                        'is_delete' => [
                            'type' => 'byte'
                        ],
                        'group_code' => [
                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác, có thể là null
                        ],
                        'service_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'service_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'service_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'service_unit_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'parent_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],  
                        'is_leaf'  => [
                            'type' => 'byte'
                        ],
                        'num_order'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'hein_service_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'hein_service_bhyt_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'hein_service_bhyt_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'hein_order'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_use_service_hein'  => [
                            'type' => 'byte'
                        ],
                        'hein_limit_price_old'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'hein_limit_ratio_old'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'hein_limit_price'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'hein_limit_ratio'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'hein_limit_price_in_time'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'hein_limit_price_intr_time'   => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'speciality_code'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_multi_request'  => [
                            'type' => 'byte'
                        ],
                        'max_expend'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'bill_option' => [
                            'type' => 'byte'
                        ],
                        'bill_patient_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_out_parent_fee'  => [
                            'type' => 'byte'
                        ],
                        'pttt_group_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'pttt_method_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'icd_cm_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'cogs'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'estimate_duration'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],
                        'revenue_department_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'package_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'package_price'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'number_of_film'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'pacs_type_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'min_duration'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'exe_service_module_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'gender_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'age_from'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],  
                        'age_to'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'ration_group_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'ration_symbol'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'notice'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'capacity'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_allow_expend' => [
                            'type' => 'byte'
                        ], 
                        'is_no_hein_limit_for_special' => [
                            'type' => 'byte'
                        ],
                        'is_kidney'  => [
                            'type' => 'byte'
                        ],
                        'is_specific_hein_price'  => [
                            'type' => 'byte'
                        ],
                        'is_other_source_paid' => [
                            'type' => 'byte'
                        ],
                        'is_antibiotic_resistance'  => [
                            'type' => 'byte'
                        ],
                        'diim_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'fuex_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'tax_rate_type' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'description'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_split_service_req'  => [
                            'type' => 'byte'
                        ],
                        'is_enable_assign_price'  => [
                            'type' => 'byte'
                        ],
                        'is_auto_finish' => [
                            'type' => 'byte'
                        ],
                        'process_code'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'test_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_out_of_drg' => [
                            'type' => 'byte'
                        ],
                        'is_conditioned'  => [
                            'type' => 'byte'
                        ],
                        'min_process_time'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_not_change_bill_paty'  => [
                            'type' => 'byte'
                        ],
                        'is_split_service'   => [
                            'type' => 'byte'
                        ],
                        'other_pay_source_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'other_pay_source_icds' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'body_part_ids'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'testing_technique' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_out_of_management' => [
                            'type' => 'byte'
                        ],
                        'must_be_consulted' => [
                            'type' => 'byte'
                        ],
                        'suim_index_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'attach_assign_print_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'test_covid_type'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'max_process_time'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_disallowance_no_execute'  => [
                            'type' => 'byte'
                        ],
                        'is_not_show_tracking'  => [
                            'type' => 'byte'
                        ],
                        'film_size_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_auto_expend'  => [
                            'type' => 'byte'
                        ],
                        'is_block_department_tran'   => [
                            'type' => 'byte'
                        ],
                        'warning_sampling_time'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'applied_patient_type_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'default_patient_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'max_total_process_time'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'allow_simultaneity'  => [
                            'type' => 'byte'
                        ],
                        'sample_type_code'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'min_proc_time_except_paty_ids'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'max_proc_time_except_paty_ids'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'max_amount'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_not_required_complete'  => [
                            'type' => 'byte'
                        ], 
                        'do_not_use_bhyt'  => [
                            'type' => 'byte'
                        ],
                        'petroleum_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'petroleum_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'allow_send_pacs'   => [
                            'type' => 'byte'
                        ],
                        'total_time_except_paty_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'applied_patient_classify_ids'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'bhyt_whitelist_codes' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'service_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'service_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'parent_service_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'parent_service_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'service_unit_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'service_unit_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'hein_service_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'hein_service_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'bill_patient_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'bill_patient_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'pttt_group_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'pttt_group_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'pttt_method_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'pttt_method_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'icd_cm_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'icd_cm_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'revenue_department_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'revenue_department_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'package_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'package_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'exe_service_module_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'gender_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'gender_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'ration_group_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'ration_group_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'diim_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'diim_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'fuex_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'fuex_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'test_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'test_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'other_pay_source_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'other_pay_source_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'film_size_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'film_size_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'default_patient_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'default_patient_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'body_parts' => [
                            'properties' => [
                                'body_part_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'body_part_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                            ],
                        ],
                        'applied_patient_types' => [
                            'properties' => [
                                'patient_type_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'patient_type_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                            ],
                        ],
                        'applied_patient_classifys' => [
                            'properties' => [
                                'patient_classify_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'patient_classify_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                            ],
                        ],
                        'min_proc_time_except_patys' => [
                            'properties' => [
                                'patient_type_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'patient_type_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                            ],
                        ],
                        'max_proc_time_except_patys' => [
                            'properties' => [
                                'patient_type_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'patient_type_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                            ],
                        ],
                        'total_time_except_patys' => [
                            'properties' => [
                                'patient_type_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'patient_type_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                            ],
                        ],
                    ]
                ]
            ]
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, \Illuminate\Broadcasting\Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('elastic-service-create-index'),
        ];
    }
}
