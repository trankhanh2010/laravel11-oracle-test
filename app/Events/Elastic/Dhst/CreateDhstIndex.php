<?php

namespace App\Events\Elastic\Dhst;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateDhstIndex
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
                        'treatment_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],       
                        'dhst_sum_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ], 
                        'tracking_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'care_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'execute_room_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ], 
                        'execute_department_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'execute_loginname'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],       
                        'execute_username'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],   
                        'execute_time'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'temperature'   => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],   
                        'breath_rate'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],   
                        'weight'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],   
                        'height'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],   
                        'chest'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],    
                        'belly'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],   
                        'blood_pressure_max'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'blood_pressure_min'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],    
                        'pulse'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'vir_bmi'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],    
                        'vir_body_surface_area'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],   
                        'spo2' => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],    
                        'capillary_blood_glucose'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],    
                        'note' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],        
                        'infution_into'  => [
                            'type' => 'byte'
                        ],
                        'infution_out'  => [
                            'type' => 'byte'
                        ],
                        'vaccination_exam_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'urine'  => [
                            "type" => "scaled_float", // nhân giá trị gốc với 100 để giữ lại 2 chữ số thập phân. Chia lại cho 100 khi đọc dữ liệu.
                            "scaling_factor" => 100
                        ],  
                        'service_reqs' => [
                            'properties' => [
                                'id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                                'dhst_id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                            ],
                        ],    
                        'antibiotic_request' => [
                            'properties' => [
                                'id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                                'dhst_id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                            ],
                        ], 
                        'cares' => [
                            'properties' => [
                                'id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                                'dhst_id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                            ],
                        ], 
                        'ksk_generals' => [
                            'properties' => [
                                'id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                                'dhst_id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                            ],
                        ], 
                        'ksk_occupationals' => [
                            'properties' => [
                                'id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                ], 
                                'dhst_id'  => [
                                    'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
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
            new PrivateChannel('elastic-dhst-create-index'),
        ];
    }
}
