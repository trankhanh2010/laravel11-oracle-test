<?php

namespace App\Events\Elastic\MediStock;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateMediStockIndex
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
                                'stopwords' => ['kho', 'khoa'] // Danh sách từ dừng tùy chỉnh
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
                        'medi_stock_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'medi_stock_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'room_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'parent_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'bhyt_head_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'not_in_bhyt_head_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_allow_imp_supplier' => [
                            'type' => 'byte'
                        ],
                        'is_cabinet' => [
                            'type' => 'byte'
                        ],
                        'is_business'  => [
                            'type' => 'byte'
                        ],
                        'is_auto_create_chms_imp' => [
                            'type' => 'byte'
                        ],
                        'is_goods_restrict' => [
                            'type' => 'byte'
                        ],
                        'is_odd' => [
                            'type' => 'byte'
                        ],
                        'is_blood' => [
                            'type' => 'byte'
                        ],
                        'is_show_ddt' => [
                            'type' => 'byte'
                        ],
                        'is_new_medicine' => [
                            'type' => 'byte'
                        ],
                        'is_traditional_medicine' => [
                            'type' => 'byte'
                        ],
                        'is_planning_trans_as_default' => [
                            'type' => 'byte'
                        ],
                        'cabinet_manage_option' => [
                            'type' => 'byte'
                        ],
                        'is_show_inpatient_return_pres' => [
                            'type' => 'byte'
                        ],
                        'is_drug_store' => [
                            'type' => 'byte'
                        ],
                        'is_for_rejected_moba' => [
                            'type' => 'byte'
                        ],
                        'is_moba_change_amount' => [
                            'type' => 'byte'
                        ],
                        'patient_classify_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'is_show_drug_store' => [
                            'type' => 'byte'
                        ],
                        'is_expend' => [
                            'type' => 'byte'
                        ],
                        'is_auto_create_reusable_imp' => [
                            'type' => 'byte'
                        ],
                        'do_not_imp_medicine' => [
                            'type' => 'byte'
                        ],
                        'do_not_imp_material' => [
                            'type' => 'byte'
                        ],
                        'is_show_anticipate' => [
                            'type' => 'byte'
                        ],
                        'department_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'department_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'room_type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'room_type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'parent_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'parent_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'exp_mest_types' => [
                            'properties' => [
                                'exp_mest_type_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'exp_mest_type_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'pivot' => [
                                    'properties' => [
                                        'exp_mest_type_id' => [
                                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                        ],
                                        'medi_stock_id' => [
                                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                        ],
                                        'is_auto_approve' => [
                                            'type' => 'byte'
                                        ],
                                        'is_auto_execute' => [
                                            'type' => 'byte'
                                        ],
                                        'id' => [
                                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                        ],
                                    ]
                                ]
                            ],
                        ],
                        'imp_mest_types' => [
                            'properties' => [
                                'imp_mest_type_code' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'imp_mest_type_name' => [
                                    'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                    'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                                    'fields' => [
                                        'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                            'type' => 'keyword'
                                        ]
                                    ]
                                ],
                                'pivot' => [
                                    'properties' => [
                                        'imp_mest_type_id' => [
                                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                        ],
                                        'medi_stock_id' => [
                                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                        ],
                                        'is_auto_approve' => [
                                            'type' => 'byte'
                                        ],
                                        'is_auto_execute' => [
                                            'type' => 'byte'
                                        ],
                                        'id' => [
                                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                                        ],
                                    ]
                                ]
                            ],
                        ],
                        'patient_classifys' => [
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
            new PrivateChannel('elastic-medi-stock-create-index'),
        ];
    }
}
