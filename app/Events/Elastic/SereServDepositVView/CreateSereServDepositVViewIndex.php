<?php

namespace App\Events\Elastic\SereServDepositVView;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateSereServDepositVViewIndex
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
                        'sere_serv_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],         
                        'deposit_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],    
                        'amount'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 1000000 để giữ lại 6 chữ số thập phân. Chia lại cho 1000000 khi đọc dữ liệu.
                            "scaling_factor" => 1000000
                        ],      
                        'is_cancel' => [
                            'type' => 'byte'
                        ], 
                        'tdl_treatment_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],      
                        'tdl_service_req_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],     
                        'tdl_service_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],    
                        'tdl_service_code'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],         
                        'tdl_service_name'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],       
                        'tdl_service_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],   
                        'tdl_service_unit_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],      
                        'tdl_patient_type_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],   
                        'tdl_hein_service_type_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'tdl_request_department_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ], 
                        'tdl_execute_department_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],  
                        'tdl_sere_serv_parent_id'   => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ], 
                        'tdl_is_out_parent_fee' => [
                            'type' => 'byte'
                        ],
                        'tdl_amount'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 1000000 để giữ lại 6 chữ số thập phân. Chia lại cho 1000000 khi đọc dữ liệu.
                            "scaling_factor" => 1000000
                        ],             
                        'tdl_is_expend' => [
                            'type' => 'byte'
                        ],
                        'tdl_hein_price'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],      
                        'tdl_hein_limit_price'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],  
                        'tdl_vir_price'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'tdl_vir_price_no_add_price'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],    
                        'tdl_vir_hein_price'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],  
                        'tdl_vir_total_price'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 1000000 để giữ lại 6 chữ số thập phân. Chia lại cho 1000000 khi đọc dữ liệu.
                            "scaling_factor" => 1000000
                        ],          
                        'tdl_vir_total_hein_price'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 1000000 để giữ lại 6 chữ số thập phân. Chia lại cho 1000000 khi đọc dữ liệu.
                            "scaling_factor" => 1000000
                        ],      
                        'tdl_vir_total_patient_price'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 1000000 để giữ lại 6 chữ số thập phân. Chia lại cho 1000000 khi đọc dữ liệu.
                            "scaling_factor" => 1000000
                        ],      
                        'service_req_stt_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],    
                        'service_req_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],     
                        'service_req_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],   
                        'intruction_time'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ], 
                        'service_type_code'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],        
                        'service_type_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],      
                        'transaction_code'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],      
                        'pay_form_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],      
                        'pay_form_code'   => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],    
                        'pay_form_name'   => [
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
            new PrivateChannel('elastic-sere-serv-deposit-v-view-create-index'),
        ];
    }
}
