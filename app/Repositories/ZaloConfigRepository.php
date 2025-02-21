<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\ZaloConfig;
use Illuminate\Support\Facades\DB;

class ZaloConfigRepository
{
    protected $zaloConfig;
    public function __construct(ZaloConfig $zaloConfig)
    {
        $this->zaloConfig = $zaloConfig;
    }

    public function getToken()
    {
        // Lấy bản ghi có id lớn nhất
        return $this->zaloConfig->orderBy('id', 'desc')->first();
    }
    public function updateToken($data)
    {
        // Lấy bản ghi có `id` lớn nhất
        $tokenRecord = $this->zaloConfig->orderBy('id', 'desc')->first();
    
        if ($tokenRecord) {
            // Nếu tìm thấy bản ghi, cập nhật thông tin
            $tokenRecord->update([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
            ]);
        } else {
            // Nếu không có bản ghi nào, tạo mới
            $tokenRecord = $this->zaloConfig->create([
                'access_token' => $data['access_token'],
                'refresh_token' => $data['refresh_token'],
            ]);
        }
    
        return $tokenRecord;
    }
    
 
}