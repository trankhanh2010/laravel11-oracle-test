<?php

namespace App\Events\Elastic\MedicinePaty;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateMedicinePatyIndex
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
                        'analyzer' => [
                            'my_custom_analyzer' => [
                                'type' => 'custom',
                                'tokenizer' => 'standard',
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
                        'medicine_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],      
                        'patient_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],     
                        'exp_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],   
                        'exp_vat_ratio' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],   
                        'imp_unit_exp_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
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
                        'contract_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'tax_ratio' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'expired_date' => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'tdl_bid_number' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tdl_bid_num_order' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'imp_time' => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'imp_vat_ratio' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'imp_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
                        ],
                        'vir_imp_price' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'internal_price' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 10000 để giữ lại 4 chữ số thập phân. Chia lại cho 10000 khi đọc dữ liệu.
                            "scaling_factor" => 10000
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
            new PrivateChannel('elastic-medicine-paty-create-index'),
        ];
    }
}