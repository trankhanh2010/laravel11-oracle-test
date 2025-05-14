<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\PatientClassify;
use Illuminate\Support\Facades\DB;

class PatientClassifyRepository
{
    protected $patientClassify;
    public function __construct(PatientClassify $patientClassify)
    {
        $this->patientClassify = $patientClassify;
    }

    public function applyJoins()
    {
        return $this->patientClassify
            ->leftJoin('his_patient_type as patient_type', 'patient_type.id', '=', 'his_patient_classify.patient_type_id')
            ->leftJoin('his_other_pay_source as other_pay_source', 'other_pay_source.id', '=', 'his_patient_classify.other_pay_source_id')
            ->select(
                'his_patient_classify.*',
                'patient_type.patient_type_code',
                'patient_type.patient_type_name',
                'other_pay_source.other_pay_source_code',
                'other_pay_source.other_pay_source_name'
            );
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_classify.patient_classify_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_patient_classify.patient_classify_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_patient_classify.is_active'), $isActive);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                    if (in_array($key, ['other_pay_source_name', 'other_pay_source_code'])) {
                        $query->orderBy('other_pay_source.' . $key, $item);
                    }
                    if (in_array($key, ['patient_type_name', 'patient_type_name'])) {
                        $query->orderBy('patient_type.' . $key, $item);
                    }
                } else {
                    $query->orderBy('his_patient_classify.' . $key, $item);
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
        return $this->patientClassify->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->patientClassify::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'patient_classify_code' => $request->patient_classify_code,
            'patient_classify_name' => $request->patient_classify_name,
            'display_color' => $request->display_color,
            'patient_type_id' => $request->patient_type_id,
            'other_pay_source_id' => $request->other_pay_source_id,
            'bhyt_whitelist_ids' => $request->bhyt_whitelist_ids,
            'military_rank_ids' => $request->military_rank_ids,
            'is_police' => $request->is_police
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'patient_classify_code' => $request->patient_classify_code,
            'patient_classify_name' => $request->patient_classify_name,
            'display_color' => $request->display_color,
            'patient_type_id' => $request->patient_type_id,
            'other_pay_source_id' => $request->other_pay_source_id,
            'bhyt_whitelist_ids' => $request->bhyt_whitelist_ids,
            'military_rank_ids' => $request->military_rank_ids,
            'is_police' => $request->is_police,
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
            $data = $this->applyJoins()->where('his_patient_classify.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_patient_classify.id');
            $maxId = $this->applyJoins()->max('his_patient_classify.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('patient_classify', 'his_patient_classify', $startId, $endId, $batchSize);
            }
        }
    }
}
