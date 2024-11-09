<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PriorityType;
use Illuminate\Support\Facades\DB;

class PriorityTypeRepository
{
    protected $priorityType;
    public function __construct(PriorityType $priorityType)
    {
        $this->priorityType = $priorityType;
    }

    public function applyJoins()
    {
        return $this->priorityType
            ->select(
                'his_priority_type.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_priority_type.priority_type_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_priority_type.priority_type_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_priority_type.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_priority_type.' . $key, $item);
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
        return $this->priorityType->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->priorityType::create([
            'create_time' => now()->format('Ymdhis'),
            'modify_time' => now()->format('Ymdhis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,

            'priority_type_code' => $request->priority_type_code,
            'priority_type_name' => $request->priority_type_name,
            'age_from' => $request->age_from,
            'age_to' => $request->age_to,
            'bhyt_prefixs' => $request->bhyt_prefixs,
            'is_for_exam_subclinical' => $request->is_for_exam_subclinical,
            'is_for_prescription' => $request->is_for_prescription,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('Ymdhis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'priority_type_code' => $request->priority_type_code,
            'priority_type_name' => $request->priority_type_name,
            'age_from' => $request->age_from,
            'age_to' => $request->age_to,
            'bhyt_prefixs' => $request->bhyt_prefixs,
            'is_for_exam_subclinical' => $request->is_for_exam_subclinical,
            'is_for_prescription' => $request->is_for_prescription,
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
            $data = $this->applyJoins()->where('his_priority_type.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_priority_type.id');
            $maxId = $this->applyJoins()->max('his_priority_type.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('priority_type', 'his_priority_type', $startId, $endId, $batchSize);
            }
        }
    }
}
