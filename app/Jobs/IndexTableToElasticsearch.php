<?php

namespace App\Jobs;

use Elasticsearch\Client;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class IndexTableToElasticsearch implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $client;
    protected $table;
    protected $first_table;
    protected $name_table;

    /**
     * Create a new job instance.
     */
    public function __construct($table, $first_table, $name_table)
    {
        $this->table = $table;
        $this->first_table = $first_table;
        $this->name_table = $name_table;
    }
    /**
     * Execute the job.
     */
    public function handle()
    {
        $this->client = app('Elasticsearch');
        switch ($this->table) {
            case 'his_bed':
                $results = DB::connection('oracle_' . $this->first_table)
                    ->table($this->table)
                    ->leftJoin('his_bed_type', 'his_bed.bed_type_id', '=', 'his_bed_type.id')
                    ->leftJoin('his_bed_room', 'his_bed.bed_room_id', '=', 'his_bed_room.id')
                    ->leftJoin('his_room', 'his_bed_room.room_id', '=', 'his_room.id')
                    ->leftJoin('his_department', 'his_room.department_id', '=', 'his_department.id')

                    ->select(
                        'his_bed.*',
                        'his_bed_type.bed_type_name',
                        'his_bed_type.bed_type_code',
                        'his_bed_room.bed_room_name',
                        'his_bed_room.bed_room_code',
                        'his_department.department_name',
                        'his_department.department_code',
                    )
                    ->get();
                    $exists = $this->client->indices()->exists(['index' => $this->name_table])->asBool();
                    if(!$exists){
                        $params = [
                            'index' => $this->name_table,
                            'body' => [
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
                                        'bed_code' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ],
                                        'bed_name' => [
                                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                            'fields' => [
                                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                                    'type' => 'keyword'
                                                ]
                                            ]
                                        ],
                                        'bed_room_id' => [
                                            'type' => 'long'  // Số nguyên 64-bit
                                        ],
                                        'bed_type_id' => [
                                            'type' => 'long'  // Số nguyên 64-bit
                                        ],
                                        'max_capacity' => [
                                            'type' => 'integer'  // Số nguyên 32-bit
                                        ],
                                        'x' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ],
                                        'y' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ],
                                        'treatment_room_id' => [
                                            'type' => 'long'  // Số nguyên 64-bit
                                        ],
                                        'is_bed_stretcher' => [
                                            'type' => 'byte'  
                                        ],
                                        'bed_type_name' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ],
                                        'bed_type_code' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ],
                                        'bed_room_name' => [
                                            'type' => 'text',  // Văn bản phân tích, hỗ trợ tìm kiếm full-text
                                            'fields' => [
                                                'keyword' => [  // Phân tích không để sắp xếp và tìm kiếm chính xác
                                                    'type' => 'keyword'
                                                ]
                                            ]
                                        ],
                                        'bed_room_code' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ],
                                        'department_name' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ],
                                        'department_code' => [
                                            'type' => 'keyword'  // Chuỗi không phân tích, lưu trữ giá trị chính xác
                                        ]
                                    ]
                                ]
                            ]
                        ];
                    
                        // Tạo chỉ mục
                        $this->client->indices()->create($params);
                    }
                break;

            default:
                // Xử lý mặc định hoặc xử lý khi không có bảng khớp
                $results = DB::connection('oracle_' . $this->first_table)->table($this->table)->get();
                break;
        }
        foreach ($results as $result) {
            $data = [];
            foreach ($result as $key => $value) {
                $data[$key] = $value;
            }
            $params = [
                'index' => $this->name_table,
                'id'    => $result->id,
                'body'  => $data
            ];

            $this->client->index($params);
        }
    }
}
