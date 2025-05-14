<?php 
namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\View\TreatmentBedRoomLView;
use Illuminate\Support\Facades\DB;

class TreatmentBedRoomLViewRepository
{
    protected $treatmentBedRoomLView;
    public function __construct(TreatmentBedRoomLView $treatmentBedRoomLView)
    {
        $this->treatmentBedRoomLView = $treatmentBedRoomLView;
    }

    public function applyJoins()
    {
        return $this->treatmentBedRoomLView
            ->select(
                'l_his_treatment_bed_room.*'
            );
    }
    public function view()
    {
        return $this->treatmentBedRoomLView
            ->select(
                'l_his_treatment_bed_room.*',
                );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('l_his_treatment_bed_room.loginname'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_treatment_bed_room.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $isDelete)
    {
        if ($isDelete !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_treatment_bed_room.is_delete'), $isDelete);
        }
        return $query;
    }
    public function applyBedRoomIdsFilter($query, $ids)
    {
        if ($ids !== null) {
            $query->whereIn(DB::connection('oracle_his')->raw('l_his_treatment_bed_room.bed_room_id'), $ids);
        }
        return $query;
    }
    public function applyAddTimeToFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('l_his_treatment_bed_room.add_time'), '>=', $param);
        }
        return $query;
    }
    public function applyIsInRoomFilter($query, $isInRoom, $time)
    {
        if($isInRoom !== null){
            if(!$isInRoom){
                if ($time !== null) {
                    $query->where(DB::connection('oracle_his')->raw('l_his_treatment_bed_room.add_time'), '<=', $time);
                }
            }else{
                $query->whereNull(DB::connection('oracle_his')->raw('l_his_treatment_bed_room.remove_time'));
            }
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('l_his_treatment_bed_room.' . $key, $item);
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
        return $this->treatmentBedRoomLView->find($id);
    }
    // public function create($request, $time, $appCreator, $appModifier){
    //     $data = $this->treatmentBedRoomLView::create([
    //         'create_time' => now()->format('YmdHis'),
    //         'modify_time' => now()->format('YmdHis'),
    //         'creator' => get_loginname_with_token($request->bearerToken(), $time),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_creator' => $appCreator,
    //         'app_modifier' => $appModifier,
    //         'is_active' => 1,
    //         'is_delete' => 0,
    //         'treatment_bed_room_l_view_code' => $request->treatment_bed_room_l_view_code,
    //         'treatment_bed_room_l_view_name' => $request->treatment_bed_room_l_view_name,
    //     ]);
    //     return $data;
    // }
    // public function update($request, $data, $time, $appModifier){
    //     $data->update([
    //         'modify_time' => now()->format('YmdHis'),
    //         'modifier' => get_loginname_with_token($request->bearerToken(), $time),
    //         'app_modifier' => $appModifier,
    //         'treatment_bed_room_l_view_code' => $request->treatment_bed_room_l_view_code,
    //         'treatment_bed_room_l_view_name' => $request->treatment_bed_room_l_view_name,
    //         'is_active' => $request->is_active
    //     ]);
    //     return $data;
    // }
    // public function delete($data){
    //     $data->delete();
    //     return $data;
    // }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('l_his_treatment_bed_room.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('l_his_treatment_bed_room.id');
            $maxId = $this->applyJoins()->max('l_his_treatment_bed_room.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('treatment_bed_room_l_view', 'l_his_treatment_bed_room', $startId, $endId, $batchSize);
            }
        }
    }
}