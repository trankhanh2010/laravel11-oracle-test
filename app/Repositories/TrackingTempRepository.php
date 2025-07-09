<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Room;
use App\Models\HIS\TrackingTemp;
use Illuminate\Support\Facades\DB;

class TrackingTempRepository
{
    protected $trackingTemp;
    protected $room;
    public function __construct(
        TrackingTemp $trackingTemp,
        Room $room,
        )
    {
        $this->trackingTemp = $trackingTemp;
        $this->room = $room;
    }

    public function applyJoins()
    {
        return $this->trackingTemp
            ->select(
                'his_tracking_temp.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_tracking_temp.tracking_temp_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_tracking_temp.tracking_temp_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_tracking_temp.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_tracking_temp.' . $key, $item);
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
        return $this->trackingTemp->find($id);
    }

    public function applySelectByLoginnameFilter($query, $currentLoginname, $roomId)
    {
        $currentDepartmentId = $roomId ? ($this->room->where('his_room.id', $roomId)->first()->department_id??0) : 0;
        if ($currentLoginname != null) {
            $query->where(function ($q) use ($currentLoginname, $currentDepartmentId) {
                $q->where(DB::connection('oracle_his')->raw('his_tracking_temp.creator'), $currentLoginname)
                ->orWhere(DB::connection('oracle_his')->raw('his_tracking_temp.is_public'), 1)
                ->orWhere(function ($q2) use ($currentDepartmentId) {
                    $q2->where(DB::connection('oracle_his')->raw('his_tracking_temp.department_id'), $currentDepartmentId)
                    ->where(DB::connection('oracle_his')->raw('his_tracking_temp.is_public_in_department'), 1);
            });
            });
        }
        return $query;
    }
    public function create($request, $time, $appCreator, $appModifier){
        $data = $this->trackingTemp::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'tracking_temp_code' => $request->tracking_temp_code,
            'tracking_temp_name' => $request->tracking_temp_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier){
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'tracking_temp_code' => $request->tracking_temp_code,
            'tracking_temp_name' => $request->tracking_temp_name,
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
            $data = $this->applyJoins()->where('his_tracking_temp.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_tracking_temp.id');
            $maxId = $this->applyJoins()->max('his_tracking_temp.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('tracking_temp', 'his_tracking_temp', $startId, $endId, $batchSize);
            }
        }
    }
}