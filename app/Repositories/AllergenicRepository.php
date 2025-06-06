<?php

namespace App\Repositories;

use App\Jobs\ElasticSearch\Index\ProcessElasticIndexingJob;
use App\Models\HIS\Allergenic;
use Illuminate\Support\Facades\DB;

class AllergenicRepository
{
    protected $allergenic;
    public function __construct(Allergenic $allergenic)
    {
        $this->allergenic = $allergenic;
    }

    public function applyJoins()
    {
        return $this->allergenic
            ->select(
                'his_allergenic.*'
            );
    }
    public function applyJoinsDataDiUngThuoc()
    {
        return $this->allergenic
        ->leftJoin('his_medicine_type', 'his_medicine_type.id', '=', 'his_allergenic.medicine_type_id')
            ->select([
                'his_allergenic.id as key',
                'his_allergenic.id',
                'his_allergenic.ALLERGENIC_NAME',
                'his_allergenic.IS_DOUBT',
                'his_allergenic.IS_SURE',
                'his_allergenic.CLINICAL_EXPRESSION',
                'his_medicine_type.ACTIVE_INGR_BHYT_NAME',
                'his_medicine_type.ACTIVE_INGR_BHYT_CODE',
            ]);
    }
    public function applyKeywordFilter($query, $keyword)
    {
        return $query->where(function ($query) use ($keyword) {
            $query->where(DB::connection('oracle_his')->raw('his_allergenic.allergenic_code'), 'like', $keyword . '%')
                ->orWhere(DB::connection('oracle_his')->raw('his_allergenic.allergenic_name'), 'like', $keyword . '%');
        });
    }
    public function applyIsActiveFilter($query, $isActive)
    {
        if ($isActive !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_allergenic.is_active'), $isActive);
        }
        return $query;
    }
    public function applyIsDeleteFilter($query, $param)
    {
        if ($param !== null) {
            $query->where(DB::connection('oracle_his')->raw('his_allergenic.is_delete'), $param);
        }
        return $query;
    }
    public function applyPatientIdFilter($query, $param)
    {
        if ($param != null) {
            $query->where(DB::connection('oracle_his')->raw('his_allergenic.tdl_patient_id'), $param);
        }
        return $query;
    }
    public function applyOrdering($query, $orderBy, $orderByJoin)
    {
        if ($orderBy != null) {
            foreach ($orderBy as $key => $item) {
                if (in_array($key, $orderByJoin)) {
                } else {
                    $query->orderBy('his_allergenic.' . $key, $item);
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
        return $this->allergenic->find($id);
    }
    public function create($request, $time, $appCreator, $appModifier)
    {
        $data = $this->allergenic::create([
            'create_time' => now()->format('YmdHis'),
            'modify_time' => now()->format('YmdHis'),
            'creator' => get_loginname_with_token($request->bearerToken(), $time),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_creator' => $appCreator,
            'app_modifier' => $appModifier,
            'is_active' => 1,
            'is_delete' => 0,
            'allergenic_code' => $request->allergenic_code,
            'allergenic_name' => $request->allergenic_name,
        ]);
        return $data;
    }
    public function update($request, $data, $time, $appModifier)
    {
        $data->update([
            'modify_time' => now()->format('YmdHis'),
            'modifier' => get_loginname_with_token($request->bearerToken(), $time),
            'app_modifier' => $appModifier,
            'allergenic_code' => $request->allergenic_code,
            'allergenic_name' => $request->allergenic_name,
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
            $data = $this->applyJoins()->where('his_allergenic.id', '=', $id)->first();
            if ($data) {
                $data = $data->getAttributes();
                return $data;
            }
        } else {
            // Xác định min và max id
            $minId = $this->applyJoins()->min('his_allergenic.id');
            $maxId = $this->applyJoins()->max('his_allergenic.id');
            $chunkSize = ceil(($maxId - $minId + 1) / $numJobs);
            for ($i = 0; $i < $numJobs; $i++) {
                $startId = $minId + ($i * $chunkSize);
                $endId = $startId + $chunkSize - 1;
                // Đảm bảo chunk cuối cùng bao phủ đến maxId
                if ($i == $numJobs - 1) {
                    $endId = $maxId;
                }
                // Dispatch job cho mỗi phạm vi id
                ProcessElasticIndexingJob::dispatch('allergenic', 'his_allergenic', $startId, $endId, $batchSize);
            }
        }
    }
}
