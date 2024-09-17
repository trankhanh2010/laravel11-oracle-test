<?php

namespace App\Events\Elastic\Icd;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateIcdIndex
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
                        'icd_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'icd_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'icd_name_en' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'chapter_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'chapter_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'chapter_name_en' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'sub_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'sub_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'sub_name_en' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'sub_code_1' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'sub_name_1' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'sub_name_1_en' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],    
                        'sub_code_2' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],   
                        'sub_name_2' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'sub_name_2_en' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],   
                        'type_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'type_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],       
                        'type_name_en' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'byt_report_code' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'icd_name_common' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'icd_chapter_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'icd_group_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_hein_nds' => [
                            'type' => 'byte'
                        ],
                        'is_require_cause' => [
                            'type' => 'byte'
                        ],
                        'is_cause' => [
                            'type' => 'byte'
                        ],
                        'is_traditional' => [
                            'type' => 'byte'
                        ],
                        'unable_for_treatment' => [
                            'type' => 'byte'
                        ],
                        'is_latent_tuberculosis' => [
                            'type' => 'byte'
                        ],
                        'do_not_use_hein' => [
                            'type' => 'byte'
                        ],
                        'gender_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'attach_icd_codes' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'age_from' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'age_to' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'age_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'is_subcode' => [
                            'type' => 'byte'
                        ],
                        'is_sword' => [
                            'type' => 'byte'
                        ],
                        'is_covid' => [
                            'type' => 'byte'
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
            new PrivateChannel('elastic-icd-create-index'),
        ];
    }
}
