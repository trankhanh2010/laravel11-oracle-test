<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\OtherPaySource;
use Illuminate\Support\Facades\DB;

class OtherPaySourceRepository
{
    protected $otherPaySource;
    public function __construct(OtherPaySource $otherPaySource)
    {
        $this->otherPaySource = $otherPaySource;
    }

    public function applyJoins()
    {
        return $this->otherPaySource
            ->select(
                'his_other_pay_source.*'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_other_pay_source.other_pay_source_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_other_pay_source.other_pay_source_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_other_pay_source.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_other_pay_source.' . $key, $item);
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
        return $this->otherPaySource->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->otherPaySource::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'other_pay_source_code' => $request->other_pay_source_code,
            'other_pay_source_name' => $request->other_pay_source_name,
            'hein_pay_source_type_id' => $request->hein_pay_source_type_id,
            'is_not_for_treatment' => $request->is_not_for_treatment,
            'is_not_paid_diff' => $request->is_not_paid_diff,
            'is_paid_all' => $request->is_paid_all,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'other_pay_source_code' => $request->other_pay_source_code,
            'other_pay_source_name' => $request->other_pay_source_name,
            'hein_pay_source_type_id' => $request->hein_pay_source_type_id,
            'is_not_for_treatment' => $request->is_not_for_treatment,
            'is_not_paid_diff' => $request->is_not_paid_diff,
            'is_paid_all' => $request->is_paid_all,
            'is_active' => $request->is_active,
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
            $data = $this->applyJoins()->where('his_other_pay_source.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_other_pay_source.id');
            $maxId = $this->applyJoins()->max('his_other_pay_source.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('other_pay_source', 'his_other_pay_source', $startId, $endId, $batchSize);
            }
        }
    }
}
