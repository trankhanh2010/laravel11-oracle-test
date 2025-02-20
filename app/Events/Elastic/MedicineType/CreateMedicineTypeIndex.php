<?php

namespace App\Events\Elastic\MedicineType;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateMedicineTypeIndex
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
                        'medicine_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'medicine_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'service_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'parent_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_leaf' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'num_order' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],  
                        'concentra' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'active_ingr_bhyt_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'active_ingr_bhyt_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'register_number' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'packing_type_id_delete' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'manufacturer_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ], 
                        'medicine_use_form_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'medicine_line_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'medicine_group_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'tdl_service_unit_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],  
                        'tdl_gender_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'national_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],      
                        'tutorial' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'imp_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],    
                        'imp_vat_ratio' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ], 
                        'internal_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'alert_max_in_treatment' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],
                        'alert_expired_date'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'alert_min_in_stock' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],
                        'alert_max_in_prescription' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],
                        'is_stop_imp' => [
                            'type' => 'byte'
                        ],
                        'is_star_mark' => [
                            'type' => 'byte'
                        ],
                        'is_allow_odd' => [
                            'type' => 'byte'
                        ],
                        'is_allow_export_odd' => [
                            'type' => 'byte'
                        ],
                        'is_functional_food' => [
                            'type' => 'byte'
                        ],
                        'is_require_hsd' => [
                            'type' => 'byte'
                        ], 
                        'is_sale_equal_imp_price' => [
                            'type' => 'byte'
                        ],
                        'is_business' => [
                            'type' => 'byte'
                        ],
                        'is_raw_medicine' => [
                            'type' => 'byte'
                        ],
                        'is_auto_expend'  => [
                            'type' => 'byte'
                        ],
                        'is_vitamin_a' => [
                            'type' => 'byte'
                        ], 
                        'is_vaccine' => [
                            'type' => 'byte'
                        ],
                        'is_tcmr'  => [
                            'type' => 'byte'
                        ],
                        'is_must_prepare' => [
                            'type' => 'byte'
                        ],
                        'use_on_day' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],
                        'description' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],       
                        'mema_group_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'byt_num_order' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tcy_num_order' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'medicine_type_proprietary_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'packing_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],  
                        'rank' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'medicine_national_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'is_kidney' => [
                            'type' => 'byte'
                        ], 
                        'is_chemical_substance' => [
                            'type' => 'byte'
                        ],
                        'last_exp_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'last_exp_vat_ratio' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'contraindication' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'last_imp_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'last_imp_vat_ratio' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'atc_codes' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'last_expired_date' => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'recording_transaction' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'is_treatment_day_count' => [
                            'type' => 'byte'
                        ], 
                        'allow_missing_pkg_info' => [
                            'type' => 'byte'
                        ], 
                        'is_block_max_in_prescription' => [
                            'type' => 'byte'
                        ],
                        'is_oxygen' => [
                            'type' => 'byte'
                        ],
                        'is_split_compensation' => [
                            'type' => 'byte'
                        ], 
                        'storage_condition_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'contraindication_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],  
                        'is_out_hospital' => [
                            'type' => 'byte'
                        ],
                        'imp_unit_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'imp_unit_convert_ratio' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'scientific_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'preprocessing' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'processing' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'used_part' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'dosage_form' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'distributed_amount' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_not_treatment_day_count' => [
                            'type' => 'byte'
                        ], 
                        'is_anaesthesia' => [
                            'type' => 'byte'
                        ],
                        'vaccine_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'quality_standards' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'source_medicine' => [
                            'type' => 'byte'
                        ],
                        'is_drug_store' => [
                            'type' => 'byte'
                        ], 
                        'locking_reason' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'preprocessing_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'processing_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'num_order_circulars20' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_block_max_in_day' => [
                            'type' => 'byte'
                        ],
                        'alert_max_in_day' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 4 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],
                        'htu_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'odd_warning_content' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'is_original_brand_name' => [
                            'type' => 'byte'
                        ],
                        'is_generic' => [
                            'type' => 'byte'
                        ],
                        'is_biologic' => [
                            'type' => 'byte'
                        ],
                        'atc_group_codes' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
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
            new PrivateChannel('elastic-medicine-type-create-index'),
        ];
    }
}
