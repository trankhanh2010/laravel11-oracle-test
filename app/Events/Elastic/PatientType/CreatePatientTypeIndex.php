<?php

namespace App\Events\Elastic\PatientType;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreatePatientTypeIndex
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
                        'description' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_copayment' => [
                            'type' => 'byte'
                        ],
                        'priority' => [
                            'type' => 'byte'
                        ],
                        'is_not_use_for_patient' => [
                            'type' => 'byte'
                        ],
                        'is_not_use_for_payment' => [
                            'type' => 'byte'
                        ],
                        'is_addition' => [
                            'type' => 'byte'
                        ],
                        'is_showing_out_stock_by_def' => [
                            'type' => 'byte'
                        ],
                        'is_not_require_fee' => [
                            'type' => 'byte'
                        ], 
                        'is_not_for_kiosk' => [
                            'type' => 'byte'
                        ],
                        'base_patient_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'inherit_patient_type_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'other_pay_source_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_check_fee_when_assign' => [
                            'type' => 'byte'
                        ], 
                        'other_pay_source_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_check_fee_when_pres' => [
                            'type' => 'byte'
                        ],
                        'is_not_service_bill' => [
                            'type' => 'byte'
                        ],
                        'is_not_check_fee_when_exp_pres' => [
                            'type' => 'byte'
                        ],
                        'is_for_sale_exp' => [
                            'type' => 'byte'
                        ],
                        'must_be_guaranteed'  => [
                            'type' => 'byte'
                        ],
                        'is_check_finish_cls_when_pres'  => [
                            'type' => 'byte'
                        ],
                        'is_ration' => [
                            'type' => 'byte'
                        ],
                        'is_addition_required' => [
                            'type' => 'byte'
                        ],
                        'treatment_type_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'is_not_edit_assign_service'  => [
                            'type' => 'byte'
                        ],
                        'treatment_types' => [
                            'properties' => [
                                'treatment_type_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'treatment_type_name' => [
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
                        'other_pay_sources' => [
                            'properties' => [
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
                            ],
                        ],
                        'inherit_patient_types' => [
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
            new PrivateChannel('elastic-patient-type-create-index'),
        ];
    }
}
