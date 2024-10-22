<?php

namespace App\Events\Elastic\TreatmentBedRoomLView;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CreateTreatmentBedRoomLViewIndex
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
                        'treatment_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'co_treatment_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],   
                        'add_time'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],           
                        'bed_room_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'remove_time'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],    
                        'treatment_room_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ], 
                        'tdl_observed_time_from'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'tdl_observed_time_to'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'patient_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],   
                        'treatment_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],        
                        'tdl_patient_first_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],       
                        'tdl_patient_last_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tdl_patient_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],    
                        'tdl_patient_dob'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],       
                        'tdl_patient_gender_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],     
                        'tdl_patient_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'tdl_patient_address'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tdl_hein_card_number'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tdl_hein_medi_org_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'icd_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],   
                        'icd_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],     
                        'icd_text'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'icd_sub_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tdl_patient_gender_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],    
                        'tdl_hein_medi_org_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],    
                        'is_pause' => [
                            'type' => 'byte'
                        ],
                        'is_approve_finish' => [
                            'type' => 'byte'
                        ],
                        'approve_finish_note'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tdl_patient_classify_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'tdl_treatment_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],   
                        'emr_cover_type_id' => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],  
                        'clinical_in_time'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'co_treat_department_ids' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],  
                        'out_time'  => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],  
                        'tdl_patient_avatar_url'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'last_department_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],
                        'tdl_patient_unsigned_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'treatment_end_type_id'  => [
                            'type' => 'long'  // Số nguyên 64-bit, phù hợp với ID số
                        ],   
                        'treatment_method'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'tdl_patient_phone'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'tdl_hein_card_from_time' => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],  
                        'tdl_hein_card_to_time' => [
                            'type' => 'date',  // Lưu trữ ngày giờ
                            'format' => 'yyyyMMddHHmmss'
                        ],
                        'tdl_patient_cccd_number'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],   
                        'tdl_patient_cmnd_number'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],  
                        'tdl_patient_passport_number'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'tdl_patient_mobile'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],   
                        'doctor_loginname'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],     
                        'doctor_username'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],    
                        'patient_type_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],  
                        'bed_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'bed_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'patient_type_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],  
                        'bed_room_name'  => [
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
                        'display_color'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],     
                        'treatment_room_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'treatment_room_name'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ], 
                        'last_department_code'  => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'last_department_name' => [
                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                            'analyzer' => 'my_custom_analyzer', // Sử dụng analyzer tùy chỉnh
                            'fields' => [
                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                    'type' => 'keyword'
                                ]
                            ]
                        ],
                        'note'  => [
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
            new PrivateChannel('elastic-treatment-bed-room-l-view-create-index'),
        ];
    }
}
