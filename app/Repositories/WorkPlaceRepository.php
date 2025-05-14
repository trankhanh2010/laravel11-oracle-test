<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\WorkPlace;
use Illuminate\Support\Facades\DB;

class WorkPlaceRepository
{
    protected $workPlace;
    public function __construct(WorkPlace $workPlace)
    {
        $this->workPlace = $workPlace;
    }

    public function applyJoins()
    {
        return $this->workPlace
            ->select(
                'his_work_place.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_work_place.work_place_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_work_place.work_place_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_work_place.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_work_place.' . $key, $item);
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
        return $this->workPlace->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->workPlace::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'work_place_code' => $request->work_place_code,
            'work_place_name' => $request->work_place_name,
            'address' => $request->address,
            'director_name' => $request->director_name,
            'tax_code' => $request->tax_code,
            'phone' => $request->phone,

            'contact_name' => $request->contact_name,
            'contact_mobile' => $request->contact_mobile,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,

            'work_place_code' => $request->work_place_code,
            'work_place_name' => $request->work_place_name,
            'address' => $request->address,
            'director_name' => $request->director_name,
            'tax_code' => $request->tax_code,
            'phone' => $request->phone,

            'contact_name' => $request->contact_name,
            'contact_mobile' => $request->contact_mobile,

            'is_active' => $request->is_active
        ]);
        return $data;
    }
    public function delete($data)
    {
        $data->delete();
        return $data;
    }
    public function getDataFromDbToElastic($batchSize = 5000, $id = null)
    {
        $numJobs = config('queue')['num_queue_worker']; // Số lượng job song song
        if ($id != null) {
            $data = $this->applyJoins()->where('his_work_place.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_work_place.id');
            $maxId = $this->applyJoins()->max('his_work_place.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('work_place', 'his_work_place', $startId, $endId, $batchSize);
            }
        }
    }
}
