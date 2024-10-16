<?php

namespace App\Jobs\ElasticSearch\Index;

use App\Repositories\DebateRepository;
use App\Repositories\DebateVViewRepository;
use App\Repositories\ServiceReqLViewRepository;
use App\Repositories\TestServiceReqListVViewRepository;
use App\Repositories\TrackingRepository;
use App\Repositories\UserRoomVViewRepository;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;

class ProcessElasticIndexingJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    protected $startId;
    protected $endId;
    protected $batchSize;
    protected $name;
    protected $nameTable;
    protected $paramWith;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($name, $nameTable, $startId, $endId, $batchSize, $paramWith = null)
    {
        $this->name = $name;
        $this->nameTable = $nameTable;
        $this->startId = $startId;
        $this->endId = $endId;
        $this->batchSize = $batchSize;
        $this->paramWith = $paramWith;
    }

    /**
     * Execute the job.
     */
    // public function handle()
    // {
    //     $batchData = [];
    //     $count = 0;
    //     $repository = $this->repository($this->name);
    //     $query = $repository->applyJoins()
    //         ->whereBetween($this->nameTable . '.id', [$this->startId, $this->endId]);
    //     foreach ($query->cursor() as $item) {
    //         if ($this->paramWith != null) {
    //             $item->load($this->paramWith);
    //             $attributes = $item;
    //         }else{
    //             $attributes = $item->getAttributes();
    //         }
    //         $batchData[] = $attributes;
    //         $count++;

    //         if ($count % $this->batchSize == 0) {
    //             $this->indexing($this->name, $batchData);
    //             $batchData = [];
    //         }
    //     }
    //     // Gửi các bản ghi còn lại
    //     if (!empty($batchData)) {
    //         $this->indexing($this->name, $batchData);
    //     }
    // }

    public function handle()
    {
        try {
            $batchData = [];
            $count = 0;
            $repository = $this->repository($this->name);
            $query = $repository->applyJoins()
                ->whereBetween($this->nameTable . '.id', [$this->startId, $this->endId]);
                if ($this->paramWith != null) {
                    $query->with($this->paramWith)->chunkById($this->batchSize, function ($items) use (&$batchData, &$count) {
                            $this->indexing($this->name, $items);
                        });
                } else {
                    foreach ($query->cursor() as $item) {
                        $attributes = $item->getAttributes();
                        $batchData[] = $attributes;
                        $count++;
        
                        if ($count % $this->batchSize == 0) {
                            $this->indexing($this->name, $batchData);
                            $batchData = [];
                        }
                    }
                }
            // Gửi các bản ghi còn lại
            if (!empty($batchData)) {
                $this->indexing($this->name, $batchData);
            }
        } catch (\Exception $e) {

        } finally {
                DB::disconnect();
        }
        
    }

    public function repository($name)
    {
        $repository = null;
        switch ($name) {
            case 'tracking':
                $repository = app(TrackingRepository::class);;
                break;
            case 'service_req_l_view':
                $repository = app(ServiceReqLViewRepository::class);;
                break;
            case 'test_service_req_list_v_view':
                $repository = app(TestServiceReqListVViewRepository::class);;
                break;
            case 'debate':
                $repository = app(DebateRepository::class);;
                break;
            case 'debate_v_view':
                $repository = app(DebateVViewRepository::class);;
                break;
            case 'user_room_v_view':
                $repository = app(UserRoomVViewRepository::class);;
                break;
            default:
                break;
        }
        return $repository;
    }

    public function indexing($name_table, $results)
    {
        // Khởi tạo kết nối đến Elastic
        $client = app('Elasticsearch');
        $maxBatchSizeMB = config('database')['connections']['elasticsearch']['bulk']['max_batch_size_mb'];
        if (isset($results)) {
            // Dùng Bulk
            $bulkData = [];
            $currentBatchSizeBytes = 0;
            $maxBatchSizeBytes = $maxBatchSizeMB * 1024 * 1024; // Chuyển đổi MB sang bytes

            foreach ($results as $result) {
                // Chuẩn bị dữ liệu cho mỗi bản ghi
                $data = [];
                // Decode và đổi tên trường về mặc định các bảng có dùng with
                if (in_array($name_table, config('params')['elastic']['json_decode'])) {
                    $result = convertKeysToSnakeCase(json_decode($result, true));
                } else {
                    // Nếu không cần decode, giả sử $result là mảng
                    $result = is_string($result) ? json_decode($result, true) : $result;
                }

                foreach ($result as $key => $value) {
                    $data[$key] = $value;
                }

                // Thêm các thông tin cần thiết cho mỗi tài liệu vào bulkData
                $actionMeta = [
                    'index' => [
                        '_index' => $name_table,
                        '_id'    => $result['id'], // Sử dụng id của bản ghi làm id cho Elasticsearch
                    ]
                ];
                // Tính kích thước của actionMeta và data
                $actionMetaSize = strlen(json_encode($actionMeta)) + 1; // Thêm 1 byte cho dấu xuống dòng
                $dataSize = strlen(json_encode($data)) + 1; // Thêm 1 byte cho dấu xuống dòng
                // Kiểm tra nếu thêm vào lô hiện tại vượt quá giới hạn kích thước
                if (($currentBatchSizeBytes + $actionMetaSize + $dataSize) > $maxBatchSizeBytes) {
                    // Thực hiện bulk insert với lô hiện tại
                    if (!empty($bulkData)) {
                        $client->bulk(['body' => $bulkData]);
                        // Reset bulkData và currentBatchSizeBytes sau khi bulk insert
                        $bulkData = [];
                        $currentBatchSizeBytes = 0;
                    }
                }
                // Thêm actionMeta và data vào bulkData
                $bulkData[] = $actionMeta;
                $bulkData[] = $data;
                $currentBatchSizeBytes += $actionMetaSize + $dataSize;
            }
            // Chèn các bản ghi còn lại nếu có
            if (!empty($bulkData)) {
                $client->bulk(['body' => $bulkData]);
            }
        }
    }
}
