<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Tracking;
use Illuminate\Support\Facades\DB;

class TrackingRepository
{
    protected $tracking;
    public function __construct(Tracking $tracking)
    {
        $this->tracking = $tracking;
    }

    public function applyJoins()
    {
        return $this->tracking
            ->select(
                'his_tracking.id',
                'his_tracking.create_time',
                'his_tracking.modify_time',
                'his_tracking.creator',
                'his_tracking.modifier',
                'his_tracking.app_creator',
                'his_tracking.app_modifier',
                'his_tracking.is_active',
                'his_tracking.is_delete',
                'his_tracking.treatment_id',
                'his_tracking.tracking_time',
                'his_tracking.icd_code',
                'his_tracking.icd_name',
                'his_tracking.department_id',
                'his_tracking.care_instruction',
                'his_tracking.room_id',
                'his_tracking.emr_document_stt_id',
                'his_tracking.content',
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_tracking.icd_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_tracking.icd_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_tracking.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_tracking.' . $key, $item);
                }
            }
        }

        return $query;
    }
    public function fetchData($query, $getAll, $start, $limit)
    {
        if ($getAll) {
            // Lấy tất cả dữ liệu
            return $query->get();
        } else {
            // Lấy dữ liệu phân trang
            return $query
                ->skip($start)
                ->take($limit)
                ->get();
        }
    }
    public function getById($id)
    {
        return $this->tracking->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->tracking::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'tracking_code' => $request->tracking_code,
            'tracking_name' => $request->tracking_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'tracking_code' => $request->tracking_code,
            'tracking_name' => $request->tracking_name,
            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data){
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_tracking.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_tracking.id');
            $maxId = $this->applyJoins()->max('his_tracking.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
    
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('tracking', 'his_tracking', $startId, $endId, $batchSize);
            }
        }
    }
}